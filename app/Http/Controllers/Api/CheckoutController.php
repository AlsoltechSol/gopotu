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

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Api\CartController;
use App\Model\Cart;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderReturnReplace;
use App\Model\ProductVariant;
use App\Model\Provider;
use App\Model\WalletReport;
use App\User;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }

        $this->_CartController = new CartController();
    }

    public function create(Request $request)
    {
        $data = array();

        try {
            $rules = array(
                'shop_id' => 'required|exists:shops,id',
                'type' => 'required|in:mart,restaurant',
                'address_id' => 'required|exists:user_addresses,id',
                'payment_mode' => 'required|in:cash,online',
                'coupon_code' => 'nullable|exists:coupons,code',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $request['user_id'] = \Auth::guard('api')->id();
            $request['guest_id'] = null;

            $_cartList = $this->_CartController->cartList($request, true);
            if ($_cartList->status() != 200) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: CART_LIST_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $_cartData = $_cartList->getData();


            // return $_cartData;git 

            if ($_cartData->status == "error") {
                return response()->json(['status' => 'error', 'message' => $_cartData->message, 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($_cartData->data->checkout->status == false) {
                return response()->json(['status' => 'error', 'message' => $_cartData->data->checkout->exception, 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $selected_shop = $_cartData->data->selected_shop;
            if (!$selected_shop) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Shop can\' be traced.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            do {
                $request['ordercode'] = config('app.shortname') . '-' . rand(1111111111, 9999999999);
            } while (Order::where("code", "=", $request->ordercode)->first() instanceof Order);

           
            $order_document = array();
            $order_document['code'] = $request->ordercode;
            $order_document['shop_id'] = $selected_shop->id;
            $order_document['type'] = $request->type;
            $order_document['user_id'] = \Auth::guard('api')->id();
            $order_document['cust_name'] = $_cartData->data->address->name;
            $order_document['cust_mobile'] = $_cartData->data->address->mobile;
            $order_document['cust_latitude'] = $_cartData->data->address->latitude;
            $order_document['cust_longitude'] = $_cartData->data->address->longitude;
            $order_document['cust_location'] = $_cartData->data->address->location;
            $order_document['cust_address'] = json_encode($_cartData->data->address->full_address);
            $order_document['item_total'] = $_cartData->data->item_total;
            $order_document['merchant_total'] = $_cartData->data->merchant_total;
            $order_document['delivery_charge'] = $_cartData->data->delivery_charge;
            $order_document['coupon_discount'] = $_cartData->data->coupon_discount;
            $order_document['wallet_deducted'] = $_cartData->data->wallet_deducted;
            $order_document['payable_amount'] = $_cartData->data->payable_amount;
            $order_document['wallet_cashback'] = $_cartData->data->wallet_cashback;
            $order_document['payment_mode'] = $request->payment_mode;

            if (@$_cartData->data->coupon_details != null) {
                $order_document['coupon_id'] = $_cartData->data->coupon_details->id;
            }

            $provider = Provider::where('type', 'order_admincharges')->where('slug', $request->type)->first();
            if ($provider && $selected_shop) {
                $order_document['admin_charge'] = \Myhelper::getCommission($order_document['item_total'], $provider->id, $selected_shop->user_id);
            }

            switch ($request->payment_mode) {
                case 'cash':
                    $order_document['status'] = 'received';
                    break;

                case 'online':
                    $order_document['status'] = 'paymentinitiated';

                    // $url = 'https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=' . config('paytm.MERCHANT_ID') . '&orderId=' . $order_document['code'];

                    // $callbackurl = route('callback.paytmorderpayment');
                    // $callbackurl = 'https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=' . $order_document['code'];
                    // Staging Environment: https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=<order_id>
                    // Production Environment: https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=<order_id>

                    // $parameter['body'] = array(
                    //     'requestType' => "Payment",
                    //     'mid' => config('paytm.MERCHANT_ID'),
                    //     'websiteName' => config('paytm.WEBSITE_NAME'),
                    //     'orderId' =>  $order_document['code'],
                    //     'txnAmount' => array(
                    //         'value' => $order_document['payable_amount'],
                    //         'currency' => 'INR',
                    //     ),
                    //     'userInfo' => array(
                    //         'custId' => $order_document['user_id'],
                    //         'mobile' => $order_document['cust_mobile'],
                    //         'email' => \Auth::guard('api')->user()->email,
                    //         'firstName' => $order_document['cust_name'],
                    //         // 'lastName' => $order_document['cust_name'],
                    //     ),
                    //     'callbackUrl' => $callbackurl,
                    // );

                    // $parameter['head'] = array(
                    //     'signature' => $this->getChecksumFromString(json_encode($parameter['body'], JSON_UNESCAPED_SLASHES), config('paytm.MERCHANT_KEY')),
                    //     'channelId' => 'WAP',
                    //     'version' => 'v1',
                    //     'requestTimestamp' => Carbon::now()->timestamp
                    // );

                    // $header = array(
                    //     "Content-Type: application/json",
                    // );

                    // $result = \Myhelper::curl($url, "POST", json_encode($parameter, JSON_UNESCAPED_SLASHES), $header);
                    // if ($result['error'] || $result['response'] == "" || $result['code'] != 200) {
                    //     return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured', 'data' => \Myhelper::formatApiResponseData($data)]);
                    // }

                    // $doc = json_decode($result['response']);

                    // if (@$doc->body->resultInfo->resultCode != "0000") {
                    //     return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured. ' . @$doc->body->resultInfo->resultMsg, 'data' => \Myhelper::formatApiResponseData($data)]);
                    // }

                    // if (!@$doc->body->txnToken) {
                    //     return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured. Transaction token not received', 'data' => \Myhelper::formatApiResponseData($data)]);
                    // }

                    // $data['paytm_document'] = array(
                    //     'merchant_id' => config('paytm.MERCHANT_ID'),
                    //     'merchant_key' => config('paytm.MERCHANT_KEY'),
                    //     'website_name' => config('paytm.WEBSITE_NAME'),
                    //     'order_id' => $order_document['code'],
                    //     'amount' => $order_document['payable_amount'],
                    //     'txn_token' => @$doc->body->txnToken,
                    //     'callbackurl' => @$callbackurl
                    // );

                    // \Log::info(Carbon::now()->format('Y-m-d H:i:s') . "PayTM Tranaction Initiated: " . json_encode($data['paytm_document']));
                    break;
            }

            $order = Order::create($order_document);
            if ($order) {
               
                foreach ($_cartData->data->items as $key => $item) {
                    $tax = \Myhelper::getVariantTax($item->variant->id);

                    $ordered_item = array(
                        'order_id' => $order->id,
                        'product_id' => $item->variant->product->id,
                        'variant_id' => $item->variant->id,
                        'variant_selected' => json_encode((object) [
                            'color_code' => $item->variant->color,
                            'color_name' => @$item->variant->color_details->name,
                            'variant_code' => $item->variant->variant,
                            'variant_name' => strtoupper(str_replace(':', ' : ', $item->variant->variant)),
                        ]),
                        'price' => $item->variant->purchase_price,
                        'listingprice' => $item->variant->listingprice,
                        'tax' => $tax,
                        'quantity' => $item->quantity,
                        'sub_total' => $item->sub_total,
                        'tax_total' => round($tax * $item->quantity, 2),
                        'shop_tin' => \Myhelper::getShopTin(@$item->variant->product->shop_id),
                    );

                    $action = OrderProduct::create($ordered_item);
                    if ($action) {
                        if (in_array($request->type, ['mart'])) {
                            // ProductVariant::where('id', $item->variant->id)->decrement('quantity', $item->quantity);
                        }
                    }
                }

                $order = Order::find($order->id);
                $data['order'] = $order;

                switch ($order->status) {
                    case 'received':
                        \Myhelper::onConfirmOrderReduceStock($order->id);

                        Cart::where('user_id', \Auth::guard('api')->id())->where('type', $request->type)->delete();

                        if ($order->wallet_deducted > 0) {
                            $report = array(
                                'user_id' => $order->user->id,
                                'ref_id' => $order->id,
                                'wallet_type' => 'userwallet',
                                'balance' => \Auth::guard('api')->user()->userwallet,
                                'trans_type' => 'debit',
                                'amount' => $order->wallet_deducted,
                                'remarks' => 'Amount debited for Order No ' . $order->code,
                                'service' => 'order',
                            );

                            $transaction = User::where('id', \Auth::guard('api')->user()->id)->decrement('userwallet', $order->wallet_deducted);
                            if ($transaction) {
                                WalletReport::create($report);
                            }
                        }

                        \Myhelper::updateOrderStatusAction($order->id);
                        return response()->json(['status' => 'success', 'message' => 'Order placed successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
                        break;

                    case 'paymentinitiated':
                        $data['paytm_document'] = array(
                            'payment_url' => route('orderpay.initpayment', ['order_id' => $order->id]),
                            'success_url' => route('orderpay.successpayment', ['order_id' => $order->id]),
                            'failed_url' => route('orderpay.failedpayment', ['order_id' => $order->id]),
                        );

                        return response()->json(['status' => 'paymentinitiated', 'message' => 'Please complete the payment to confirm the order', 'data' => \Myhelper::formatApiResponseData($data)]);
                        break;
                }

                return response()->json(['status' => 'success', 'message' => 'Order placed successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Order cannot be placed', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetch(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
                'status' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $orders = Order::with('order_products', 'return_replacements')
                ->withCount('return_replacements')
                ->where('user_id', \Auth::guard('api')->id())
                ->whereIn('status', ['received', 'accepted', 'processed', 'intransit', 'outfordelivery', 'delivered', 'cancelled', 'returned']);

            /** Filter By Status */
            if ($request->has('status') && $request->status != null) {
                if (is_array($request->status)) {
                    $orders->whereIn('status', $request->status);
                } else {
                    $orders->where('status', $request->status);
                }
            }

            $orders = $orders->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $orders->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $orders = $orders->skip($skip)->take(config('app.pagination_records'));
            }

            $orders = $orders->get();

            $data['orders'] = $orders;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function details(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::with('order_products', 'shop', 'deliveryboy', 'return_replacements')->where('user_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($order->expected_delivery) {
                $order->expected_delivery = Carbon::parse($order->expected_delivery)->format('d M Y');
            }

            if ($order->deliveryboy && $order->deliveryboy_status != 'accepted') {
                $order->deliveryboy = null;
            }

            /**
             * Calculation Distance & Duration from origin and destination
             */

            $distance = [
                'deliveryboytocustomer' => [
                    'distance_value' => 0,
                    'distance_text' => "0 km",
                    'duration_value' => 0,
                    'duration_text' => "0 min",
                ],
                'shoptocustomer' => [
                    'distance_value' => 0,
                    'distance_text' => "0 km",
                    'duration_value' => 0,
                    'duration_text' => "0 min",
                ],
            ];

            if ($order->deliveryboy && $order->deliveryboy_status == 'accepted') {
               
                if (in_array($order->status, ['outfordelivery'])) {
                    if ($order->deliveryboy->latitude && $order->deliveryboy->longitude && $order->cust_latitude && $order->cust_longitude) {
                        $deliveryboytocustomer_dist = \Myhelper::getDistanceMatric($order->deliveryboy->latitude, $order->deliveryboy->longitude, $order->cust_latitude, $order->cust_longitude);
                        if ($deliveryboytocustomer_dist) {
                            $distance['deliveryboytocustomer'] = [
                                'distance_value' => $deliveryboytocustomer_dist->distance_value,
                                'distance_text' => $deliveryboytocustomer_dist->distance_text,
                                'duration_value' => $deliveryboytocustomer_dist->duration_value,
                                'duration_text' => $deliveryboytocustomer_dist->duration_text,
                            ];
                        }
                    }

                   

                    if ($order->shop->shop_latitude && $order->shop->shop_longitude && $order->cust_latitude && $order->cust_longitude) {
                      
                        $shoptocustomer_dist = \Myhelper::getDistanceMatric($order->shop->shop_latitude, $order->shop->shop_longitude, $order->cust_latitude, $order->cust_longitude);
                        if ($shoptocustomer_dist) {
                            $distance['shoptocustomer'] = [
                                'distance_value' => $shoptocustomer_dist->distance_value,
                                'distance_text' => $shoptocustomer_dist->distance_text,
                                'duration_value' => $shoptocustomer_dist->duration_value,
                                'duration_text' => $shoptocustomer_dist->duration_text,
                            ];
                        }
                    }
                }
            }

            $order->distance = $distance;
            $data['order'] = $order;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function submitReview(Request $request)
    {
        $data = array();

        try {
            $rules = array(
                'order_id' => 'required|exists:orders,id',
                'shop_rating' => 'required|numeric|min:1|max:5',
                'shop_review' => 'nullable',
                'deliveryboy_rating' => 'nullable|numeric|min:1|max:5',
                'deliveryboy_review' => 'nullable',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::where('user_id', \Auth::id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => "The order selected is invalid", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (!in_array($order->status, ['delivered'])) {
                return response()->json(['status' => 'error', 'message' => "The order must be delivered, then you can give rating.", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($order->rating != null) {
                return response()->json(['status' => 'error', 'message' => "You have already rated this order before, you cannot update this.", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $document = [
                'shop_rating' => $request->shop_rating,
                'shop_review' => $request->shop_review,
                'deliveryboy_rating' => $request->deliveryboy_rating,
                'deliveryboy_review' => $request->deliveryboy_review,
            ];

            $action = Order::where('id', $order->id)->update($document);
            if ($action) {
                return response()->json(['status' => 'success', 'message' => "Review submitted successfully.", 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => "Review cannot be submitted. Please try again later.", 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function cancelSubmit(Request $request)
    {
        $data = array();

        try {
            $rules = array(
                'order_id' => 'required|exists:orders,id',
                'cancel_reason' => 'required',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::where('user_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'The order selected is invalid', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (!in_array($order->status, ['received', 'paymentinitiated'])) {
                return response()->json(['status' => 'error', 'message' => 'You cannot cancel this order.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (in_array($order->payment_mode, ['online'])) {
                return response()->json(['status' => 'error', 'message' => 'You cannot cancel this order. To cancel prepaid orders, contact our support team', 'data' => $data]);
            }

            $order->status = 'cancelled';
            $order->user_cancel_reason = $request->cancel_reason;
            if ($order->save()) {
                \Myhelper::updateOrderStatusLog($order->id);
                \Myhelper::updateOrderStatusAction($order->id);

                return response()->json(['status' => 'success', 'message' => 'The order has been cancelled successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Please try again later', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function initiatePaymentforCashOrder(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::where('user_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($order->payment_mode != 'cash' || !in_array($order->status, ['received', 'accepted', 'processed', 'intransit', 'outfordelivery'])) {
                return response()->json(['status' => 'error', 'message' => 'You cannot make payment for this order', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['paytm_document'] = array(
                'payment_url' => route('cashorderpay.initpayment', ['order_id' => $order->id]),
                'success_url' => route('cashorderpay.successpayment', ['order_id' => $order->id]),
                'failed_url' => route('cashorderpay.failedpayment', ['order_id' => $order->id]),
            );

            $data['order'] = $order;

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function _TRASHED_orderPaymentCallback(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::where('payment_mode', 'online')->findorfail($request->order_id);

            $url = 'https://securegw-stage.paytm.in/v3/order/status';

            $parameter['body'] = array(
                'mid' => config('paytm.MERCHANT_ID'),
                'orderId' =>  $order->code,
            );

            $parameter['head'] = array(
                'signature' => $this->getChecksumFromString(json_encode($parameter['body'], JSON_UNESCAPED_SLASHES), config('paytm.MERCHANT_KEY'))
            );

            $header = array(
                "Content-Type: application/json",
            );

            $result = \Myhelper::curl($url, "POST", json_encode($parameter, JSON_UNESCAPED_SLASHES), $header);
            if ($result['error'] || $result['response'] == "" || $result['code'] != 200) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $doc = json_decode($result['response']);
            $payment = $doc->body;
            if (!$payment || $order->status != 'paymentinitiated') {
                return response()->json(['status' => 'error', 'message' => 'Payment validation failed', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (in_array(@$payment->resultInfo->resultStatus, ['TXN_SUCCESS'])) {
                $order->status = 'received';
                $order->payment_txnid = @$payment->txnId;

                $action = $order->save();
                if ($action) {
                    \Myhelper::updateOrderStatusLog($order->id);
                    \Myhelper::updateOrderStatusAction($order->id);

                    $order = Order::find($order->id);
                    $data['order'] = $order;

                    // Cart::where('user_id', \Auth::guard('api')->id())->where('type', $order->type)->delete();

                    return response()->json(['status' => 'success', 'message' => 'Order placed successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } elseif (in_array(@$payment->resultInfo->resultStatus, ['PENDING'])) {
                $order->payment_txnid = @$payment->txnId;
                $action = $order->save();

                $order = Order::find($order->id);
                $data['order'] = $order;
                return response()->json([
                    'status' => 'paymentprocessing',
                    'message' => @$payment->resultInfo->resultMsg ?? "Looks like the payment is not complete. Please wait while we confirm the status with your bank.",
                    'data' => \Myhelper::formatApiResponseData($data)
                ]);
            } else {
                $order->status = 'received';
                $order->payment_mode = 'cash';

                $action = $order->save();
                if ($action) {
                    \Myhelper::updateOrderStatusLog($order->id);
                    \Myhelper::updateOrderStatusAction($order->id);

                    $order = Order::find($order->id);
                    $data['order'] = $order;

                    // Cart::where('user_id', \Auth::guard('api')->id())->where('type', $order->type)->delete();

                    return response()->json(['status' => 'paymentfailed', 'message' => 'The payment failed, you can pay later for the order.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function _TRASHED_initiatePaymentforCashOrder(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::where('user_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($order->payment_mode != 'cash' || !in_array($order->status, ['received', 'accepted', 'processed', 'intransit', 'outfordelivery'])) {
                return response()->json(['status' => 'error', 'message' => 'You cannot make payment for this order', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            /** START OF GENERATING ORDER ID FOR PAYMENT */

            $url = 'https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=' . config('paytm.MERCHANT_ID') . '&orderId=' . $order->code;

            // $callbackurl = route('callback.paytmorderpayment');
            $callbackurl = 'https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=' . $order->code;
            // Staging Environment: https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=<order_id>
            // Production Environment: https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=<order_id>

            $parameter['body'] = array(
                'requestType' => "Payment",
                'mid' => config('paytm.MERCHANT_ID'),
                'websiteName' => config('paytm.WEBSITE_NAME'),
                'orderId' =>  $order->code,
                'txnAmount' => array(
                    'value' => $order->payable_amount,
                    'currency' => 'INR',
                ),
                'userInfo' => array(
                    'custId' => $order->user_id,
                    'mobile' => $order->cust_mobile,
                    'email' => \Auth::guard('api')->user()->email,
                    'firstName' => $order->cust_name,
                    // 'lastName' => $order->cust_name,
                ),
                'callbackUrl' => $callbackurl,
            );

            $parameter['head'] = array(
                'signature' => $this->getChecksumFromString(json_encode($parameter['body'], JSON_UNESCAPED_SLASHES), config('paytm.MERCHANT_KEY')),
                'channelId' => 'WAP',
                'version' => 'v1',
                'requestTimestamp' => Carbon::now()->timestamp
            );

            $header = array(
                "Content-Type: application/json",
            );

            $result = \Myhelper::curl($url, "POST", json_encode($parameter, JSON_UNESCAPED_SLASHES), $header);
            if ($result['error'] || $result['response'] == "" || $result['code'] != 200) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $doc = json_decode($result['response']);

            if (@$doc->body->resultInfo->resultCode != "0000") {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured. ' . @$doc->body->resultInfo->resultMsg, 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if (!@$doc->body->txnToken) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured. Transaction token not received', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['paytm_document'] = array(
                'merchant_id' => config('paytm.MERCHANT_ID'),
                'merchant_key' => config('paytm.MERCHANT_KEY'),
                'website_name' => config('paytm.WEBSITE_NAME'),
                'order_id' => $order->code,
                'amount' => $order->payable_amount,
                'txn_token' => @$doc->body->txnToken,
                'callbackurl' => @$callbackurl
            );

            /** END OF GENERATING ORDER ID FOR PAYMENT */

            $data['order'] = $order;

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function _TRASHED_paymentCallbackforCashOrder(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => $data]);
                }
            }

            $order = Order::findorfail($request->order_id);

            $url = 'https://securegw-stage.paytm.in/v3/order/status';

            $parameter['body'] = array(
                'mid' => config('paytm.MERCHANT_ID'),
                'orderId' =>  $order->code,
            );

            $parameter['head'] = array(
                'signature' => $this->getChecksumFromString(json_encode($parameter['body'], JSON_UNESCAPED_SLASHES), config('paytm.MERCHANT_KEY'))
            );

            $header = array(
                "Content-Type: application/json",
            );

            $result = \Myhelper::curl($url, "POST", json_encode($parameter, JSON_UNESCAPED_SLASHES), $header);
            if ($result['error'] || $result['response'] == "" || $result['code'] != 200) {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Payment geteway error occured', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $doc = json_decode($result['response']);
            $payment = $doc->body;
            if (!$payment && ($order->payment_mode != 'cash' || !in_array($order->status, ['accepted', 'intransit', 'outfordelivery']))) {
                return response()->json(['status' => 'error', 'message' => 'You cannot make payment for this order', 'data' => $data]);
            }

            if (in_array(@$payment->resultInfo->resultStatus, ['TXN_SUCCESS'])) {
                $order->payment_mode = 'online';
                $order->payment_txnid = @$payment->txnId;

                $action = $order->save();
                if ($action) {
                    $order = Order::find($order->id);
                    $data['order'] = $order;

                    $notify_content = "Thank you for paying online for your order. We have recieved payment for your Order No: " . $order->code;
                    \Myhelper::sendNotification($order->user_id, "Payment Recieved", $notify_content);

                    return response()->json(['status' => 'success', 'message' => 'Payment recieved for the order successfully', 'data' => $data]);
                }
            } elseif (in_array(@$payment->resultInfo->resultStatus, ['PENDING'])) {
                $order->payment_txnid = @$payment->txnId;
                $action = $order->save();

                $order = Order::find($order->id);
                $data['order'] = $order;
                return response()->json([
                    'status' => 'paymentprocessing',
                    'message' => @$payment->resultInfo->resultMsg ?? "Looks like the payment is not complete. Please wait while we confirm the status with your bank.",
                    'data' => \Myhelper::formatApiResponseData($data)
                ]);
            } else {
                $order = Order::find($order->id);
                $data['order'] = $order;

                return response()->json(['status' => 'error', 'message' => 'Order payment failed. Please try again', 'data' => $data]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => $data]);
        }
    }

    public function fetchReturnReplacements(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
                'order_id' => 'nullable',
                'status' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplacements = OrderReturnReplace::with('order', 'returnreplacement_items')
                ->whereHas('order', function ($q) {
                    $q->where('user_id', \Auth::guard('api')->id());
                });

            /** Filter By Order */
            if ($request->has('order_id') && $request->order_id != null) {
                if (is_array($request->order_id)) {
                    $returnreplacements->whereIn('order_id', $request->order_id);
                } else {
                    $returnreplacements->where('order_id', $request->order_id);
                }
            }

            /** Filter By Status */
            if ($request->has('status') && $request->status != null) {
                if (is_array($request->status)) {
                    $returnreplacements->whereIn('status', $request->status);
                } else {
                    $returnreplacements->where('status', $request->status);
                }
            }

            $returnreplacements = $returnreplacements->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $returnreplacements->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $returnreplacements = $returnreplacements->skip($skip)->take(config('app.pagination_records'));
            }

            $returnreplacements = $returnreplacements->get();

            $data['returnreplacements'] = $returnreplacements;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function returnReplacementDetails(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'returnreplacement_id' => 'required|exists:order_return_replaces,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplace = OrderReturnReplace::with('order', 'returnreplacement_items')
                ->where('id', $request->returnreplacement_id)
                ->whereHas('order', function ($q) {
                    $q->where('user_id', \Auth::guard('api')->id());
                })->first();

            if (!$returnreplace) {
                return response()->json(['status' => 'error', 'message' => 'Return/Replacement details not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['returnreplace'] = $returnreplace;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function timerDetails(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::with('order_products', 'shop', 'deliveryboy')->where('user_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $current_time = Carbon::now();
            $intransit_time = Carbon::parse($order->intransit_time);
            $expected_delivery = Carbon::parse($order->expected_delivery);

            $seconds_estimated = $intransit_time->diffInSeconds($expected_delivery);

            $seconds_remaining = $expected_delivery->diffInSeconds($current_time);
            if ($current_time->gt($expected_delivery) && $seconds_remaining > 0) {
                $seconds_remaining = -$seconds_remaining;
            }

            // dd($seconds_estimated);
            // dd($seconds_remaining);

            $deliverystatus_text = "On Time";
            switch ($order->delivery_status) {
                case 'ontime':
                    $deliverystatus_text = "On Time";
                    break;

                case 'delayed':
                    $deliverystatus_text = "Delayed";
                    break;
            }

            $data['intransit_time'] = $intransit_time->format('d M Y, H:i A');
            $data['current_time'] = $current_time->format('d M Y, H:i A');
            $data['expected_delivery'] = $expected_delivery->format('d M Y, H:i A');
            $data['seconds_estimated'] = $seconds_estimated;
            $data['seconds_remaining'] = $seconds_remaining;
            $data['deliverystatus_text'] = $deliverystatus_text;
            return response()->json(['status' => 'success', 'message' => "Success", 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
