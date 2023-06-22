<?php

namespace App\Helpers;

use App\Guest;
use Illuminate\Http\Request;

use App\User;
use App\Model\UserPermission;
use App\Model\Permission;
use App\Model\Cart;
use App\Model\Commission;
use App\Model\Order;
use App\Model\OrderReturnReplace;
use App\Model\Product;
use App\Model\ProductVariant;
use App\Model\Scheme;
use App\Model\Shop;
use App\Model\UserAddress;
use App\Model\UserFcmToken;
use App\Model\UserNotification;
use App\Model\WalletReport;
use Carbon\Carbon;

class Myhelper
{
    public static function can($permission, $id = "none")
    {
        if ($id == "none") {
            $id = \Auth::id();
        }

        $user = User::where('id', $id)->first();
        if (is_array($permission)) {
            $flag = Permission::whereIn('slug', $permission)->first();
            if (!$flag) {
                return false;
            }

            $mypermissions = \DB::table('permissions')->whereIn('slug', $permission)->get(['id', 'slug'])->toArray();
            if ($mypermissions) {
                foreach ($mypermissions as $value) {
                    $mypermissionss[] = $value->id;
                }
            } else {
                $mypermissionss = [];
            }
            $output = UserPermission::where('user_id', $id)->whereIn('permission_id', $mypermissionss)->count();
        } else {
            $flag = Permission::where('slug', $permission)->first();
            if (!$flag) {
                return false;
            }

            $mypermission = \DB::table('permissions')->where('slug', $permission)->first(['id']);
            if ($mypermission) {
                $output = UserPermission::where('user_id', $id)->where('permission_id', $mypermission->id)->count();
            } else {
                $output = 0;
            }
        }

        if ($output > 0 || $user->role->slug == "superadmin") {
            return true;
        } else {
            return false;
        }
    }

