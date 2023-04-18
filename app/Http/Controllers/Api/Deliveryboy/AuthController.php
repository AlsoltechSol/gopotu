<?php

namespace App\Http\Controllers\Api\Deliveryboy;

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
use App\Model\Order;
use Tymon\JWTAuth\Exceptions\JWTException;

use App\User;

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
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $request['mobile'] = $request->email;

            $user = User::where('email', $request->email)->orWhere('mobile', $request->mobile)->first();

            if (!$user || !in_array($user->role->slug, ['deliveryboy'])) {
                // return response()->json(['status' => 'error', 'message' => 'The mobile number entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
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

            $user = User::with('documents', 'bankdetails')->find($user->id);

            if ($request->has('fcm_token') && $request->fcm_token != null) {
                \Myhelper::storeFcmToken($user->id, $request->fcm_token);
            }

            $data['user'] = $user;
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
            $data['user'] = User::with('documents', 'bankdetails')->findorfail($request->user()->id);
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
                if (Order::where('deliveryboy_id', \Auth::guard('api')->id())->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery'])->exists()) {
                    return response()->json(['status' => 'error', 'message' => 'Currently a order is running, you cannot go offline', 'data' => \Myhelper::formatApiResponseData($data)]);
                }

                if ($request->has('fcm_token') && $request->fcm_token) {
                    \Myhelper::deleteFcmToken(\Auth::guard('api')->id(), $request->fcm_token);
                }

                User::where('id', \Auth::guard('api')->id())->update(['online' => '0']);
            }

            return response()->json(['status' => 'success', 'message' => 'Successfully Loged Out', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
