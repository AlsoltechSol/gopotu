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
use App\Guest;
use Carbon\Carbon;

class GuestController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function login(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'fcm_token' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            // return response()->json(['status' => 'error', 'message' => 'The guest login system is currently down. Please check after sometime', 'data' => \Myhelper::formatApiResponseData($data)]);


            $data['token'] = \Str::random(50);

            $guest = Guest::updateorcreate(
                ['fcm_token' => $request->fcm_token],
                ['fcm_token' => $request->fcm_token, 'token' => $data['token']]
            );

            if ($guest) {
                $data['guest'] = $guest;
                $data['default_address'] = null;
                return response()->json(['status' => 'success', 'message' => 'Logedin as guest successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Guest token cannot be generated', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