    public static function hasRole($roles)
    {
        if (\Auth::check()) {
            if (is_array($roles)) {
                if (in_array(\Auth::user()->role->slug, $roles)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (\Auth::user()->role->slug == $roles) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function hasNotRole($roles)
    {
        if (\Auth::check()) {
            if (is_array($roles)) {
                if (!in_array(\Auth::user()->role->slug, $roles)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (\Auth::user()->role->slug != $roles) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function FormValidator($rules, $post)
    {
        $validator = \Validator::make($post->all(), array_reverse($rules));
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(array(
                'status' => 'ERR',
                'message' => $error
            ));
        } else {
            return "no";
        }
    }

    public static function mail($view, $data, $mailto, $name, $mailvia, $namevia, $subject)
    {
        \Mail::send($view, $data, function ($message) use ($mailto, $name, $mailvia, $namevia, $subject) {
            $message->to($mailto, $name)->subject($subject);
            $message->from($mailvia, $namevia);
        });

        if (\Mail::failures()) {
            return "fail";
        }
        return "success";
    }

    public static function sms($mobile, $content)
    {
        if (config('sms.flag')) {
            $url = 'http://smsapi.syscogen.com/rest/services/sendSMS/sendGroupSms?AUTH_KEY=' . config('sms.pwd') . '&message=' . urlencode($content) . '&senderId=' . config('sms.sender') . '&routeId=1&mobileNos=' . $mobile . '&smsContentType=english';
            $result = \Myhelper::curl($url, 'GET', "", []);

            if ($result['code'] != 200) {
                \Log::info("SMS -- " . $mobile . " -- " . $content);
                \Log::info(json_encode($result));

                return false;
            }

            $doc = json_decode($result['response']);
            if ($doc->responseCode == '3001') {
                return true;
            } else {
                \Log::info("SMS -- " . $mobile . " -- " . $content);
                \Log::info(json_encode($result));

                return false;
            }
        } else {
            \Log::info("SMS -- " . $mobile . " -- " . $content);
            return false;
        }
    }

    public static function storeFcmToken($user_id, $fcm_token)
    {
        UserFcmToken::where('fcm_token', $fcm_token)->where('user_id', '!=', $user_id)->delete();

        $action = UserFcmToken::updateorcreate(
            ['fcm_token' => $fcm_token, 'user_id' => $user_id],
            ['fcm_token' => $fcm_token, 'user_id' => $user_id]
        );

        if ($action) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteFcmToken($user_id, $fcm_token)
    {
        $action = UserFcmToken::where('fcm_token', $fcm_token)->where('user_id', '=', $user_id)->delete();
        if ($action) {
            return true;
        } else {
            return false;
        }
    }

    public static function sendNotification($user_id, $heading, $content, $debug = false)
    {
        $user = User::find($user_id);

        $firebase_serverkey = null;
        if (@$user->role->slug == 'user') {
            $firebase_serverkey = config('firebasepush.userapp.serverkey');
        } elseif (@$user->role->slug == 'branch') {
            $firebase_serverkey = config('firebasepush.branchapp.serverkey');
        } elseif (@$user->role->slug == 'deliveryboy') {
            $firebase_serverkey = config('firebasepush.deliveryboyapp.serverkey');
        }

        $action = UserNotification::create([
            'user_id' => $user_id,
            'heading' => $heading,
            'content' => $content,
        ]);

        if ($action) {
            $fcm_tokens = UserFcmToken::where('user_id', $user_id)->pluck('fcm_token');

            if (count($fcm_tokens) > 0) {
                // $url = "https://onesignal.com/api/v1/notifications";

                // $header = [
                //     'Content-Type: application/json; charset=utf-8',
                //     'Authorization: Basic ' . config('onesignal.apikey')
                // ];

                // $parameters = array(
                //     'app_id' => config('onesignal.appid'),
                //     'include_player_ids' => $fcm_tokens->toArray(),
                //     'contents' => ["en" => $content],
                //     'headings' => ["en" => $heading],
                // );

                if (!$firebase_serverkey)
                    return false;

                $url = 'https://fcm.googleapis.com/fcm/send';

                $parameters = [
                    "registration_ids" => $fcm_tokens,
                    "notification" => [
                        "title" => $heading,
                        "body" => $content,
                    ]
                ];

                $header = [
                    'Authorization: key=' . $firebase_serverkey,
                    'Content-Type: application/json',
                ];

                $result = \Myhelper::curl($url, "POST", json_encode($parameters), $header);
                if ($debug == true) {
                    dd($url, json_encode($parameters), $header, $result);
                }

                if ($result['code'] != 200 || $result['error'] != "") {
                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    public static function curl($url, $method = 'GET', $parameters, $header)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if ($parameters != "") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        if (sizeof($header) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return ["response" => $response, "error" => $err, 'code' => $code];
    }

    public static function getTds($amount)
    {
        return $amount * 5 / 100;
    }

    public static function getGst($amount)
    {
        return $amount * 18 / 100;
    }

    public static function getVariantTax($variant_id)
    {
        $productvariant = ProductVariant::find($variant_id);
        if ($productvariant) {
            $purchase_price = @$productvariant->purchase_price;
            $tax_rate = @$productvariant->product->details->tax_rate;
            $branchgstin = @$productvariant->product->shop->user->documents->gstin_number;

            if ($tax_rate && $branchgstin && $purchase_price) {
                $value = (($tax_rate) / 100) * $purchase_price;
                $value = round($value, 2);
                return $value;
            }
        }

        return 0;
    }

    public static function getShopTin($shop_id)
    {
        $shop = Shop::find($shop_id);
        if ($shop) {
            $branchgstin = @$shop->user->documents->gstin_number;
            $tin = substr($branchgstin, 0, 2);

            if (is_numeric($tin))
                return $tin;
        }

        return null;
    }

    public static function getCartDeliveryCharge($amount, $address_id, $shop_id)
    {
        if (config('app.deliverycharge_status') == 'enable') {
            if (config('app.deliverycharge_freeordervalue') && $amount >= config('app.deliverycharge_freeordervalue')) {
                return 0;
            }

            $address = UserAddress::where('id', $address_id)->first();
            if (!$address) {
                return 0;
            }

            $shop = Shop::find($shop_id);
            if (!$shop) {
                return 0;
            }


            $locationDistance = \Myhelper::locationDistance($address->latitude, $address->longitude, $shop->shop_latitude, $shop->shop_longitude, 'km');
            $delivery_charge = (float) config('app.deliverycharge_perkm') * (float) $locationDistance;

            if (config('app.deliverycharge_min') && $delivery_charge < (float) config('app.deliverycharge_min')) {
                $delivery_charge = (float) config('app.deliverycharge_min') * (float) $locationDistance;
            }
            else if (config('app.upto_3km') && $locationDistance >=1 && $locationDistance <3){
                $delivery_charge = (float) config('app.upto_3km') * (float) $locationDistance;
            } else if (config('app._3km_to_5km') && $locationDistance >=3 && $locationDistance <5){
                $delivery_charge = (float) config('app._3km_to_5km') * (float) $locationDistance;
            }else{
                $delivery_charge = (float) config('app._5km_to_8km') * (float) $locationDistance;
            }

            return ceil($delivery_charge);
        } else {
            return 0;
        }
    }

    public static function getShop($user_id = "none")
    {
        if ($user_id == "none") {
            $user_id = \Auth::id();
        }

        $user = User::findorfail($user_id);
        if (in_array($user->role->slug, ['superadmin', 'admin'])) {
            // $shop = Shop::with('user')->whereHas('user', function ($q) {
            //     $q->whereHas('role', function ($qr) {
            //         $qr->where('slug', 'superadmin');
            //     });
            // })->first();

            // if (!$shop) {
            //     $superadmin = User::whereHas('role', function ($q) {
            //         $q->where('slug', 'superadmin');
            //     })->first();

            //     $shop = Shop::create(['user_id' => $superadmin->id]);
            // }

            // return $shop->id;

            return null;
        } elseif (in_array($user->role->slug, ['branch'])) {
            $shop = Shop::with('user')->whereHas('user', function ($q) use ($user_id) {
                $q->where('id', $user_id);
            })->first();

            if (!$shop) {
                $shop = Shop::create(['user_id' => $user_id]);
            }

            return $shop->id;
        }

        return 0;
    }

    public static function getAccesedShops($user_id = "none")
    {
        if ($user_id == "none") {
            $user_id = \Auth::id();
        }

        $user = User::findorfail($user_id);
        if (in_array($user->role->slug, ['superadmin', 'admin'])) {
            return Shop::pluck('id')->all();
        } else if (in_array($user->role->slug, ['deliveryboy'])) {
            return Shop::whereIn('id', $user->alloted_shops)->get()->pluck('id');
        }

        return [];
    }

    public static function validatePurchaseLocation($address_id, $shop_id)
    {
        $shop = Shop::find($shop_id);
        if (!$shop || $shop->status != '1') {
            return (object) ['status' => false, 'message' => 'Store is currently unavailable'];
        }

        $address = UserAddress::where('id', $address_id)->first();
        if (!$address) {
            return (object) ['status' => false, 'message' => 'Address selected is invalid'];
        }

        $locationDistance = \Myhelper::locationDistance($address->latitude, $address->longitude, $shop->shop_latitude, $shop->shop_longitude);
        if ($locationDistance > (float) $shop->shop_delivery_radius) {
            return (object) ['status' => false, 'message' => 'Oopss!! We don\'t deliver to the selected address'];
        }

        return (object) ['status' => true, 'message' => 'Success'];
    }

    public static function locationDistance($lat1, $lon1, $lat2, $lon2, $rtrn = 'meters')
    {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lon1 *= $pi80;
        $lat2 *= $pi80;
        $lon2 *= $pi80;
        $r = 6372.797; // mean radius of Earth in km
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $r * $c;
        //echo ' '.$km;

        if ($rtrn == 'meters') {
            return $km * 1000;
        } else if ($rtrn == 'km') {
            return $km;
        }
    }

    public static function updateOrderStatusLog($order_id, $customkey = "orderstatus")
    {
        try {
            $order = Order::find($order_id);
            if ($order && !in_array($order->status, ['paymentinitiated', 'paymentfailed'])) {
                $status_log = $order->status_log;

                if ($customkey == "orderstatus") {
                    $flag = false;
                    foreach ($status_log as $key => $item) {
                        if ($item->key == $order->status) {
                            $status_log[$key]->timestamp = Carbon::now();
                            $flag = true;
                            break;
                        } elseif ($flag == false && !$status_log[$key]->timestamp && !in_array($order->status, ['paymentinitiated', 'paymentfailed', 'cancelled', 'returned'])) {
                            $status_log[$key]->timestamp = Carbon::now();
                        }
                    }

                    if ($order->status == 'cancelled') {
                        if ($flag === false) {
                            array_push($status_log, (object)[
                                'key' => 'cancelled',
                                'label' => 'Cancelled',
                                'timestamp' => Carbon::now(),
                            ]);
                        }

                        $last_timestamp_found = false;
                        foreach ($status_log as $key => $item) {
                            if ($item->timestamp != null && $last_timestamp_found == false) {
                                $last_timestamp_found = true;
                            }

                            if ($item->timestamp == null && $last_timestamp_found == true) {
                                unset($status_log[$key]);
                            }
                        }
                    }
                } elseif ($customkey == "deliveryboyassigned" || $customkey == "deliveryreachedstore") {
                    $new_log = [];
                    foreach ($status_log as $key => $item) {
                        if ($item->key != $order->status) {
                            array_push($new_log, $item);
                        } else {
                            array_push($new_log, $item);

                            switch ($customkey) {
                                case 'deliveryboyassigned':
                                    array_push($new_log, (object)[
                                        'key' => 'deliveryboyassigned',
                                        'label' => "Delivery Partner Assigned",
                                        'timestamp' => Carbon::now(),
                                    ]);
                                    break;

                                case 'deliveryreachedstore':
                                    array_push($new_log, (object)[
                                        'key' => 'deliveryreachedstore',
                                        'label' => "Delivery Partner Reached Store",
                                        'timestamp' => Carbon::now(),
                                    ]);
                                    break;
                            }
                        }
                    }

                    $status_log = $new_log;
                }

                $action = Order::where('id', $order->id)->update(['status_log' => json_encode(array_values($status_log))]);
                if ($action) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function updateOrderStatusAction($order_id, $spcl_status = 'none')
    {
        $order = Order::with('shop')->find($order_id);

        if ($spcl_status == 'none') {
            switch ($order->status) {
                case 'paymentfailed':
                    $notify_content = "Payment for the order failed, please reinitiate the payment to continue. If any amount is deducted from your bank account, please contact our support team for further assistance";
                    \Myhelper::sendNotification($order->user_id, "Order Payment Failed", $notify_content);
                    break;

                case 'received':
                    $notify_content = "Order No. " . $order->code . " has been placed successfully. You will be notified once the order will be accepted";
                    \Myhelper::sendNotification($order->user_id, "Order Placed", $notify_content);

                    $content = 'Hey ' . @$order->user->name . ', Order no ' . $order->code . ' has been placed successfully. You will be notified once the order will be accepted. Thanks, Team GoPotu';
                    \Myhelper::sms(@$order->user->mobile, $content);

                    $shop = Shop::with('user')->where('id', $order->shop_id)->first();
                    $notify_content = "There is a new order for your store Order No. " . $order->code . ". Please accept the order";

                    $content = "Hey " . $shop->user->name . ", there is a new order for your store " . $shop->shop_name . " order no " . $order->code . ". Please accept the order. Thanks, Team GoPotu";
                    \Myhelper::sms(@$shop->user->mobile, $content);

                    \Myhelper::sendNotification($shop->user_id, "New Order Placed", $notify_content);
                    break;

                case 'accepted':
                    $cust_lat = $order->cust_latitude;
                    $cust_long = $order->cust_longitude;
                    $shop_lat = $order->shop->shop_latitude;
                    $shop_long = $order->shop->shop_longitude;

                    $distance = \Myhelper::getDistanceMatric($shop_lat, $shop_long, $cust_lat, $cust_long);
                    $order->expected_delivery = Carbon::parse($order->expected_intransit)->addSeconds($distance->duration_value);
                    $order->save();

                    $notify_content = "Hooray!! Your Order No. " . $order->code . " has been accepted, and we are expecting it to be delivered at " . Carbon::parse($order->expected_delivery)->format('d M y - h:i A');
                    Myhelper::sendNotification($order->user_id, "Order Accepted", $notify_content);
                    break;

                case 'processed':
                    $notify_content = "Your Order No. " . $order->code . " is under processing in the store, and will be ready for pickup soon";
                    Myhelper::sendNotification($order->user_id, "Order Processing", $notify_content);
                    break;

                case 'intransit':
                    $notify_content = "Order No. " . $order->code . " is ready at the store, and it is waiting for the delivery partner to pick it up. Order will be out for delivery soon, and please be available at your location";
                    Myhelper::sendNotification($order->user_id, "Order ready for Pickup", $notify_content);

                    $notify_content = "Order No. " . $order->code . " is ready at the store, and it is waiting for you to pick it up. Please pick the order up from the store and try to deliver the order within " . Carbon::parse($order->expected_delivery)->format('d M y - h:i A') . " at the customer location";
                    Myhelper::sendNotification($order->deliveryboy_id, "Order ready for Pickup", $notify_content);

                    Order::where('id', $order->id)->update(['intransit_time' => Carbon::now()->format('Y-m-d H:i:s')]);
                    break;

                case 'outfordelivery':
                    $notify_content = "Our delivery partner " . $order->deliveryboy->name . " is out for delivery with your Order No. " . $order->code . ". Please be available at your location to receive your order";
                    Myhelper::sendNotification($order->user_id, "Order Out for Delivery", $notify_content);

                    Order::where('id', $order->id)->update(['outfordelivery_time' => Carbon::now()->format('Y-m-d H:i:s')]);
                    break;

                case 'delivered':
                    $notify_content = "Order No. " . $order->code . " has been delivered to you. Please rate your experience with us from your account. Happy to serve you! Hope to see you again";
                    Myhelper::sendNotification($order->user_id, "Order Delivered", $notify_content);

                    $content = "Hey " . @$order->user->name . ", Order no " . $order->code . " has been delivered to the address. Please rate your experience with us from your account. Happy to serve you! Hope to see you again. Thanks, GoPotu Team";
                    \Myhelper::sms(@$order->user->mobile, $content);

                    $shop = Shop::with('user')->where('id', $order->shop_id)->first();
                    if ($shop && in_array($shop->user->role->slug, ['branch'])) {
                        $exist = WalletReport::where('wallet_type', 'branchwallet')->where('ref_id', $order->id)->where('service', 'order')->where('user_id', $shop->user->id)->exists();
                        if (!$exist) {
                            $amount = $order->item_total - $order->admin_charge;

                            $report = [
                                'user_id' => $shop->user->id,
                                'ref_id' => $order->id,
                                'wallet_type' => 'branchwallet',
                                'balance' => $shop->user->branchwallet,
                                'trans_type' => 'credit',
                                'amount' => $amount,
                                'remarks' => "Payment credited for Order No " . $order->code,
                            ];

                            $transaction = User::where('id', $shop->user->id)->increment('branchwallet', $amount);
                            if ($transaction) {
                                WalletReport::create($report);
                            }
                        }
                    }

                    if ($order->deliveryboy_id) {
                        $deliveryboy = User::find($order->deliveryboy_id);
                        if ($deliveryboy) {
                            if ($order->payment_mode == 'cash' && $order->payable_amount > 0) {
                                $exist = WalletReport::where('wallet_type', 'creditwallet')->where('ref_id', $order->id)->where('service', 'order')->where('user_id', $deliveryboy->id)->exists();
                                if (!$exist) {
                                    $amount = $order->payable_amount;

                                    $report = [
                                        'user_id' => $deliveryboy->id,
                                        'ref_id' => $order->id,
                                        'wallet_type' => 'creditwallet',
                                        'balance' => $deliveryboy->creditwallet,
                                        'trans_type' => 'credit',
                                        'amount' => $amount,
                                        'remarks' => "Cash payment received for Order No " . $order->code,
                                    ];

                                    $transaction = User::where('id', $deliveryboy->id)->increment('creditwallet', $amount);
                                    if ($transaction) {
                                        WalletReport::create($report);
                                    }
                                }
                            }

                            if ($order->delivery_charge > 0) {
                                $exist = WalletReport::where('wallet_type', 'riderwallet')->where('ref_id', $order->id)->where('service', 'order')->where('user_id', $deliveryboy->id)->exists();
                                if (!$exist) {
                                    $amount = $order->delivery_charge;

                                    $report = [
                                        'user_id' => $deliveryboy->id,
                                        'ref_id' => $order->id,
                                        'wallet_type' => 'riderwallet',
                                        'balance' => $deliveryboy->riderwallet,
                                        'trans_type' => 'credit',
                                        'amount' => $amount,
                                        'remarks' => "Delivery charge received for Order No " . $order->code,
                                    ];

                                    $transaction = User::where('id', $deliveryboy->id)->increment('riderwallet', $amount);
                                    if ($transaction) {
                                        WalletReport::create($report);
                                    }
                                }
                            }
                        }
                    }

                    $user = User::where('id', $order->user->id)->where('status', '1')->whereHas('role', function ($q) {
                        $q->where('slug', 'user');
                    })->first();

                    if ($user) {
                        /** DISPATCH FIRST ORDER CASHBACK  */
                        if (Order::where('user_id', $user->id)->whereIn('status', ['delivered'])->count() == 1) {
                            $exist = WalletReport::where('wallet_type', 'userwallet')->where('user_id', $user->id)->where('service', 'firstorder')->exists();
                            if (!$exist) {
                                $userwallet_type = config('firstorder.userwallet.type');
                                $userwallet_value = config('firstorder.userwallet.value');

                                if ($userwallet_value > 0) {
                                    $amount = $userwallet_value;
                                    if ($userwallet_type == 'percentage') {
                                        $amount = ($userwallet_value / 100) * $order->item_total;
                                    }

                                    $report = [
                                        'user_id' => $user->id,
                                        'ref_id' => $order->id,
                                        'wallet_type' => 'userwallet',
                                        'balance' => $user->userwallet,
                                        'trans_type' => 'credit',
                                        'amount' => $amount,
                                        'remarks' => "Cashback for your first order received",
                                        'service' => "firstorder",
                                    ];

                                    $transaction = User::where('id', $user->id)->increment('userwallet', $amount);
                                    if ($transaction) {
                                        WalletReport::create($report);

                                        $notify_content = "Rs.$amount has been credited to your wallet as a cashback for your first order";
                                        Myhelper::sendNotification($user->id, "Cashback received in your Wallet", $notify_content);
                                    }
                                }
                            }

                            if ($user->parent_id) {
                                $parentuser = User::where('id', $user->parent_id)->where('status', '1')->whereHas('role', function ($q) {
                                    $q->where('slug', 'user');
                                })->first();

                                if ($parentuser) {
                                    $exist = WalletReport::where('wallet_type', 'userwallet')->where('user_id', $parentuser->id)->where('ref_id', $user->id)->where('service', 'referral')->exists();
                                    if (!$exist) {
                                        $parentwallet_type = config('firstorder.parentwallet.type');
                                        $parentwallet_value = config('firstorder.parentwallet.value');

                                        if ($parentwallet_value > 0) {
                                            $amount = $parentwallet_value;
                                            if ($parentwallet_type == 'percentage') {
                                                $amount = ($parentwallet_value / $order->item_total) * 100;
                                            }

                                            $report = [
                                                'user_id' => $parentuser->id,
                                                'ref_id' => $user->id,
                                                'wallet_type' => 'userwallet',
                                                'balance' => $parentuser->userwallet,
                                                'trans_type' => 'credit',
                                                'amount' => $amount,
                                                'remarks' => "Referral bonus received for joining " . $order->user->name,
                                                'service' => "referral",
                                            ];

                                            $transaction = User::where('id', $parentuser->id)->increment('userwallet', $amount);
                                            if ($transaction) {
                                                WalletReport::create($report);

                                                $notify_content = "Rs.$amount has been credited to your wallet as a referral bonus for joining " . $order->user->name;
                                                Myhelper::sendNotification($parentuser->id, "Referral Bonus Received", $notify_content);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        /** DISPATCH IF ORDER WALLET CASHBACK EXIST */
                        if ($order->wallet_cashback > 0) {
                            $user = User::find($user->id);
                            $amount = $order->wallet_cashback;

                            $exist = WalletReport::where('wallet_type', 'userwallet')->where('user_id', $user->id)->where('ref_id', $order->id)->where('service', 'ordercashback')->where('trans_type', 'credit')->exists();
                            if (!$exist) {
                                $report = [
                                    'user_id' => $user->id,
                                    'ref_id' => $order->id,
                                    'wallet_type' => 'userwallet',
                                    'balance' => $user->userwallet,
                                    'trans_type' => 'credit',
                                    'amount' => $amount,
                                    'remarks' => "Cashback received for Order No " . $order->code,
                                    'service' => "ordercashback",
                                ];

                                $transaction = User::where('id', $user->id)->increment('userwallet', $amount);
                                if ($transaction) {
                                    WalletReport::create($report);

                                    $notify_content = "Rs.$amount has been credited to your wallet as a cashback for your Order No: " . $order->code;
                                    Myhelper::sendNotification($user->id, "Order Cashback Received", $notify_content);
                                }
                            }
                        }
                    }

                    Order::where('id', $order->id)->update(['delivered_time' => Carbon::now()->format('Y-m-d H:i:s')]);
                    break;

                case 'cancelled':
                    $notify_content = "Order No. " . $order->code . " has been cancelled successfully. If you have cancelled the order by mistake please contact our support team.";
                    Myhelper::sendNotification($order->user_id, "Order Cancelled", $notify_content);

                    if ($order->wallet_deducted > 0) {
                        $user = User::find($order->user_id);
                        $amount = $order->wallet_deducted;

                        $exist = WalletReport::where('wallet_type', 'userwallet')->where('user_id', $user->id)->where('ref_id', $order->id)->where('service', 'ordercancellation')->where('trans_type', 'credit')->exists();
                        if (!$exist) {
                            $report = [
                                'user_id' => $user->id,
                                'ref_id' => $order->id,
                                'wallet_type' => 'userwallet',
                                'balance' => $user->userwallet,
                                'trans_type' => 'credit',
                                'amount' => $amount,
                                'remarks' => "Refund for cancellation of Order No " . $order->code,
                                'service' => "ordercancellation",
                            ];

                            $transaction = User::where('id', $user->id)->increment('userwallet', $amount);
                            if ($transaction) {
                                WalletReport::create($report);

                                $notify_content = "Rs.$amount has been credited to your wallet for cancellation of your Order No: " . $order->code;
                                Myhelper::sendNotification($user->id, "Order Cancellation Refund Received", $notify_content);
                            }
                        }
                    }
                    break;
            }
        } else {
            switch ($spcl_status) {
                case 'deliveryassigned':
                    $notify_content = "Order No. " . $order->code . " has been assigned to you. Please visit the store before " . Carbon::parse($order->expected_intransit)->format('d M y - h:i A') . " to pick up the order";
                    \Myhelper::sendNotification($order->deliveryboy_id, "New Order Assigned", $notify_content);
                    break;

                case 'deliveryaccepted':
                    $notify_content = $order->deliveryboy->name . " has been assigned as the delivery boy for your Order No. " . $order->code . " and is on-way to the store to pick up your order";
                    \Myhelper::sendNotification($order->user_id, "Delivery Partner assigned for your Order", $notify_content);

                    $notify_content = $order->deliveryboy->name . " is assigned for Order No. " . $order->code . " and will reach the store before " . Carbon::parse($order->expected_intransit)->format('d M y - h:i A') . ". Please be ready with the order";
                    \Myhelper::sendNotification($order->shop->user_id, "Delivery Partner is on the way", $notify_content);
                    break;

                case 'deliveryrejected':
                    break;

                case 'deliveryreachedstore':
                    $notify_content = "The delivery partner has reached the store to pickup the Order No. " . $order->code . "";
                    \Myhelper::sendNotification($order->user_id, "Delivery partner reached the store", $notify_content);

                    $shop = Shop::with('user')->where('id', $order->shop_id)->first();
                    $notify_content = "The delivery partner has reached the store to pickup the Order No. " . $order->code . ". Please make sure the order is ready for pickup once the processing is completed";
                    \Myhelper::sendNotification($shop->user_id, "Delivery partner reached the store", $notify_content);
                    break;

                default:
                    return false;
                    break;
            }
        }
    }

    public static function getDefaultAddress($user_id = null, $guest_id = null)
    {
        return UserAddress::where('user_id', $user_id)->where('guest_id', $guest_id)->where('is_default', 1)->first();
    }

    public static function getAvailableShops($latitude, $longitude)
    {
        $result = Shop::query();

        $result = $result->select("id", "shop_delivery_radius", \DB::raw("6371 * acos(cos(radians(" . $latitude . "))
                                * cos(radians(shop_latitude)) * cos(radians(shop_longitude) - radians(" . $longitude . "))
                                + sin(radians(" . $latitude . ")) * sin(radians(shop_latitude))) AS distance"));

        $result = $result->orderBy('distance', 'asc');
        $result = $result->get();

        // dd($result->toArray());


        $output = array();
        foreach ($result as $key => $res) {
            if ($res->distance <= ($res->shop_delivery_radius / 1000)) {
                array_push($output, $res->id);
            }
        }

        return $output;
    }

    public static function getAvailableDrivers($srch_latitude, $srch_longitude, $ref_id = null, $_DEFKMRANGE = 10, $dashboard_action = false, $type = 'order')
    {
        $result = User::where('status', 1)
            ->where('online', 1)
            ->where('status', 1)
            ->whereHas('role', function ($q) {
                $q->where('slug', 'deliveryboy');
            });

        $result = $result->select("id", \DB::raw("6371 * acos(cos(radians(" . $srch_latitude . "))
        * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $srch_longitude . "))
        + sin(radians(" . $srch_latitude . ")) * sin(radians(latitude))) AS distance"));

        $result = $result->having('distance', '<', $_DEFKMRANGE);
        $result = $result->orderBy('distance', 'asc');
        $result = $result->pluck('id');

        $deliveryboyids = [];
        foreach ($result as $key => $deliveryboy_id) {
            if ($dashboard_action == false) {
                $liverorder_exists = Order::where('deliveryboy_id', $deliveryboy_id)
                    ->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery'])
                    ->exists();

                if ($liverorder_exists) {
                    continue;
                }


                $liverreturnreplace_exists = OrderReturnReplace::where('deliveryboy_id', $deliveryboy_id)
                    ->whereIn('status', ['initiated', 'accepted', 'processed', 'intransit', 'outfordelivery', 'outforpickup', 'outforstore'])
                    ->exists();

                if ($liverreturnreplace_exists) {
                    continue;
                }
            }

            if ($dashboard_action == false && $ref_id && $type == 'order') {
                $prevrej_exists = Order::where('id', $ref_id)
                    ->whereHas('deliveryboy_logs', function ($q) use ($deliveryboy_id) {
                        $q->where('deliveryboy_id', $deliveryboy_id);
                        $q->whereIn('status', ['rejected']);
                    })->exists();

                if ($prevrej_exists) {
                    continue;
                }
            } elseif ($dashboard_action == false && $ref_id && $type == 'returnreplace') {
                $prevrej_exists = OrderReturnReplace::where('id', $ref_id)
                    ->whereHas('deliveryboy_logs', function ($q) use ($deliveryboy_id) {
                        $q->where('deliveryboy_id', $deliveryboy_id);
                        $q->whereIn('status', ['rejected']);
                    })->exists();

                if ($prevrej_exists) {
                    continue;
                }
            }

            array_push($deliveryboyids, $deliveryboy_id);
        }

        return $deliveryboyids;
    }

    public static function formatApiResponseData($data)
    {
        if (is_array($data) && empty($data)) {
            $data = null;
        }

        return $data;
    }

    public static function getCommission($amount, $provider_id, $user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return 0;
        }

        $scheme = Scheme::find($user->scheme_id);
        if (!$scheme) {
            return 0;
        }

        $commission = Commission::where('provider_id', $provider_id)->where('scheme_id', $scheme->id)->first();
        if ($commission) {
            if ($commission->type == 'percent') {
                $value = ($commission->value / 100) * $amount;
            } else {
                $value = $commission->value;
            }

            return number_format((float)$value, 2, '.', '');
        } else {
            return 0;
        }
    }

    public static function getDistanceMatric($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&destinations=$destination_latitude,$destination_longitude&origins=$origin_latitude,$origin_longitude&units=imperial&key=" . config('google.apikey');

        $result = Myhelper::curl($url, 'GET', "", []);
        if ($result['code'] == 200) {
            $doc = json_decode($result['response']);

            $obj = @$doc->rows[0]->elements[0];
            if ($obj) {
                return (object)[
                    'distance_value' => @$obj->distance->value ?? 0,
                    'distance_text' => @$obj->distance->text ?? "0 km",
                    'duration_value' => @$obj->duration->value ?? 0,
                    'duration_text' => @$obj->duration->text ?? "0 min",
                ];
            }
        }

        return (object)[
            'distance_value' => 0,
            'distance_text' => "0 km",
            'duration_value' => 0,
            'duration_text' => "0 min",
        ];
    }

    public static function showTwoDecimalNumber($value)
    {
        $value = number_format((float)$value, 2, '.', '');  // Outputs -> 105.00
        return $value;
    }

    public static function getShopAvgRating($shop_id)
    {
        $total_rating = Order::where('shop_id', $shop_id)->where('shop_rating', '!=', NULL)->sum('shop_rating');
        $total_orders = Order::where('shop_id', $shop_id)->where('shop_rating', '!=', NULL)->count();

        if ($total_rating == 0 || $total_orders == 0) {
            return 0;
        }

        return $total_rating / $total_orders;
    }

    public static function syncGuestWithUser($user_id, $guest_id)
    {
        $guestaddresses = UserAddress::where('user_id', null)->where('guest_id', $guest_id)->get();
        if (count($guestaddresses) > 0) {
            UserAddress::where('user_id', $user_id)->update(['is_default' => false]); // Remove Default Address for User

            foreach ($guestaddresses as $guestaddress) {
                $prev_type_address = UserAddress::select('id')->where('user_id', $user_id)->where('type', $guestaddress->type)->first();
                $id = $prev_type_address ? $prev_type_address->id : null;

                $document['user_id'] = $user_id;
                $document['guest_id'] = null;
                $document['is_default'] = $guestaddress->is_default;
                $document['type'] = $guestaddress->type;
                $document['name'] = $guestaddress->name;
                $document['mobile'] = $guestaddress->mobile;
                $document['alternative_mobile'] = $guestaddress->alternative_mobile;
                $document['location'] = $guestaddress->location;
                $document['latitude'] = $guestaddress->latitude;
                $document['longitude'] = $guestaddress->longitude;
                $document['full_address'] = json_encode((object)[
                    'address_line1' => $guestaddress->address_line1,
                    'address_line2' => $guestaddress->address_line2,
                    'postal_code' => $guestaddress->postal_code,
                    'city' => $guestaddress->city,
                    'state' => $guestaddress->state,
                    'country' => $guestaddress->country,
                    'landmark' => $guestaddress->landmark,
                ]);

                UserAddress::updateorcreate(['id' => $id], $document);
            }

            UserAddress::where('guest_id', $guest_id)->delete(); // Remove Address for Guest
        }

        $guestcartids = Cart::where('user_id', null)->where('guest_id', $guest_id)->pluck('id');
        if (count($guestcartids) > 0) {
            Cart::where('user_id', $user_id)->delete(); // Delete existing cart for the User

            Cart::whereIn('id', $guestcartids)->update([ // Sync cart items from Guest to the User
                'guest_id' => null,
                'user_id' => $user_id,
            ]);
        }

        Guest::where('id', $guest_id)->delete();
    }

    public static function updateOrderReturnReplaceStatusLog($order_id, $customkey = "orderstatus")
    {
        try {
            $returnreplacement = OrderReturnReplace::find($order_id);

            if ($returnreplacement) {
                $status_log = $returnreplacement->status_log;

                if ($customkey == "orderstatus") {
                    $flag = false;
                    foreach ($status_log as $key => $item) {
                        if ($item->key == $returnreplacement->status) {
                            $status_log[$key]->timestamp = Carbon::now();
                            $flag = true;
                            break;
                        } elseif ($flag == false && !$status_log[$key]->timestamp && !in_array($returnreplacement->status, ['rejected'])) {
                            $status_log[$key]->timestamp = Carbon::now();
                        }
                    }

                    if ($returnreplacement->status == 'rejected') {
                        if ($flag === false) {
                            array_push($status_log, (object)[
                                'key' => 'rejected',
                                'label' => @config('returnreplacestatus.options')['rejected'] ?? "Rejected",
                                'timestamp' => Carbon::now(),
                            ]);
                        }

                        $last_timestamp_found = false;
                        foreach ($status_log as $key => $item) {
                            if ($item->timestamp != null && $last_timestamp_found == false) {
                                $last_timestamp_found = true;
                            }

                            if ($item->timestamp == null && $last_timestamp_found == true) {
                                unset($status_log[$key]);
                            }
                        }
                    }
                } elseif ($customkey == "deliveryboyassigned" || $customkey == "deliveryreachedstore") {
                    $new_log = [];
                    foreach ($status_log as $key => $item) {
                        if ($item->key != $returnreplacement->status) {
                            array_push($new_log, $item);
                        } else {
                            array_push($new_log, $item);

                            switch ($customkey) {
                                case 'deliveryboyassigned':
                                    array_push($new_log, (object)[
                                        'key' => 'deliveryboyassigned',
                                        'label' => "Delivery Partner Assigned",
                                        'timestamp' => Carbon::now(),
                                    ]);
                                    break;

                                case 'deliveryreachedstore':
                                    array_push($new_log, (object)[
                                        'key' => 'deliveryreachedstore',
                                        'label' => "Delivery Partner Reached Store",
                                        'timestamp' => Carbon::now(),
                                    ]);
                                    break;
                            }
                        }
                    }

                    $status_log = $new_log;
                }

                // dd($status_log);

                $action = OrderReturnReplace::where('id', $returnreplacement->id)->update(['status_log' => json_encode(array_values($status_log))]);
                if ($action) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function updateReturnReplaceStatusAction($returnreplace_id, $spcl_status = 'none')
    {
        $returnreplace = OrderReturnReplace::find($returnreplace_id);

        if ($spcl_status == 'none') {
            switch ($returnreplace->status) {
                case 'initiated':
                    $notify_content = "A " . $returnreplace->type . " request has been initiated for your Order No. " . $returnreplace->order->code . ". Please check the details. " . ucfirst($returnreplace->type) . " request no " . $returnreplace->code;
                    \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Initiated", $notify_content);

                    $shop = Shop::with('user')->where('id', $returnreplace->order->shop_id)->first();
                    $notify_content = "There is a new " . $returnreplace->type . " request for your store Order No. " . $returnreplace->order->code . ". Please accept the request, return request no " . $returnreplace->code;
                    \Myhelper::sendNotification($shop->user_id, "New " . ucfirst($returnreplace->type) . " Initiated", $notify_content);
                    break;

                case 'accepted':
                    if ($returnreplace->type == 'return')
                        $notify_content = "Your " . $returnreplace->type . " request no " . $returnreplace->code . ", has been accepted for your Order No. " . $returnreplace->order->code;
                    else if ($returnreplace->type == 'replace')
                        $notify_content = "Your " . $returnreplace->type . " request no " . $returnreplace->code . ", has been accepted for your Order No. " . $returnreplace->order->code . ", and we are expecting it to be delivered at " . Carbon::parse($returnreplace->expected_delivery)->format('d M y - h:i A');

                    \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Accepted", $notify_content);
                    break;

                case 'processed':
                    $notify_content = "Your " . $returnreplace->type . " request no " . $returnreplace->code . ", for your Order No. " . $returnreplace->order->code . " has start processing by the merchant";
                    \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Processing", $notify_content);
                    break;

                case 'intransit':
                    if ($returnreplace->type == 'replace') {
                        $notify_content = "Your " . $returnreplace->type . " request no " . $returnreplace->code . ", for your Order No. " . $returnreplace->order->code . " is ready for pickup at the store";
                        \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Ready for Pickup", $notify_content);


                        $notify_content = ucfirst($returnreplace->type) . " request no " . $returnreplace->code . ", is ready for pickup at the store, please pickup the item(s) from the store";
                        \Myhelper::sendNotification($returnreplace->deliveryboy_id, ucfirst($returnreplace->type) . " Ready for Pickup", $notify_content);
                    }
                    break;

                case 'outforpickup':
                    if ($returnreplace->type == 'return') {
                        $notify_content = "The delivery partner is out to pick up your return item(s) for Return Request no " . $returnreplace->code . ", from your location. Please be ready with the item(s)";
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Delivery Partner Out for Pickup", $notify_content);
                    }
                    break;

                case 'outfordelivery':
                    if ($returnreplace->type == 'replace') {
                        $notify_content = "Our delivery partner " . $returnreplace->deliveryboy->name . " is out for delivery with your " . $returnreplace->type . " request no " . $returnreplace->code . ", please be available at your location to receive the item(s)";
                        \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Out for Delivery", $notify_content);
                    }
                    break;

                case 'outforstore':
                    if ($returnreplace->type == 'return') {
                        $notify_content = "The delivery partner has picked up the returned item(s) for Return Request no " . $returnreplace->code . ". The refund will be processed shortly once the item(s) are verified from the merchant";
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Return Items Picked Up", $notify_content);

                        $notify_content = "The delivery partner has picked up the returned item(s) for Return Request no " . $returnreplace->code . ", from the customer location and the partner is on the way to the store to deliver";
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Delivery Partner Reaching", $notify_content);
                    } else if ($returnreplace->type == 'replace') {
                        $notify_content = "The delivery partner has delivered and picked up the replaced item(s) for Replace Request no " . $returnreplace->code . ". The refund will be processed shortly once the item(s) are verified from the merchant";
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Replaced Items Delivered", $notify_content);

                        $notify_content = "The delivery partner has delivered and picked up the replaced item(s) for Replace Request no " . $returnreplace->code . ", from the customer location and the partner is on the way to the store to deliver";
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Delivery Partner Reaching", $notify_content);
                    }
                    break;

                case 'deliveredtostore':
                    if ($returnreplace->type == 'return') {
                        $notify_content = "The delivery partner has delivered the returned item(s) for Return Request no " . $returnreplace->code;
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Return Items Delivered", $notify_content);
                    } else if ($returnreplace->type == 'replace') {
                        $notify_content = "The delivery partner has delivered the replaced item(s) for Replace Request no " . $returnreplace->code;
                        \Myhelper::sendNotification($returnreplace->order->user_id, "Replace Items Delivered", $notify_content);
                    }
                    break;

                case 'rejected':
                    $notify_content = "Your " . $returnreplace->type . " request no " . $returnreplace->code . ", has been rejected for your Order No. " . $returnreplace->order->code;
                    \Myhelper::sendNotification($returnreplace->order->user_id, ucfirst($returnreplace->type) . " Rejected", $notify_content);
                    break;
            }

            if (($returnreplace->type == 'return' && $returnreplace->status == 'deliveredtostore') || ($returnreplace->type == 'replace' && $returnreplace->status == 'deliveredtostore')) {
                // Dispatch Delivery Charge for Rider
                if ($returnreplace->deliveryboy_id && $returnreplace->delivery_charge > 0) {
                    $deliveryboy = User::find($returnreplace->deliveryboy_id);
                    if ($deliveryboy) {
                        if ($returnreplace->delivery_charge > 0) {
                            $exist = WalletReport::where('wallet_type', 'riderwallet')->where('ref_id', $returnreplace->id)->where('service', 'returnreplace')->where('user_id', $deliveryboy->id)->exists();
                            if (!$exist) {
                                $amount = $returnreplace->delivery_charge;

                                $report = [
                                    'user_id' => $deliveryboy->id,
                                    'ref_id' => $returnreplace->id,
                                    'wallet_type' => 'riderwallet',
                                    'balance' => $deliveryboy->riderwallet,
                                    'trans_type' => 'credit',
                                    'amount' => $amount,
                                    'remarks' => "Delivery charge received for Return/Replace Job No " . $returnreplace->code,
                                    'service' => 'returnreplace'
                                ];

                                $transaction = User::where('id', $deliveryboy->id)->increment('riderwallet', $amount);
                                if ($transaction) {
                                    WalletReport::create($report);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            switch ($spcl_status) {
                case 'deliveryassigned':
                    if ($returnreplace->type == 'return')
                        $notify_content = ucfirst($returnreplace->type) . " request no " . $returnreplace->code . " has been assigned to you. Please visit the customer location to pick-up the return item(s)";
                    if ($returnreplace->type == 'replace')
                        $notify_content = ucfirst($returnreplace->type) . " request no " . $returnreplace->code . " has been assigned to you. Please visit the store to pick-up the replacement item(s)";

                    \Myhelper::sendNotification($returnreplace->deliveryboy_id, "New " . ucfirst($returnreplace->type) . " Job Assigned", $notify_content);
                    break;

                case 'deliveryaccepted':
                    if ($returnreplace->type == 'return')
                        $notify_content = $returnreplace->deliveryboy->name . " has been assigned as the delivery partner for your " . $returnreplace->type . " request no " . $returnreplace->code . " and is on-way to your location to pick up your return item(s)";
                    if ($returnreplace->type == 'replace')
                        $notify_content = $returnreplace->deliveryboy->name . " has been assigned as the delivery partner for your " . $returnreplace->type . " request no " . $returnreplace->code . " and is on-way to the store to pick up your replacement item(s)";

                    \Myhelper::sendNotification($returnreplace->order->user_id, "Delivery Partner assigned for your " . ucfirst($returnreplace->type) . " Request", $notify_content);


                    if ($returnreplace->type == 'return')
                        $notify_content = $returnreplace->deliveryboy->name . " is assigned for " . $returnreplace->type . " request no " . $returnreplace->code . " and is on the way to customer's location to pickup the item(s)";
                    if ($returnreplace->type == 'replace')
                        $notify_content = $returnreplace->deliveryboy->name . " is assigned for " . $returnreplace->type . " request no " . $returnreplace->code . " and is on the way to store to pickup the item(s)";

                    \Myhelper::sendNotification($returnreplace->order->shop->user_id, "Delivery Partner assigned for " . ucfirst($returnreplace->type) . " Request", $notify_content);
                    break;

                case 'deliveryrejected':
                    break;

                default:
                    return false;
                    break;
            }
        }
    }

    public static function onConfirmOrderReduceStock($order_id)
    {
        $order = Order::with('order_products')->where('id', $order_id)->first();
        if ($order) {
            foreach ($order->order_products as $key => $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)->decrement('quantity', $item->quantity);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    public static function checkDeliveryBoyAvailabality($deliveryboy_id, $type = 'order', $ref_id = null)
    {
        $deliveryboy = User::findorfail($deliveryboy_id);

        if (!$deliveryboy || $deliveryboy->status != '1') {
            return (object)['status' => false, 'message' => 'The delivery partner is not available or may be the account has been suspended'];
        }

        if ($deliveryboy->online != '1') {
            return (object)['status' => false, 'message' => 'The delivery partner is not online currently'];
        }

        /** CHECK IF ANY RUNNING ORDER IS AVAILBLE */
        $liverorder_exists = Order::where('deliveryboy_id', $deliveryboy->id)
            ->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery'])
            ->exists();

        if ($liverorder_exists) {
            return (object)['status' => false, 'message' => 'The delivery partner is currently holding an running order. You cannot assign this job to this delivery partner'];
        }

        if ($type == 'order' && $ref_id != null) {
            $prevrej_exists = Order::where('id', $ref_id)
                ->whereHas('deliveryboy_logs', function ($q) use ($deliveryboy) {
                    $q->where('deliveryboy_id', $deliveryboy->id);
                    $q->whereIn('status', ['rejected']);
                })->exists();

            if ($prevrej_exists) {
                return (object)['status' => false, 'message' => 'The delivery partner has already rejected this job. You cannot reassign this job to this delivery partner'];
            }
        }


        /** CHECK IF ANY RUNNING RETURN/REPLACE JOB IS AVAILBLE */
        $liverreturnreplace_exists = OrderReturnReplace::where('deliveryboy_id', $deliveryboy->id)
            ->whereIn('status', ['initiated', 'accepted', 'processed', 'intransit', 'outfordelivery', 'outforpickup', 'outforstore'])
            ->exists();

        if ($liverreturnreplace_exists) {
            return (object)['status' => false, 'message' => 'The delivery partner is currently holding an running return or replacement job. You cannot assign this job to this delivery partner'];
        }

        if ($type == 'returnreplace' && $ref_id != null) {
            $prevrej_exists = OrderReturnReplace::where('id', $ref_id)
                ->whereHas('deliveryboy_logs', function ($q) use ($deliveryboy) {
                    $q->where('deliveryboy_id', $deliveryboy->id);
                    $q->whereIn('status', ['rejected']);
                })->exists();

            if ($prevrej_exists) {
                return (object)['status' => false, 'message' => 'The delivery partner has already rejected this job. You cannot reassign this job to this delivery partner'];
            }
        }

        return (object)['status' => true, 'message' => 'The delivery partner is available for the order'];
    }

    public static function calculateReturnRefundDeliveryCharge($order_id, $type)
    {
        $order = Order::findorfail($order_id);
        if (!$order) {
            return 0;
        }

        $cust_latitude = $order->cust_latitude;
        $cust_longitude = $order->cust_longitude;
        $shop_latitude = $order->shop->shop_latitude;
        $shop_longitude = $order->shop->shop_longitude;

        if (config('app.deliverycharge_status') == 'enable') {
            $delivery_charge = 0;

            if ($type == 'return') {
                $locationDistance = \Myhelper::locationDistance($cust_latitude, $cust_longitude, $shop_latitude, $shop_longitude, 'km');
                $delivery_charge += (float) config('app.deliverycharge_perkm') * (float) $locationDistance;
            } else if ($type == 'replace') {
                $locationDistance = \Myhelper::locationDistance($cust_latitude, $cust_longitude, $shop_latitude, $shop_longitude, 'km');
                $delivery_charge += ((float) config('app.deliverycharge_perkm') * (float) $locationDistance) * 2;
            }

            // dd($locationDistance, (float) config('app.deliverycharge_perkm'), $delivery_charge);

            if (config('app.deliverycharge_min') && $delivery_charge < (float) config('app.deliverycharge_min')) {
                $delivery_charge = (float) config('app.deliverycharge_min');
            }

            return ceil($delivery_charge);
        } else {
            return 0;
        }
    }

    public static function approveUser($id = "none"){
        if ($id == "none") {
            $id = \Auth::id();
        }

        $user = User::where('id', $id)->first();
        return $user->mobile_verified_at;
    }

    public static function userId($id = "none"){
        if ($id == "none") {
            $id = \Auth::id();
        }

        $user = User::where('id', $id)->first();
        return $user->id;
    }

    public static function getSchemeDet($id = 'none'){
        $details = Commission::where('scheme_id', $id)->get();

        return $details;
    }
}
