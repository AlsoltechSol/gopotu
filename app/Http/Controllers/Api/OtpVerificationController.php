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
use App\Model\OtpVerification;
use App\User;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }

        $this->_AuthController = new AuthController();
        $this->_UserController = new UserController();
    }

    public function verify($type, Request $request)
    {
        $data = [];

        try {
            if (in_array($type, ["voktoesache", "user-signup"])) $type = "user-register";

            $request['type'] = $type;

            $rules = [
                'type' => 'required|in:user-login,user-register,user-profile-update,user-password-reset',
                'verification_token' => 'required|exists:otp_verifications,token',
                'otp' => 'required|digits:4',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $otp_verification = OtpVerification::where('token', $request->verification_token)->where('type', $request->type)->first();
            if ($otp_verification) {
                $otp_time = Carbon::parse($otp_verification->updated_at);
                $current_time = Carbon::now();

                $diff = $current_time->diffInMinutes($otp_time, true);
                if ($diff > 20) {
                    return response()->json(['status' => 'error', 'message' => 'The OTP has expired. Please request a new OTP to proceed further.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }

                if (!\Hash::check($request->otp, $otp_verification->otp)) {
                    return response()->json(['status' => 'error', 'message' => 'The OTP entered is invalid.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }

                switch ($request->type) {
                    case 'user-login':
                        $verificationdata = json_decode($otp_verification->data);

                        if (isset($verificationdata->user_id) && $verificationdata->user_id != null) {
                            $action = User::where('id', $verificationdata->user_id)->update(['mobile_verified_at' => $current_time]);
                            if (!$action) {
                                return response()->json(['status' => 'error', 'message' => 'The verification cannot be completed', 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                        }

                        $request['email'] = $verificationdata->email;
                        $request['password'] = $verificationdata->password;
                        $request['fcm_token'] = @$verificationdata->fcm_token;

                        $_login = $this->_AuthController->login($request, true);
                        if ($_login->status() != 200) {
                            return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: LOGIN_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        $_loginData = $_login->getData();
                        if ($_loginData->status != "success") {
                            return response()->json(['status' => $_loginData->status, 'message' => $_loginData->message, 'data' => $_loginData->data]);
                        }

                        $otp_verification->delete();
                        return response()->json($_loginData);
                        break;

                    case 'user-register':
                        $verificationdata = json_decode($otp_verification->data);

                        $request['name'] = $verificationdata->name;
                        $request['email'] = $verificationdata->email;
                        $request['mobile'] = $verificationdata->mobile;
                        $request['password'] = $verificationdata->password;
                        $request['password_confirmation'] = $verificationdata->password_confirmation;
                        $request['fcm_token'] = @$verificationdata->fcm_token;
                        $request['referred_code'] = @$verificationdata->referred_code;

                        $_register = $this->_AuthController->register($request, true);
                        if ($_register->status() != 200) {
                            return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: LOGIN_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        $_registerData = $_register->getData();
                        if ($_registerData->status != "success") {
                            return response()->json(['status' => $_registerData->status, 'message' => $_registerData->message, 'data' => $_registerData->data]);
                        }

                        $otp_verification->delete();
                        return response()->json($_registerData);
                        break;

                    case 'user-password-reset':
                        $verificationdata = json_decode($otp_verification->data);

                        $request['mobile'] = $verificationdata->mobile;

                        $_register = $this->_AuthController->resetPassword($request, true);
                        if ($_register->status() != 200) {
                            return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: RESETPASSWORED_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        $_registerData = $_register->getData();
                        if ($_registerData->status != "success") {
                            return response()->json(['status' => $_registerData->status, 'message' => $_registerData->message, 'data' => $_registerData->data]);
                        }

                        $otp_verification->delete();
                        return response()->json($_registerData);
                        break;

                    case 'user-profile-update':
                        $verificationdata = json_decode($otp_verification->data);
                        if (!\Auth::guard('api')->check()) {
                            return response()->json(['status' => 'unauthenticated', 'message' => 'Please sign in to continue', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        if (\Auth::guard('api')->id() != $verificationdata->user_id) {
                            return response()->json(['status' => 'unauthenticated', 'message' => 'Unauthenticated', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        $request['name'] = $verificationdata->name;
                        $request['email'] = $verificationdata->email;
                        $request['mobile'] = $verificationdata->mobile;

                        $_profileUpdate = $this->_UserController->update('basic-details', $request, true);
                        if ($_profileUpdate->status() != 200) {
                            return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Error_Code: LOGIN_N_200', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        $_profileUpdateData = $_profileUpdate->getData();
                        if ($_profileUpdateData->status != "success") {
                            return response()->json(['status' => $_profileUpdateData->status, 'message' => $_profileUpdateData->message, 'data' => $_profileUpdateData->data]);
                        }

                        $otp_verification->delete();
                        return response()->json($_profileUpdateData);
                        break;
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Token & Verification Type didn\'t matched', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function resend($type, Request $request)
    {
        $data = [];

        try {
            if (in_array($type, ["voktoesache", "user-signup"])) $type = "user-register";

            $request['type'] = $type;

            $rules = [
                'type' => 'required|in:user-login,user-register,user-profile-update,user-password-reset',
                'verification_token' => 'required|exists:otp_verifications,token',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $otp_verification = OtpVerification::where('token', $request->verification_token)->where('type', $request->type)->first();
            if ($otp_verification) {
                switch ($request->type) {
                    case 'user-profile-update':
                        $verificationdata = json_decode($otp_verification->data);
                        if (!\Auth::guard('api')->check()) {
                            return response()->json(['status' => 'unauthenticated', 'message' => 'Please sign in to continue', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        if (\Auth::guard('api')->id() != $verificationdata->user_id) {
                            return response()->json(['status' => 'unauthenticated', 'message' => 'Unauthenticated', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                    case 'user-login':
                    case 'user-register':
                    case 'user-password-reset':
                        $otp = rand(1111, 9999);

                         $content = "Your Gopotu account OTP is $otp. Only valid for 20 min minutes. DO NOT share it with anyone.";
                        //$content = "Your one-time password for GoPotu is $otp. Only valid for 20 min";
                        if (\Myhelper::sms($otp_verification->mobile, $content)) {
                            $document = array();
                            $document['name'] = @$otp_verification->data->name;
                            $document['email'] = $otp_verification->email;

                            // \Mail::send('emails.accountverify', compact('document', 'otp'), function ($m) use ($document) {
                            //     $subject = "GoPotu - Account Verification";

                            //     $m->to($document['email'])->subject($subject);
                            // });

                            $otp_verification->otp = $otp;
                            if ($otp_verification->save()) {
                                return response()->json(['status' => 'success', 'message' => 'The OTP has been resent successfully.', 'data' => \Myhelper::formatApiResponseData($data)]);
                            } else {
                                return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be resend. DB Exception occured', 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                        } else {
                            return response()->json(['status' => 'error', 'message' => 'The one-time password cannot be resend.', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                        break;
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Token & Verification Type didn\'t matched', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
