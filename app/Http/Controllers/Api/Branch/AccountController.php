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
use App\Model\Shop;
use App\User;
use Carbon\Carbon;

class AccountController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function update($type, Request $request, $verification = false)
    {
        $data = array();

        try {
            $request['user_id'] = \Auth::guard('api')->user()->id;
            $userdata = User::findorfail($request->user_id);

            switch ($type) {
                case 'basic-details':
                    $rules = [
                        'name' => 'required',
                        // 'email' => 'required|email|unique:users,email,' . $request->user_id,
                        // 'mobile' => 'required|digits:10|unique:users,mobile,' . $request->user_id,
                    ];
                    break;

                case 'password':
                    $rules = [
                        'current_password' => 'required',
                        'new_password' => 'required|confirmed'
                    ];
                    break;

                case 'profile-picture':
                    $rules = [
                        'profile_picture' => 'required|mimes:jpeg,jpg,png,gif',
                    ];
                    break;

                case 'shop-status':
                    $rules = [
                        'status' => 'required|in:1,0',
                    ];
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => 'Invalid Request', 'data' => \Myhelper::formatApiResponseData($data)]);
                    break;
            }

            if (isset($rules) && count($rules) > 0) {
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }
            }

            $scs_msg = "Profile updated successfully";
            $err_msg = "Profile cannot be updated. Please try again";

            switch ($type) {
                case 'basic-details':
                    $document = [
                        'name' => $request->name,
                        // 'email' => $request->email,
                        // 'mobile' => $request->mobile,
                    ];

                    // if ($document['mobile'] != $userdata->mobile && $verification != true) {
                    //     $otp = rand(111111, 999999);
                    //     $content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";
                    //     if (\Myhelper::sms($request->mobile, $content)) {
                    //         OtpVerification::where('mobile', $request->mobile)->where('type', 'user-profile-update')->delete();

                    //         $document = [
                    //             'mobile' => $request->mobile,
                    //             'otp' => $otp,
                    //             'type' => 'user-profile-update',
                    //             'token' => \Str::random(100),
                    //             'data' => json_encode($request->all()),
                    //         ];

                    //         $action = OtpVerification::create($document);
                    //         if ($action) {
                    //             $data['otp_token'] = $action->token;
                    //             return response()->json(['status' => 'otpverification', 'message' => 'An OTP has been sent to your mobile number.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    //         } else {
                    //             return response()->json(['status' => 'error', 'message' => 'The token cannot be created.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    //         }
                    //     } else {
                    //         return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be send.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    //     }
                    // }

                    $scs_msg = "Profile updated successfully";
                    $err_msg = "Profile cannot be updated. Please try again";

                    $action = User::where('id', $request->user_id)->update($document);
                    break;

                case 'password':
                    if (!\Hash::check($request->current_password, $userdata->password)) {
                        return response()->json(['status' => 'error', 'message' => 'Your current password does not match!', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if ($request->new_password == $request->current_password) {
                        return response()->json(['status' => 'error', 'message' => 'Your New Password cannot be the same as your Old Password.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    $document = [
                        'password' => \Hash::make($request->new_password),
                    ];

                    $scs_msg = "Password updated successfully";
                    $err_msg = "Password cannot be updated. Please try again";

                    $action = User::where('id', $request->user_id)->update($document);
                    break;

                case 'profile-picture':
                    $file = $request->file('profile_picture');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if ($userdata->profile_image != NULL) {
                        $deletefile = 'uploads/profile/' . $userdata->profile_image;
                    }

                    if (\Image::make($file->getRealPath())->resize(160, 160)->save('uploads/profile/' . $filename, 60)) {
                        $document['profile_image'] = $filename;

                        if (isset($deletefile)) {
                            \File::delete($deletefile);
                        }
                    } else {
                        return response()->json(['status' => 'error', 'message' => 'File cannot be saved to server.', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    $scs_msg = "Profile picture updated successfully";
                    $err_msg = "Profile picture cannot be updated. Please try again";

                    $action = User::where('id', $request->user_id)->update($document);
                    break;

                case 'shop-status':
                    $shop = Shop::find(\Myhelper::getShop($userdata->id));
                    $shop->online = $request->status;

                    $scs_msg = "Shop status updated successfully";
                    $err_msg = "Shop status cannot be updated. Please try again";

                    $action = $shop->save();
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => 'Invalid Request', 'data' => \Myhelper::formatApiResponseData($data)]);
                    break;
            }

            if ($action) {
                $data['user'] = User::find($request->user_id);
                $data['shop'] = Shop::find(\Myhelper::getShop($request->user_id));
                return response()->json(['status' => 'success', 'message' => $scs_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => $err_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
