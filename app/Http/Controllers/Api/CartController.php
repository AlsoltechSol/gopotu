<?php

namespace App\Http\Controllers\Api;

date_default_timezone_set('Asia/Kolkata');
/*
 * Add error_reporting to track error in code
 */
error_reporting(E_ALL);

header('Content-Type:application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: *");
/*
 * Additional header for app
 */
header('Content-Type:application/json');

use Carbon\Carbon;
use App\Model\Cart;

use App\Model\Shop;
use App\Model\Coupon;
use App\Model\UserAddress;
use Illuminate\Http\Request;
use App\Model\ProductVariant;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ProductsController;
use App\Model\Order;

class CartController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }

        $this->_ProductsController = new ProductsController();
    }

    public function addToCart(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'shop_id' => 'required|exists:shops,id',
                'type' => 'required|in:mart,restaurant',
                'quantity' => 'required|numeric',
                'variant_id' => 'required|exists:product_variants,id',
                'strictconfirm' => 'nullable|in:true,false'
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $data['cartcount'] = 0;
            $data['carttotal'] = 0;
            $data['variant_details'] = null;

            $variant = ProductVariant::with('product')->where('id', $request->variant_id)->whereHas('product', function ($q) use ($request) {
                $q->where('shop_id', $request->shop_id);
                $q->where('type', $request->type);
            })->first();

            if (!$variant || ($variant->status != '1' && $request->quantity > 0)) {
                return response()->json(['status' => 'error', 'message' => 'Variant selected is currently unavailable', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (!$variant->product || ($variant->product->status != '1' && $request->quantity > 0) || ($variant->product->deleted_at != null && $request->quantity > 0)) {
                return response()->json(['status' => 'error', 'message' => 'Product currently unavailable.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $existCart = Cart::where('shop_id', $request->shop_id)
                ->where('type', $request->type)
                ->where('user_id', $request->user_id)
                ->where('guest_id', $request->guest_id)
                ->where('variant_id', $variant->id)
                ->first();

            if (!$existCart) {
                if (in_array($request->type, ['mart'])) {
                    if ($request->quantity < 1) {
                        return response()->json(['status' => 'error', 'message' => 'Product quantity should be minumum 1.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if ($request->quantity > $variant->quantity) {
                        return response()->json(['status' => 'error', 'message' => 'Insufficient stock for this product variant.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }

                $docuent = array();
                $docuent['shop_id'] = $request->shop_id;
                $docuent['type'] = $request->type;
                $docuent['variant_id'] = $variant->id;
                $docuent['user_id'] = $request->user_id;
                $docuent['guest_id'] = $request->guest_id;
                $docuent['quantity'] = $request->quantity;

                if ($request->has('strictconfirm') && $request->strictconfirm != 'true') {
                    $existcart_othshop = Cart::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->where('shop_id', '!=', $request->shop_id)->first();
                    if ($existcart_othshop) {
                        $othshop = Shop::find($existcart_othshop->shop_id);
                        $currshop = Shop::find($request->shop_id);
                        if ($othshop && $currshop) {
                            return response()->json(['status' => 'confirm', 'message' => "Your cart contains products from $othshop->shop_name. Do you want to discard the selection and add product from $currshop->shop_name?", 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                    }
                }

                $action = Cart::create($docuent);
                if ($action) {
                    Cart::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->where('shop_id', '!=', $request->shop_id)->delete();

                    $_cartCount = $this->cartCount($request);
                    if ($_cartCount->status() == 200) {
                        $_cartCountData = $_cartCount->getData();
                        if ($_cartCountData->status == "success") {
                            $data['cartcount'] = $_cartCountData->data->cartcount;
                            $data['carttotal'] = $_cartCountData->data->carttotal;
                        }
                    }

                    $_variantDetails = $this->_ProductsController->variantDetails($request);
                    if ($_variantDetails->status() == 200) {
                        $_variantDetailsData = $_variantDetails->getData();
                        if ($_variantDetailsData->status == "success") {
                            $data['variant_details'] = $_variantDetailsData->data->variant_details;
                        }
                    }

                    return response()->json(['status' => 'success', 'message' => 'Product added to the cart successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Task failed. Please try again.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } else {
                if ($request->quantity == 0) {
                    return response()->json(['status' => 'error', 'message' => 'Product quantity cannot be zero.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }

                $updatedQuantity = $existCart->quantity + $request->quantity;

                if (in_array($request->type, ['mart'])) {
                    if ($request->quantity > 0 && $updatedQuantity > $variant->quantity) {
                        return response()->json(['status' => 'error', 'message' => 'Insufficient stock for this product variant.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }

                if ($updatedQuantity < 1) {
                    $action = $existCart->delete();
                    if ($action) {
                        $_cartCount = $this->cartCount($request);
                        if ($_cartCount->status() == 200) {
                            $_cartCountData = $_cartCount->getData();
                            if ($_cartCountData->status == "success") {
                                $data['cartcount'] = $_cartCountData->data->cartcount;
                                $data['carttotal'] = $_cartCountData->data->carttotal;
                            }
                        }

                        $_variantDetails = $this->_ProductsController->variantDetails($request);
                        if ($_variantDetails->status() == 200) {
                            $_variantDetailsData = $_variantDetails->getData();
                            if ($_variantDetailsData->status == "success") {
                                $data['variant_details'] = $_variantDetailsData->data->variant_details;
                            }
                        }

                        return response()->json(['status' => 'success', 'message' => 'Product removed from the cart successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'Task failed. Please try again.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                } else {
                    $existCart->quantity = $updatedQuantity;

                    $action = $existCart->save();
                    if ($action) {
                        $_cartCount = $this->cartCount($request);
                        if ($_cartCount->status() == 200) {
                            $_cartCountData = $_cartCount->getData();
                            if ($_cartCountData->status == "success") {
                                $data['cartcount'] = $_cartCountData->data->cartcount;
                                $data['carttotal'] = $_cartCountData->data->carttotal;
                            }
                        }

                        $_variantDetails = $this->_ProductsController->variantDetails($request);
                        if ($_variantDetails->status() == 200) {
                            $_variantDetailsData = $_variantDetails->getData();
                            if ($_variantDetailsData->status == "success") {
                                $data['variant_details'] = $_variantDetailsData->data->variant_details;
                            }
                        }

                        return response()->json(['status' => 'success', 'message' => 'Product quantity updated to the cart successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'Task failed. Please try again.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function removeFromCart(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'cart_id' => 'required|exists:carts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $existCart = Cart::where('id', $request->cart_id)->where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->first();
            if (!$existCart) {
                return response()->json(['status' => 'success', 'message' => 'Cart data mismatch exception occured.', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                $action = $existCart->delete();
                if ($action) {
                    return response()->json(['status' => 'success', 'message' => 'Product removed from the cart successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
                } else {
                    return response()->json(['status' => 'success', 'message' => 'Task failed. Please try again.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function cartList(Request $request, $is_checkout = false)
    {
        $data = array();

        try {
            $rules = array(
                'shop_id' => 'nullable|exists:shops,id',
                'type' => 'required|in:mart,restaurant',
                'address_id' => 'nullable|exists:user_addresses,id',
                'coupon_code' => 'nullable|exists:coupons,code',
                'wallet_used' => 'nullable|in:true,false',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $response_status = 'success';
            $response_msg = "Success";

            $checkout = ['status' => true, 'exception' => null];
            $item_total = 0;
            $merchant_total = 0;
            $coupon_discount = 0;
            $wallet_cashback = 0;
            $payable_amount = 0;


            $default_address = UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('is_default', 1)->first();
            if ($default_address && !$request->address_id) {
                $request['address_id'] = $default_address->id;
            }

            $items = Cart::with('variant')->where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->get();
            if (count($items) < 1) {
                if ($checkout['status'] == true) {
                    $checkout = ['status' => false, 'exception' => 'No items found in the Cart'];
                }
            }

            $cart_shop = Cart::select('shop_id')->where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->distinct('shop_id')->first();
            $data['selected_shop'] = $selected_shop = $cart_shop ? Shop::find($cart_shop->shop_id) : null;

            if (@$selected_shop->status != '1') {
                if ($checkout['status'] == true) {
                    $checkout = ['status' => false, 'exception' => 'The store has been suspended for accepting orders'];
                }
            }

            $default_address->deliverable = @\Myhelper::validatePurchaseLocation($default_address->id, $selected_shop->id)->status ?? false;
            $data['default_address'] = $default_address;

            switch ($request->type) {
                case 'restaurant':
                case 'mart':
                    if (@$selected_shop->online != '1') {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => 'The store is currently not accepting order. Please comeback later'];
                        }
                    }
                    break;
            }

            foreach ($items as $item) {
                if (@$item->variant && @$item->variant->product) {
                    if ($item->variant->status != '1' || $item->variant->product->status != '1') {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => $item->variant->product->details->name . " is currently not available."];
                        }
                    }

                    if ($item->variant->deleted_at || $item->variant->product->deleted_at) {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => $item->variant->product->details->name . " is currently removed for shopping."];
                        }
                    }

                    if (in_array($request->type, ['mart'])) {
                        if ($item->quantity > $item->variant->quantity) {
                            if ($checkout['status'] == true) {
                                $checkout = ['status' => false, 'exception' => $item->variant->product->details->name . " not have sufficient stock."];
                            }
                        }
                    }


                    $price = ($item->quantity * $item->variant->purchase_price);
                    $m_price = ($item->quantity * $item->variant->offeredprice);
                    if($item->variant->listingprice !== null){
                        $price = ($item->quantity * $item->variant->listingprice);
                    }

                    // if($item->variant->offeredprice == null ||  $item->variant->offeredprice == 0){
                    //     $m_price = $price;
                    // }
                   
                 //   $price = ($item->quantity * $item->variant->listingprice);
                    // $price = ($item->quantity * $item->variant->purchase_price);
                    $item->sub_total = $price;
                    $item_total += $price;
                    
                    $merchant_total += $m_price;
                } else {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => 'Few item are currently not available'];
                    }
                }
            }

            if ($request->has('coupon_code') && $request->coupon_code != null) {
                if ($request->has('forcecoupon') && $request->forcecoupon == true) {
                    $response_status = 'success';
                    $response_msg = "Success";

                    $checkout = ['status' => true, 'exception' => null];
                }

                if (!$request->user_id) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => 'To apply coupon you must signin to your account'];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = 'To apply coupon you must signin to your account';
                    }
                }

                $coupon = Coupon::withCount('coupon_used')->where('code', $request->coupon_code)->first();

                if (!$coupon) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => 'The coupon code selected is invalid'];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = 'The coupon code selected is invalid';
                    }
                }

                if ($coupon->status != '1') {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => 'The coupon is currently not available'];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = 'The coupon is currently not available';
                    }
                }

                if (is_array($coupon->applied_for_users) && count($coupon->applied_for_users) > 0) {
                    if (!in_array($request->user_id, $coupon->applied_for_users)) {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => 'You cannot apply this coupon'];
                        }

                        if ($response_status == 'success') {
                            $response_status = 'error';
                            $response_msg = 'You cannot apply this coupon';
                        }
                    }
                }

                if ($coupon->max_usages != null) {
                    $coupon_used = Order::where('user_id', \Auth::guard('api')->id())
                        ->where('coupon_id', $coupon->id)
                        ->whereIn('status', ['paymentinitiated', 'received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered', 'returned'])
                        ->count();

                    if ($coupon_used >= $coupon->max_usages) {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => 'You have already used this coupon the maximum time'];
                        }

                        if ($response_status == 'success') {
                            $response_status = 'error';
                            $response_msg = 'You have already used this coupon the maximum time';
                        }
                    }

                    // if ($coupon->coupon_used_count >= $coupon->max_usages) {
                    //     if ($checkout['status'] == true) {
                    //         $checkout = ['status' => false, 'exception' => 'The coupon has been used the maximum times allowed'];
                    //     }

                    //     if ($response_status == 'success') {
                    //         $response_status = 'error';
                    //         $response_msg = 'The coupon has been used the maximum times allowed';
                    //     }
                    // }
                }

                if ($coupon->min_order != null && $item_total < $coupon->min_order) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => "The minimum order amount for this coupon is " . $coupon->min_order . " " . config('app.currency.code')];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = "The minimum order amount for this coupon is " . $coupon->min_order . " " . config('app.currency.code');
                    }
                }

                if ($coupon->valid_till != null) {
                    $current_date = Carbon::now()->endOfDay();
                    $coupon_expiry = Carbon::parse($coupon->valid_till)->endOfDay();
                    $diff = $current_date->diffInDays($coupon_expiry, false);

                    if ($diff < 0) {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => "The selected coupon has expired"];
                        }

                        if ($response_status == 'success') {
                            $response_status = 'error';
                            $response_msg = "The selected coupon has expired";
                        }
                    }
                }

                if ($coupon->type == "flat") {
                    $coupon_discount = (float)($coupon->value);
                } else {
                    $coupon_discount = ((float)($coupon->value) / 100) * (float)($item_total);
                }

                if ($coupon_discount >= $item_total) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => "You cannot apply this coupon for current order"];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = "You cannot apply this coupon for current order";
                    }
                }

                if ($coupon->max_discount != null && $coupon_discount > $coupon->max_discount) {
                    $coupon_discount = $coupon->max_discount;
                }

                if ($coupon && ($request->has('wallet_used') && $request->wallet_used == 'true')) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => "You cannot appy coupon if you are using wallet on confirming the order"];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = "You cannot appy coupon if you are using wallet on confirming the order";
                    }
                }

                if ($response_status == 'success') {
                    $data['coupon_details'] = $coupon;
                } else {
                    $data['coupon_details'] = null;
                    $coupon_discount = 0;
                }

                if ($request->has('forcecoupon') && $request->forcecoupon == true) {
                    $cpn_data = [
                        'coupon_details' => $data['coupon_details'],
                        'coupon_discount' => $coupon_discount,
                        'checkout' => $checkout,
                    ];

                    return response()->json(['status' => $response_status, 'message' => $response_msg, 'data' => \Myhelper::formatApiResponseData($cpn_data)]);
                }

                /** CHECKING THE COUPON REWARD TYPE */
                if (in_array($coupon->rewarded, ['walletafterdelivery'])) {
                    $wallet_cashback = $coupon_discount;
                    $coupon_discount = 0;
                }
            }

            $data['available_coupons'] = [];
            if ($request->user_id) {
                $coupons = Coupon::withCount('coupon_used')->where('status', '1')->get();
                foreach ($coupons as $key => $item) {
                    $item->selected = false;
                    $c_flag = true;

                    if ($item->valid_till != null) {
                        $current_date = Carbon::now()->endOfDay();
                        $coupon_expiry = Carbon::parse($item->valid_till)->endOfDay();
                        $diff = $current_date->diffInDays($coupon_expiry, false);

                        if ($diff < 0) {
                            $c_flag = false;
                        }
                    }

                    if (is_array($item->applied_for_users) && count($item->applied_for_users) > 0) {
                        if (!in_array($request->user_id, $item->applied_for_users)) {
                            $c_flag = false;
                        }
                    }

                    if ($item->max_usages != null) {
                        // $coupon_used = Order::where('coupon_id', $item->id)
                        //     ->whereIn('status', ['paymentinitiated', 'received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered', 'returned'])
                        //     ->count();

                        // if ($coupon_used >= $item->max_usages) {
                        //     $c_flag = false;
                        // }

                        if ($item->coupon_used_count >= $item->max_usages) {
                            $c_flag = false;
                        }
                    }

                    if (isset($data['coupon_details']) && $data['coupon_details'] != null) {
                        if ($data['coupon_details']->id === $item->id) {
                            $item->selected = true;
                        }
                    }

                    if ($c_flag == true) {
                        array_push($data['available_coupons'], $item);
                    }
                }
            }

            $delivery_charge = ($items && count($items) > 0 && $selected_shop) ? \Myhelper::getCartDeliveryCharge($item_total, $request->address_id, $selected_shop->id) : 0;
            $payable_amount = ($item_total + $delivery_charge) - $coupon_discount;

            if (config('app.order_minval') && (float) (float)$payable_amount < config('app.order_minval')) {
                if ($checkout['status'] == true) {
                    $checkout = ['status' => false, 'exception' => 'The minimum order value should be ' . config('app.order_minval') . ' INR'];
                }

                if ($response_status == 'success' && $is_checkout == true) {
                    $response_status = 'error';
                    $response_msg = 'The minimum order value should be ' . config('app.order_minval') . ' INR';
                }
            }

            if ($request->has('address_id') && $request->address_id != null) {
                $user_address = UserAddress::where('id', $request->address_id)->where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->first();
                if (!$user_address) {
                    if ($checkout['status'] == true) {
                        $checkout = ['status' => false, 'exception' => 'Address selected is invalid'];
                    }

                    if ($response_status == 'success') {
                        $response_status = 'error';
                        $response_msg = 'Address selected is invalid';
                    }
                }

                if ($selected_shop) {
                    $validate_purchase = \Myhelper::validatePurchaseLocation($user_address->id, $selected_shop->id);
                    $user_address->deliverable = @$validate_purchase->status ?? false;
                    if ($validate_purchase->status == false) {
                        if ($checkout['status'] == true) {
                            $checkout = ['status' => false, 'exception' => $validate_purchase->message];
                        }

                        if ($response_status == 'success') {
                            $response_status = 'error';
                            $response_msg = $validate_purchase->message;
                        }
                    }
                }

                if ($response_status == 'success') {
                    $data['address'] = $user_address;
                } else {
                    $data['address'] = null;
                    $delivery_charge = 0;
                }
            }

            /** CALCULATING THE WALLET USABLE */
            $wallet_deducted = 0;
            $wallet = (object) [
                'balance' => 0,
                'maxusage_per' => 0,
                'maxusage_allowed' => 0,
                'usable' => 0,
            ];

            if ($request->user_id) {
                $wallet->balance = \Auth::guard('api')->user()->userwallet;
                $wallet->maxusage_per = @config('maxwalletuse')[$request->type] ?? 0;
                $wallet->maxusage_allowed = ($wallet->maxusage_per / 100) * $item_total;
                $wallet->usable = $wallet->maxusage_allowed;
                if ($wallet->balance < $wallet->usable) {
                    $wallet->usable = $wallet->balance;
                }

                if ($request->has('wallet_used') && $request->wallet_used == 'true') {
                    $wallet_deducted = $wallet->usable;
                    $payable_amount -= $wallet_deducted;
                }
            }

            $deliverycharge_settings = (object) [
                'delivery_distance' => @$selected_shop ? \Myhelper::locationDistance($user_address->latitude, $user_address->longitude, $selected_shop->shop_latitude, $selected_shop->shop_longitude, 'km') : 0,
                // 'deliverycharge_status' => config('app.deliverycharge_status'),
                'deliverycharge_perkm' => config('app.deliverycharge_perkm'),
                'deliverycharge_min' => config('app.deliverycharge_min'),
                // 'deliverycharge_freeordervalue' => config('app.deliverycharge_freeordervalue'),
            ];

            $data['items'] = $items;
            $data['item_total'] = number_format((float)$item_total, 2, '.', '');
            $data['merchant_total'] = number_format((float)$merchant_total, 2, '.', '');
            $data['delivery_charge'] = number_format((float)$delivery_charge, 2, '.', '');
            $data['coupon_discount'] = number_format((float)$coupon_discount, 2, '.', '');
            $data['wallet_deducted'] = number_format((float)$wallet_deducted, 2, '.', '');
            $data['payable_amount'] = number_format((float)$payable_amount, 2, '.', '');
            $data['wallet_cashback'] = number_format((float)$wallet_cashback, 2, '.', '');
            $data['wallet'] = $wallet;
            $data['deliverycharge_settings'] = $deliverycharge_settings;
            $data['checkout'] = $checkout;

            return response()->json(['status' => $response_status, 'message' => $response_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage() . '; Line: ' . $e->getLine(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function cartCount(Request $request)
    {
        $data = array();
        try {
            $rules = array(
                'shop_id' => 'nullable|exists:shops,id',
                'type' => 'required|in:mart,restaurant',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $data['cartcount'] = Cart::with('variant')->where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->sum('quantity');
            $data['carttotal'] = 0;

            $_cartList = $this->cartList($request);
            if ($_cartList->status() == 200) {
                $_cartData = $_cartList->getData();
                if ($_cartData->status == "success") {
                    $data['carttotal'] = $_cartData->data->item_total;
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function addCoupon(Request $request)
    {
        $data = array();

        try {
            $rules = array(
                'coupon_code' => 'required|exists:coupons,code',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $request['forcecoupon'] = true;
            $_cartList = $this->cartList($request);
            if ($_cartList->status() != 200) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: CART_LIST_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $_cartData = $_cartList->getData();

            if ($_cartData->status == "error") {
                return response()->json(['status' => 'error', 'message' => $_cartData->message, 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($_cartData->data->checkout->status == false) {
                return response()->json(['status' => 'error', 'message' => $_cartData->data->checkout->exception, 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['coupon_details'] = $_cartData->data->coupon_details;
            $data['coupon_discount'] = $_cartData->data->coupon_discount;

            return response()->json(['status' => 'success', 'message' => 'Coupon applied successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
