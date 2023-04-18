<?php

namespace App\Http\Controllers;

use App\Model\Order;
use Illuminate\Http\Request;

class CashOrderPaymentController extends Controller
{
    public function initOrderPayment(Request $request)
    {
        $rules = array(
            'order_id' => 'required|exists:orders,id',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                abort(500, $value[0]);
            }
        }

        $order = Order::findorfail($request->order_id);

        if ($order->payment_mode != 'cash' || !in_array($order->status, ['received', 'accepted', 'processed', 'intransit', 'outfordelivery'])) {
            abort(500, "You cannot make payment for this order");
        }

        do {
            $request['payment_refid'] = config('app.shortname') . '-' . rand(1111111111, 9999999999);
        } while (Order::where("payment_refid", "=", $request->payment_refid)->first() instanceof Order);

        $order->payment_refid = $request->payment_refid;
        $order->save();

        $paytmParams = array(
            "MID" => config('paytm.MERCHANT_ID'),
            "WEBSITE" => config('paytm.WEBSITE_NAME'),
            "INDUSTRY_TYPE_ID" => config('paytm.INDUSTRY_TYPE'),
            // "CHANNEL_ID" => "YOUR_CHANNEL_ID", /* WEB for website and WAP for Mobile-websites or App */
            "CHANNEL_ID" => "WAP",
            "ORDER_ID" => $request->payment_refid,
            "CUST_ID" => $order->user_id,
            "MOBILE_NO" => $order->cust_mobile,
            "EMAIL" => $order->user->email,
            "TXN_AMOUNT" => $order->payable_amount,
            "CALLBACK_URL" => route('cashorderpay.processedpayment'), /* on completion of transaction, we will send you the response on this URL */
        );

        // $paytmChecksum = $this->getChecksumFromString(json_encode($paytmParams, JSON_UNESCAPED_SLASHES), config('paytm.MERCHANT_KEY'));
        $paytmChecksum = $this->getChecksumFromArray($paytmParams, config('paytm.MERCHANT_KEY'));

        /* for Staging */
        $paytmUrl = "https://securegw-stage.paytm.in/order/process";

        /* for Production */
        // $url = "https://securegw.paytm.in/order/process";

        $data['paytmParams'] = $paytmParams;
        $data['paytmChecksum'] = $paytmChecksum;
        $data['paytmUrl'] = $paytmUrl;

        // dd($data);
        return view('cashorderpayment.init', $data);
    }

    public function processOrderPayment(Request $request)
    {
        $rules = array(
            'ORDERID' => 'required|exists:orders,payment_refid',
            'TXNID' => 'required|unique:orders,payment_txnid',
            'STATUS' => 'required',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                abort(500, $value[0]);
            }
        }

        $order = Order::where('payment_refid', $request->ORDERID)->first();

        if ($order->payment_mode != 'cash' || !in_array($order->status, ['received', 'accepted', 'processed', 'intransit', 'outfordelivery'])) {
            abort(500, "You cannot make payment for this order");
        }

        if (in_array(@$request->STATUS, ['TXN_SUCCESS'])) {
            $order->payment_mode = 'online';
            $order->payment_txnid = @$request->TXNID;

            $action = $order->save();
            if ($action) {
                $notify_content = "Thank you for paying online for your order. We have recieved payment for your Order No: " . $order->code;
                \Myhelper::sendNotification($order->user_id, "Payment Recieved", $notify_content);

                return redirect()->route('cashorderpay.successpayment', ['order_id' => $order->id]);
            }
        } else {
            $order->payment_txnid = @$request->TXNID;

            $action = $order->save();
            if ($action) {
                return redirect()->route('cashorderpay.failedpayment', ['order_id' => $order->id]);
            }
        }
    }

    public function successOrderPayment(Request $request)
    {
        $rules = array(
            'order_id' => 'required|exists:orders,id',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                abort(500, $value[0]);
            }
        }

        $data['order'] = Order::findorfail($request->order_id);

        return view('cashorderpayment.success', $data);
    }

    public function failedOrderPayment(Request $request)
    {
        $rules = array(
            'order_id' => 'required|exists:orders,id',
        );

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                abort(500, $value[0]);
            }
        }

        $data['order'] = Order::findorfail($request->order_id);

        return view('cashorderpayment.failed', $data);
    }
}
