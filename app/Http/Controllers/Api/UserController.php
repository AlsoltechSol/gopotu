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
use App\Model\UserAddress;
use App\Model\UserNotification;
use App\User;
use App\Model\Cart;
use Carbon\Carbon;

class UserController extends Controller
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
                        'email' => 'required|email|unique:users,email,' . $request->user_id,
                        // 'mobile' => 'required|digits:10|unique:users,mobile,' . $request->user_id,
                    ];
                    break;

                case 'password':
                    $rules = [
                        'current_password' => 'required',
                        'new_password' => 'required|not_in:' . $request->current_password . '|confirmed'
                    ];
                    break;

                case 'profile-picture':
                    $rules = [
                        'profile_picture' => 'required|mimes:jpeg,jpg,png,gif',
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
                        'email' => $request->email,
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
                        return response()->json(['status' => 'error', 'message' => 'Your current password didn\'t matched', 'data' => \Myhelper::formatApiResponseData($data)]);
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

                default:
                    return response()->json(['status' => 'error', 'message' => 'Invalid Request', 'data' => \Myhelper::formatApiResponseData($data)]);
                    break;
            }

            if ($action) {
                $data['user'] = User::find($request->user_id);
                return response()->json(['status' => 'success', 'message' => $scs_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => $err_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function addressSubmit($type, Request $request)
    {
        $data = array();

        try {
            switch ($type) {
                case 'add':
                    if (!$request->state)
                        $request['state'] = 'WB';

                    $rules = array(
                        'name' => 'required',
                        'mobile' => 'required|digits:10',
                        'alternative_mobile' => 'nullable|digits:10',
                        'location' => 'required',
                        'latitude' => 'required|numeric',
                        'longitude' => 'required|numeric',
                        'address_line1' => 'nullable',
                        'address_line2' => 'nullable',
                        'postal_code' => 'required|digits:6',
                        'city' => 'nullable',
                        'state' => 'required|exists:state_masters,state_code',
                        'country' => 'nullable',
                        'landmark' => 'nullable',
                        'type' => 'required|in:home,work,other',
                    );
                    break;

                case 'edit':
                    if (!$request->state)
                        $request['state'] = 'WB';

                    $rules = array(
                        'id' => 'required|exists:user_addresses,id',
                        'name' => 'required',
                        'mobile' => 'required|digits:10',
                        'alternative_mobile' => 'nullable|digits:10',
                        'location' => 'required',
                        'latitude' => 'required|numeric',
                        'longitude' => 'required|numeric',
                        'address_line1' => 'nullable',
                        'address_line2' => 'nullable',
                        'postal_code' => 'required|digits:6',
                        'city' => 'nullable',
                        'state' => 'required|exists:state_masters,state_code',
                        'country' => 'nullable',
                        'landmark' => 'nullable',
                        'type' => 'required|in:home,work,other',
                    );
                    break;

                case 'delete':
                case 'set-default':
                    $rules = array(
                        'id' => 'required|exists:user_addresses,id',
                    );
                    break;

                case 'fetch':
                    $rules = array(
                        'shop_id' => 'nullable|exists:shops,id',
                    );
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => 'Unsupported Request', 'data' => \Myhelper::formatApiResponseData($data)]);
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

            switch ($type) {
                case 'add':
                    $prev_type_address = UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('type', $request->type)->first();
                    if ($prev_type_address) {
                        $request['id'] = $prev_type_address->id;
                    }

                case 'edit':
                    UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->update(['is_default' => false]);

                    $document['user_id'] = $request->user_id;
                    $document['guest_id'] = $request->guest_id;
                    $document['type'] = $request->type;
                    $document['name'] = $request->name;
                    $document['mobile'] = $request->mobile;
                    $document['alternative_mobile'] = $request->alternative_mobile;
                    $document['location'] = $request->location;
                    $document['latitude'] = $request->latitude;
                    $document['longitude'] = $request->longitude;
                    $document['is_default'] = true;
                    $document['full_address'] = json_encode((object)[
                        'address_line1' => $request->address_line1,
                        'address_line2' => $request->address_line2,
                        'postal_code' => $request->postal_code,
                        'city' => $request->city,
                        'state' => $request->state,
                        'country' => $request->country,
                        'landmark' => $request->landmark,
                    ]);

                    $action = UserAddress::updateorcreate(['id' => $request->id], $document);
                    break;

                case 'fetch':
                    $addresses = UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->orderBy('created_at', 'DESC')->get();

                    foreach ($addresses as $address) {
                        if ($request->has('shop_id') && $request->shop_id) {
                            $address->deliverable = @\Myhelper::validatePurchaseLocation($address->id, $request->shop_id)->status ?? false;
                        } else {
                            $address->deliverable = true;
                        }
                    }

                    $data['addresses'] = $addresses;
                    $action = true;
                    break;

                case 'set-default':
                    UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->update(['is_default' => false]);

                    $action = UserAddress::where('id', $request->id)->update(['is_default' => true]);
                    if ($action) {
                        // Cart::where('user_id', \Auth::guard('api')->id())->delete();
                    }
                    break;

                case 'delete':
                    $address = UserAddress::where('user_id', $request->user_id)->where('guest_id', $request->guest_id)->where('id', $request->id)->first();
                    if (!$address) {
                        return response()->json(['status' => 'error', 'message' => 'Address selected is invalid', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if ($address->is_default == true) {
                        return response()->json(['status' => 'error', 'message' => 'You cannot delete the default address', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    $action = $address->delete();
                    break;
            }

            if ($action) {
                switch ($type) {
                    case 'add':
                        $s_msg = "Address added successfully";
                        break;

                    case 'edit':
                        $s_msg = "Address updated successfully";
                        break;

                    case 'fetch':
                        $s_msg = "Address fetched successfully";
                        break;

                    case 'delete':
                        $s_msg = "Address deleted successfully";
                        break;

                    case 'set-default':
                        $s_msg = "Default address updated successfully";
                        break;

                    default:
                        $s_msg = "Task successfully completed";
                        break;
                }

                return response()->json(['status' => 'success', 'message' => $s_msg, 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Task failed. Please try agaain.', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function notifications(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $notifications = UserNotification::where('user_id', \Auth::guard('api')->id());

            $notifications = $notifications->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $notifications->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $notifications = $notifications->skip($skip)->take(config('app.pagination_records'));
            }

            $notifications = $notifications->get();

            $data['notifications'] = $notifications;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
