<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserFcmToken;
use App\User;

class DebugController extends Controller
{
    public function checkpaytmstatus(Request $post)
    {
        $rules = array(
            'orderId' => 'required',
        );

        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                return response()->json(['status' => $value[0]], 400);
            }
        }


        $url = 'https://securegw-stage.paytm.in/v3/order/status';

        $parameter['body'] = array(
            'mid' => config('paytm.MERCHANT_ID'),
            'orderId' =>  $post->orderId,
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

        dd($payment);
    }

    public function sendpushnotification()
    {
        $user_id = 6;

        $user = User::findorfail($user_id);
        $fcmTokens = UserFcmToken::where('user_id', $user->id)->get();

        \Myhelper::sendNotification($user->id, "Hello World", "The quick brown fox jumps over the lazy dog", true);

        dd($fcmTokens->toArray());
    }

    public function syncguestwithuser(){
        \Myhelper::syncGuestWithUser(5, 1);
    }

    public function checkorderstatuslog(){
        \Myhelper::updateOrderStatusLog(79);
    }

    public function generatereferralcode(){
        $users = User::whereHas('role', function($q){
            $q->where('slug', 'user');
        })->where('referral_code', null)->get();

        foreach ($users as $key => $user) {
            do {
                $referral_code = config('app.shortname') .  rand(11111111, 99999999);
            } while (User::where("referral_code", "=", $referral_code)->first() instanceof User);

            $user->referral_code = $referral_code;
            $user->save();
        }

        // dd($users->toArray());

    }
}
