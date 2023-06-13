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

use App\Guest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\Model\OtpVerification;
use App\Model\Role;
use App\Model\UserAddress;
use App\User;
use GuzzleHttp\Client;

use Carbon\Carbon;
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
                'guest_token' => 'nullable|exists:guests,token'
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $request['mobile'] = $request->email;

            $user = User::where('email', $request->email)->orWhere('mobile', $request->mobile)->first();

            if (!$user || !in_array($user->role->slug, ['user'])) {
                return response()->json(['status' => 'error', 'message' => 'The User Id or Password is incorrect.', 'data' => \Myhelper::formatApiResponseData($data)]);
                // return response()->json(['status' => 'error', 'message' => 'The email or mobile number entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
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
                    return response()->json(['status' => 'error', 'message' => 'The User Id or Password is incorrect.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    // return response()->json(['status' => 'error', 'message' => 'The password you entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } catch (JWTException $e) {
                return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($user->mobile_verified_at == null && $otp_verification != true) {
                $otp = rand(1111, 9999);
                // $content = "Your Gopotu account OTP is $otp. Only valid for 20 min minutes. DO NOT share it with anyone.";
                $content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";

                if (\Myhelper::sms($user->mobile, $content)) {
                    \Mail::send('emails.accountverify', compact('user', 'otp'), function ($m) use ($user) {
                        $subject = "GoPotu - Account Verification";

                        $m->to($user->email)->subject($subject);
                    });

                    $request['user_id'] = $user->id;
                    OtpVerification::where('mobile', $user->mobile)->where('type', 'user-login')->delete();

                    $document = [
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'otp' => $otp,
                        'type' => 'user-login',
                        'token' => \Str::random(100),
                        'data' => json_encode($request->all()),
                    ];

                    $action = OtpVerification::create($document);
                    if ($action) {
                        $data['otp_token'] = $action->token;
                        return response()->json(['status' => 'otpverification', 'message' => 'An OTP has been sent to your mobile number.Please verify your account.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                } else {
                    return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be send.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $user = User::find($user->id);

            if ($request->has('fcm_token') && $request->fcm_token != null) {
                \Myhelper::storeFcmToken($user->id, $request->fcm_token);
            }

            if ($request->has('guest_token') && $request->guest_token != null) {
                $guest = Guest::select('id')->where('token', $request->guest_token)->first();
                if ($guest) {
                    \Myhelper::syncGuestWithUser($user->id, $guest->id);
                }
            }

            $data['user'] = $user;
            $data['default_address'] = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
            $data['token'] = [
                'access_token' => $token,
                'token_type' => 'bearer',
            ];

            return response()->json(['status' => 'success', 'message' => 'Logged In Successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function register(Request $request, $otp_verification = false)
    {
        $data = array();

        try {
            $rules = [
                'name' => 'required',
                'email' => 'required|unique:users',
                'mobile' => 'required|digits:10|unique:users',
                'password' => 'required|confirmed',
                'fcm_token' => 'nullable',
                'guest_token' => 'nullable|exists:guests,token',
                'referred_code' => 'nullable|exists:users,referral_code'
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $role = Role::where('slug', 'user')->first();
            if (!$role) {
                return response()->json(['status' => 'error', 'message' => 'Registration is currently paused. Please try again after sometime.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $document = array();
            $document['name'] = $request->name;
            $document['email'] = $request->email;
            $document['mobile'] = $request->mobile;
            $document['password'] = bcrypt($request->password);
            $document['role_id'] = $role->id;

            do {
                $document['referral_code'] = config('app.shortname') . '-' . rand(11111111, 99999999);
            } while (User::where("referral_code", "=", $document['referral_code'])->first() instanceof User);

            if ($request->has('referred_code') && $request->referred_code) {
                $parent = User::where('referral_code', $request->referred_code)->first();
                if ($parent && $parent->status == '1') {
                    $document['parent_id'] = $parent->id;
                } else {
                    return response()->json(['status' => 'error', 'message' => 'The referred code you entered may be invalid or the user has been suspended.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            if ($otp_verification != true) {
                $otp = rand(1111, 9999);
                // $content = "Your Gopotu account OTP is $otp. Only valid for 20 min minutes. DO NOT share it with anyone.";
                $content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";

                if (\Myhelper::sms($request->mobile, $content)) {
                    \Mail::send('emails.accountverify', compact('document', 'otp'), function ($m) use ($document) {
                        $subject = "GoPotu - Account Verification";

                        $m->to($document['email'])->subject($subject);
                    });

                    OtpVerification::where('mobile', $request->mobile)->where('type', 'user-register')->delete();

                    $document = [
                        'mobile' => $request->mobile,
                        'email' => $request->email,
                        'otp' => $otp,
                        'type' => 'user-register',
                        'token' => \Str::random(100),
                        'data' => json_encode($request->all()),
                    ];

                    $action = OtpVerification::create($document);
                    if ($action) {
                        $data['otp_token'] = $action->token;
                        return response()->json(['status' => 'otpverification', 'message' => 'An OTP has been sent to your mobile number.Please verify your account.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                } else {
                    return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be send.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } else {
                $document['mobile_verified_at'] = Carbon::now();
            }

            $user = User::create($document);
            if ($user) {
                $user = User::find($user->id);
                $token = JWTAuth::fromUser($user);

                if ($request->has('fcm_token') && $request->fcm_token != null) {
                    \Myhelper::storeFcmToken($user->id, $request->fcm_token);
                }

                if ($request->has('guest_token') && $request->guest_token != null) {
                    $guest = Guest::select('id')->where('token', $request->guest_token)->first();
                    if ($guest) {
                        \Myhelper::syncGuestWithUser($user->id, $guest->id);
                    }
                }

                $data['user'] = $user;
                $data['default_address'] = UserAddress::where('user_id', $user->id)->where('is_default', 1)->first();
                $data['token'] = [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                ];

                return response()->json(['status' => 'success', 'message' => 'Registered Successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Registration cannot be completed. Please try again after sometime.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function resetPassword(Request $request, $otp_verification = false)
    {
        $data = array();

        try {
            $rules = [
                'mobile' => 'required|digits:10|exists:users,mobile',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $user = User::where('mobile', $request->mobile)->whereHas('role', function ($q) {
                $q->whereIn('slug', ['user', 'branch', 'deliveryboy']);
            })->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Invalid mobile number', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($user->status != '1') {
                return response()->json(['status' => 'error', 'message' => 'Your account has been suspended. Please contact your support team for further assistance', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($otp_verification != true) {
                $otp = rand(1111, 9999);
                // $content = "Your Gopotu account OTP is $otp. Only valid for 20 min minutes. DO NOT share it with anyone.";
                $content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";

                if (\Myhelper::sms($request->mobile, $content)) {
                    OtpVerification::where('mobile', $request->mobile)->where('type', 'user-password-reset')->delete();

                    $document = [
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'otp' => $otp,
                        'type' => 'user-password-reset',
                        'token' => \Str::random(100),
                        'data' => json_encode($request->all()),
                    ];

                    $action = OtpVerification::create($document);
                    if ($action) {
                        $data['otp_token'] = $action->token;
                        return response()->json(['status' => 'otpverification', 'message' => 'An OTP has been sent to your mobile number.Please verify your account.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                } else {
                    return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be send.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } else {
                $new_password = rand(11111111, 99999999);

                $user->password = bcrypt($new_password);
                $user->resetpwd = 'default';

                $action = $user->save();
                if ($action) {
                    $content = 'Hello ' . $user->name . ', as per your request we have reset your account password, your new account password is ' . $new_password . '. Team GoPotu';
                    \Myhelper::sms($user->mobile, $content);

                    // $data['new_password'] = $new_password;
                    return response()->json(['status' => 'success', 'message' => 'We have sent a new account password to your mobile number', 'data' => \Myhelper::formatApiResponseData($data)]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. If you are having trouble contact our support team', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function getAccountDetails(Request $request)
    {
        $data = array();

        try {
            $data['user'] = $request->user();
            $data['default_address'] = UserAddress::where('user_id', \Auth::guard('api')->id())->where('is_default', 1)->first();
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

    public function sendOtp(Request $request){
        $request->validate([
            'mobile' => 'required',
            //'business_name' => 'required'
        ]);
        $otp_rand = rand(1000, 9999);
        // $otp_rand = 1000;
       
        // $last_send_time = Otp::where('mobile', $request->mobile)->orderBy('created_at', 'desc')->first();
        // // dd($last_send_time->updated_at);
        // $dt = new DateTime();

        // $duration = 10;
        // if (isset($last_send_time)) {
        //     $last_otp_send_time = $last_send_time->created_at->format('Y-m-d H:i:s');
        //     $time_now = $dt->format('Y-m-d H:i:s');
        //     $duration = strtotime($time_now) - strtotime($last_otp_send_time);
        // }
        $user = User::where('mobile', $request->mobile)->first(); 
        if (isset($user)){
            $user->otp = $otp_rand;
            $user->save();
        }  else{
            $user = new User();
            $user->mobile = $request->mobile;
            $user->otp = $otp_rand;
            $user->role_id = 3;
          //  $user->name = $request->name;
            $user->save();
        }  
                  
        $client = new Client();

        $response = $client->get('smsapi.syscogen.com/rest/services/sendSMS/sendGroupSms?AUTH_KEY=2946464c2021d8e0b1277bed83cd9f&message='.$otp_rand.'&senderId=DEMOOS&routeId=1&mobileNos='.$request->mobile.'&smsContentType=english&entityid=1001238677144196147&tmid=140200000022&templateid=NoneedIfAddedInPanel');

            return response()->json([
                'message' => 'otp send to your mobile number',
                'status' => '200',
                'otp' => $otp_rand
            ]);
    
    }

    public function otpLogin(Request $request){
        $otp = User::where('mobile', $request->mobile)->orderBy('created_at', 'desc')->first()->otp;
        $request->validate([
            'otp' => 'required',
            'mobile' => 'required',
         
        ]);
        // return response()->json([
        //     'otp' => $otp->otp,
        //     'request' => $request->otp
        // ]);
        if ($otp == $request->otp) {
            $user =  $user = User::where('mobile', $request->mobile)->first();
           
            $token = JWTAuth::fromUser($user);

                return response()->json([
                    'message' => ' Otp verification succesfully completed',
                    'status' => '200',
                    'user' => $user,
                    'token' => $token,
                  
                ]);
        }else{
                
            return response()->json([
                'message' => 'otp verification failed',
                'status' => 401,
                // 'token' => $token,
                //'otp' => $otp
            ]);
        }
            //return $user->createToken('API Token')->accessToken;


          


          
        
    }
}
