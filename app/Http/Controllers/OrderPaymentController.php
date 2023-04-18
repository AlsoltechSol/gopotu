<?php

namespace App\Http\Controllers;

use App\Model\Cart;
use App\Model\Order;
use App\Model\WalletReport;
use App\User;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
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

        if ($order->payment_mode != 'online') {
            abort(500, "The order is not set for online payment");
        }

        if ($order->status != 'paymentinitiated') {
            abort(500, "The payment cannot be initiated for this order");
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
            "CALLBACK_URL" => route('orderpay.processedpayment'), /* on completion of transaction, we will send you the response on this URL */
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
        return view('orderpayment.init', $data);
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

        if ($order->payment_mode != 'online') {
            abort(500, "The order is not set for online payment");
        }

        if ($order->status != 'paymentinitiated') {
            abort(500, "The payment cannot be initiated for this order");
        }

        if (in_array(@$request->STATUS, ['TXN_SUCCESS'])) {
            $order->status = 'received';
            $order->payment_txnid = @$request->TXNID;

            $action = $order->save();
            if ($action) {
                \Myhelper::updateOrderStatusLog($order->id);
                \Myhelper::updateOrderStatusAction($order->id);
                \Myhelper::onConfirmOrderReduceStock($order->id);

                Cart::where('user_id', $order->user->id)->where('type', $order->type)->delete();

                if ($order->wallet_deducted > 0) {
                    $report = array(
                        'user_id' => $order->user->id,
                        'ref_id' => $order->id,
                        'wallet_type' => 'userwallet',
                        'balance' => $order->user->userwallet,
                        'trans_type' => 'debit',
                        'amount' => $order->wallet_deducted,
                        'remarks' => 'Amount debited for Order No ' . $order->code,
                        'service' => 'order',
                    );

                    $transaction = User::where('id', $order->user->id)->decrement('userwallet', $order->wallet_deducted);
                    if ($transaction) {
                        WalletReport::create($report);
                    }
                }

                return redirect()->route('orderpay.successpayment', ['order_id' => $order->id]);
            }
        } else {
            $order->status = 'paymentfailed';

            $action = $order->save();
            if ($action) {
                \Myhelper::updateOrderStatusLog($order->id);
                \Myhelper::updateOrderStatusAction($order->id);

                return redirect()->route('orderpay.failedpayment', ['order_id' => $order->id]);
            }
        }

        // else {
        //     $order->status = 'received';
        //     $order->payment_mode = 'cash';

        //     $action = $order->save();
        //     if ($action) {
        //         \Myhelper::updateOrderStatusLog($order->id);
        //         \Myhelper::updateOrderStatusAction($order->id);

        //         $order = Order::find($order->id);
        //         $data['order'] = $order;

        //         Cart::where('user_id', $order->user->id)->where('type', $order->type)->delete();

        //         if ($order->wallet_deducted > 0) {
        //             $report = array(
        //                 'user_id' => $order->user->id,
        //                 'ref_id' => $order->id,
        //                 'wallet_type' => 'userwallet',
        //                 'balance' => $order->user->userwallet,
        //                 'trans_type' => 'debit',
        //                 'amount' => $order->wallet_deducted,
        //                 'remarks' => 'Amount debited for Order No ' . $order->code,
        //                 'service' => 'order',
        //             );

        //             $transaction = User::where('id', $order->user->id)->decrement('userwallet', $order->wallet_deducted);
        //             if ($transaction) {
        //                 WalletReport::create($report);
        //             }
        //         }

        //         return redirect()->route('orderpay.failedpayment', ['order_id' => $order->id]);
        //     }
        // }
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

        return view('orderpayment.success', $data);
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

        return view('orderpayment.failed', $data);
    }

    public function redirectPayment(Request $request)
    {
        return view('orderpayment.redirect');
    }
}
