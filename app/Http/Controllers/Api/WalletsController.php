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
use App\Model\Order;
use App\Model\WalletReport;
use App\User;
use Carbon\Carbon;

class WalletsController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function fetchStatement($type, Request $request)
    {
        $data = array();
        try {
            switch ($type) {
                case 'userwallet':
                    $rules = [
                        'page' => 'nullable|numeric',
                        'service' => 'nullable|in:order,payout',
                        'start_date' => 'nullable|required_with:end_date|date',
                        'end_date' => 'nullable|required_with:start_date|date|after_or_equal:start_date',
                    ];

                    $request['wallet_type'] = $type;
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => "Invalid request received", 'data' => \Myhelper::formatApiResponseData($data)]);
                    break;
            }

            if (isset($rules)) {
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }
            }

            $query = WalletReport::where('user_id', \Auth::guard('api')->id())->where('wallet_type', $request->wallet_type);

            if ($request->has('start_date') && $request->has('end_date')) {
                $request['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d') . " 00:00:00";
                $request['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d') . " 23:59:59";

                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            if ($request->has('service') && $request->service) {
                $query->where('service', $request->service);
            }

            $query->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $query->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $query = $query->skip($skip)->take(config('app.pagination_records'));
            }

            $statement = $query->get();

            $data['statement'] = $statement;
            // $data[$request->wallet_type] = \Auth::user()[$request->wallet_type];
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function getDashboard($type, Request $request)
    {
        $data = array();

        try {
            $userdata = User::find(\Auth::id());

            switch ($type) {
                case 'userwallet':
                    $data['balance'] = $userdata[$type];
                    $data['totalused'] = Order::where('user_id', \Auth::guard('api')->user()->id)
                        ->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered'])
                        ->sum('wallet_deducted');
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => "Invalid request received", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
