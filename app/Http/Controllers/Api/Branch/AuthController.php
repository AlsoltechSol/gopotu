<?php

namespace App\Http\Controllers\Api\Branch;

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
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Model\Shop;
use App\User;

use JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function login(Request $request, $otp_verification = false)
    {
        $data = array();

        try {
            $rules = [
                // 'email' => 'required|exists:users,email',
                'email' => 'required',
                'password' => 'required',
                'fcm_token' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $request['mobile'] = $request->email;

            $user = User::where('email', $request->email)->orWhere('mobile', $request->mobile)->first();

            if (!$user || !in_array($user->role->slug, ['branch'])) {
                // return response()->json(['status' => 'error', 'message' => 'The email or mobile number entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
                return response()->json(['status' => 'error', 'message' => 'The mobile number or password is incorrect.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($user->status != '1') {
                return response()->json(['status' => 'error', 'message' => 'Your account has been blocked.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $credentials_email = request(['email', 'password']);
            $credentials_mobile = request(['mobile', 'password']);
            try {
                if ($token = JWTAuth::attempt($credentials_email)) {
                    #login-success
                } else if ($token = JWTAuth::attempt($credentials_mobile)) {
                    #login-success
                } else {
                    // return response()->json(['status' => 'error', 'message' => 'The password you entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    return response()->json(['status' => 'error', 'message' => 'The mobile number or password is incorrect.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } catch (JWTException $e) {
                return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            // if ($user->mobile_verified_at == null && $otp_verification != true) {
            //     $otp = rand(1111, 9999);
            //     $content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";

            //     if (\Myhelper::sms($user->mobile, $content)) {
            //         \Mail::send('emails.accountverify', compact('user', 'otp'), function ($m) use ($user) {
            //             $subject = "GoPotu - Account Verification";

            //             $m->to($user->email)->subject($subject);
            //         });

            //         $request['user_id'] = $user->id;
            //         OtpVerification::where('mobile', $user->mobile)->where('type', 'user-login')->delete();

            //         $document = [
            //             'mobile' => $user->mobile,
            //             'email' => $user->email,
            //             'otp' => $otp,
            //             'type' => 'user-login',
            //             'token' => \Str::random(100),
            //             'data' => json_encode($request->all()),
            //         ];

            //         $action = OtpVerification::create($document);
            //         if ($action) {
            //             $data['otp_token'] = $action->token;
            //             return response()->json(['status' => 'otpverification', 'message' => 'An OTP has been sent to your email address. Please verify your account.', 'data' => \Myhelper::formatApiResponseData($data)]);
            //         } else {
            //             return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
            //         }
            //     } else {
            //         return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be send.', 'data' => \Myhelper::formatApiResponseData($data)]);
            //     }
            // }

            $user = User::find($user->id);

            if ($request->has('fcm_token') && $request->fcm_token != null) {
                \Myhelper::storeFcmToken($user->id, $request->fcm_token);
            }

            $data['user'] = $user;
            $data['shop'] = Shop::find(\Myhelper::getShop($user->id));
            $data['token'] = [
                'access_token' => $token,
                'token_type' => 'bearer',
            ];

            return response()->json(['status' => 'success', 'message' => 'Logged In Successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function getAccountDetails(Request $request)
    {
        $data = array();

        try {
            $data['user'] = $request->user();
            $data['shop'] = Shop::find(\Myhelper::getShop(\Auth::guard('api')->id()));
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function logout(Request $request)
    {
        $data = array();
        try {
            if (\Auth::guard('api')->check()) {
                if ($request->has('fcm_token') && $request->fcm_token) {
                    \Myhelper::deleteFcmToken(\Auth::guard('api')->id(), $request->fcm_token);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Successfully Loged Out', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
