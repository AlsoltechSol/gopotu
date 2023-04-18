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

use App\User;
use App\Model\Order;
use App\Model\OrderDeliveryboyLog;
use App\Model\OrderReturnReplace;
use App\Model\OrderReturnReplaceDeliveryboyLog;
use Carbon\Carbon;

class ReturnReplaceController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function fetch(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
                'status' => 'required|in:new,active,completed,rejected',
                'type' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplaces = OrderReturnReplace::with('order', 'order.shop');

            switch ($request->status) {
                case 'new':
                    $returnreplaces->whereIn('deliveryboy_status', ['pending']);
                    $status_checking = false;
                    $user_restricted = true;
                    break;

                case 'active':
                    $returnreplaces->whereIn('deliveryboy_status', ['accepted']);
                    $request['job_status'] = ['initiated', 'accepted', 'processed', 'intransit', 'outfordelivery', 'outforpickup', 'outforstore'];
                    $status_checking = true;
                    $user_restricted = true;
                    break;

                case 'completed':
                    $request['job_status'] = ["deliveredtostore"];
                    $status_checking = true;
                    $user_restricted = true;
                    break;

                case 'rejected':
                    $returnreplaces->whereHas('deliveryboy_logs', function ($q) {
                        $q->where('deliveryboy_id', \Auth::guard('api')->id());
                        $q->where('status', 'rejected');
                    });

                    $status_checking = false;
                    $user_restricted = false;
                    break;

                default:
                    return response()->json(['status' => 'error', 'message' => "Invalid order status", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($user_restricted == true) {
                $returnreplaces->where('deliveryboy_id', \Auth::guard('api')->id());
            }

            if ($status_checking == true) {
                $returnreplaces->whereIn('status', $request->job_status);
            }

            /** Filter By Types */
            if ($request->has('type') && $request->type != null) {
                if (is_array($request->type)) {
                    $returnreplaces->whereIn('type', $request->type);
                } else {
                    $returnreplaces->where('type', $request->type);
                }
            }

            $returnreplaces = $returnreplaces->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $returnreplaces->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $returnreplaces = $returnreplaces->skip($skip)->take(config('app.pagination_records'));
            }

            $data['returnreplaces'] = $returnreplaces->get();
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function details(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'returnreplace_id' => 'required|exists:order_return_replaces,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplace = OrderReturnReplace::with('returnreplacement_items', 'order', 'order.shop', 'deliveryboy')->where('deliveryboy_id', \Auth::guard('api')->id())->where('id', $request->returnreplace_id)->first();
            if (!$returnreplace) {
                if (OrderReturnReplaceDeliveryboyLog::where('returnreplace_id', $request->returnreplace_id)->where('deliveryboy_id', \Auth::guard('api')->id())->where('status', 'rejected')->exists()) {
                    $returnreplace = OrderReturnReplace::with('returnreplacement_items', 'order', 'order.shop', 'deliveryboy')->where('id', $request->returnreplace_id)->first();
                }

                if (!$returnreplace) {
                    return response()->json(['status' => 'error', 'message' => 'Job not found', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $data['returnreplace'] = $returnreplace;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function update($type, Request $request)
    {
        $data = array();
        try {
            switch ($type) {
                case 'status':
                    $rules = [
                        'returnreplace_id' => 'required|exists:order_return_replaces,id',
                        'latitude' => 'required|numeric',
                        'longitude' => 'required|numeric',
                    ];

                    $returnreplace = OrderReturnReplace::where('deliveryboy_id', \Auth::guard('api')->id())->findorfail($request->returnreplace_id);
                    $statusupdt_flag = true;

                    if ($returnreplace->deliveryboy_status == 'pending') {
                        $rules['status'] = 'required|in:accepted,rejected';
                        $rules['rejection_reason'] = 'required_if:status,==,rejected';
                    } else if ($returnreplace->deliveryboy_status == 'accepted') {
                        switch ($returnreplace->type) {
                            case 'return':
                                switch ($returnreplace->status) {
                                    case 'accepted':
                                    case 'outforpickup':
                                    case 'outforstore':
                                        if (!in_array($returnreplace->deliveryboy_status, ['accepted'])) {
                                            return response()->json(['status' => 'error', 'message' => "Please accept the job", 'data' => \Myhelper::formatApiResponseData($data)]);
                                            $statusupdt_flag = false;
                                        }

                                        if ($returnreplace->status == 'accepted') $rules['status'] = 'required|in:outforpickup';
                                        else if ($returnreplace->status == 'outforpickup') $rules['status'] = 'required|in:outforstore';
                                        else if ($returnreplace->status == 'outforstore') $rules['status'] = 'required|in:deliveredtostore';
                                        else $statusupdt_flag = false;
                                        break;

                                    default:
                                        $statusupdt_flag = false;
                                }
                                break;

                            case 'replace':
                                switch ($returnreplace->status) {
                                    case 'intransit':
                                    case 'outfordelivery':
                                    case 'outforstore':
                                        if (!in_array($returnreplace->deliveryboy_status, ['accepted'])) {
                                            return response()->json(['status' => 'error', 'message' => "Please accept the job", 'data' => \Myhelper::formatApiResponseData($data)]);
                                            $statusupdt_flag = false;
                                        }

                                        if ($returnreplace->status == 'intransit') $rules['status'] = 'required|in:outfordelivery';
                                        else if ($returnreplace->status == 'outfordelivery') $rules['status'] = 'required|in:outforstore';
                                        else if ($returnreplace->status == 'outforstore') $rules['status'] = 'required|in:deliveredtostore';
                                        else $statusupdt_flag = false;
                                        break;

                                    default:
                                        $statusupdt_flag = false;
                                        break;
                                }
                                break;

                            default:
                                $statusupdt_flag = false;
                                break;
                        }
                    } else {
                        $statusupdt_flag = false;
                    }

                    /*switch ($returnreplace->status) {
                        case 'initiated':
                        case 'accepted':
                        case 'processed':
                            if ($order->deliveryboy_status != 'pending') {
                                $statusupdt_flag = false;
                                return response()->json(['status' => 'error', 'message' => "Updating status for this order is not available", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }

                            $rules['status'] = 'required|in:accepted,rejected';
                            $rules['rejection_reason'] = 'required_if:status,==,rejected';
                            break;

                        case 'intransit':
                            $rules['status'] = 'required|in:outfordelivery';
                            break;

                        case 'outfordelivery':
                            $rules['status'] = 'required|in:delivered';
                            break;

                        default:
                            $statusupdt_flag = false;
                    }*/

                    if ($statusupdt_flag == false) {
                        return response()->json(['status' => 'error', 'message' => "Updating status for this job is not available", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
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

            switch ($type) {
                case 'status':
                    $update = array();

                    if ($returnreplace->deliveryboy_status == 'pending') {
                        switch ($request->status) {
                            case 'accepted':
                                $logdocument = [
                                    'returnreplace_id' => $returnreplace->id,
                                    'deliveryboy_id' => \Auth::guard('api')->id(),
                                    'status' => 'accepted',
                                    'description' => null,
                                ];

                                $update['deliveryboy_status'] = 'accepted';
                                $action = OrderReturnReplace::where('id', $returnreplace->id)->update($update);
                                if ($action) {
                                    OrderReturnReplaceDeliveryboyLog::create($logdocument);

                                    /**
                                     * ---------------------------------
                                     * MAKE ORDER ACTIONS
                                     * ---------------------------------
                                     */
                                    \Myhelper::updateReturnReplaceStatusAction($returnreplace->id, 'deliveryaccepted');

                                    /**
                                     * ---------------------------------
                                     * UPDATE ORDER STATUS LOG
                                     * ---------------------------------
                                     */
                                    \Myhelper::updateOrderReturnReplaceStatusLog($returnreplace->id, 'deliveryboyassigned');

                                    /**
                                     * ---------------------------------
                                     * UPDATING DELIVERY BOY LAT & LONG
                                     * ---------------------------------
                                     */
                                    User::where('id', \Auth::guard('api')->id())->update([
                                        'latitude' => $request->latitude,
                                        'longitude' => $request->longitude,
                                    ]);

                                    return response()->json(['status' => 'success', 'message' => "Job accepted successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                                } else {
                                    return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                                }
                                break;

                            case 'rejected':
                                $logdocument = [
                                    'returnreplace_id' => $returnreplace->id,
                                    'deliveryboy_id' => \Auth::guard('api')->id(),
                                    'status' => 'rejected',
                                    'description' => $request->rejection_reason,
                                ];

                                $update['deliveryboy_id'] = null;
                                $update['deliveryboy_status'] = null;
                                $update['deliveryboy_reachedstore'] = '0';
                                $action = OrderReturnReplace::where('id', $returnreplace->id)->update($update);
                                if ($action) {
                                    OrderReturnReplaceDeliveryboyLog::create($logdocument);

                                    /**
                                     * ---------------------------------
                                     * MAKE ORDER ACTIONS
                                     * ---------------------------------
                                     */
                                    \Myhelper::updateReturnReplaceStatusAction($returnreplace->id, 'deliveryrejected');

                                    /**
                                     * ---------------------------------
                                     * UPDATING DELIVERY BOY LAT & LONG
                                     * ---------------------------------
                                     */
                                    User::where('id', \Auth::guard('api')->id())->update([
                                        'latitude' => $request->latitude,
                                        'longitude' => $request->longitude,
                                    ]);

                                    return response()->json(['status' => 'success', 'message' => "Job rejected successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                                } else {
                                    return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                                }
                                break;

                            default:
                                return response()->json(['status' => 'success', 'message' => "The status mismatch exception occured", 'data' => \Myhelper::formatApiResponseData($data)]);
                                break;
                        }
                    } else {
                        $update['status'] = $request->status;

                        $action = OrderReturnReplace::where('id', $returnreplace->id)->update($update);
                        if ($action) {
                            /**
                             * ---------------------------------
                             * MAKE ORDER ACTIONS
                             * ---------------------------------
                             */
                            \Myhelper::updateReturnReplaceStatusAction($returnreplace->id);

                            /**
                             * ---------------------------------
                             * UPDATE ORDER STATUS LOG
                             * ---------------------------------
                             */
                            \Myhelper::updateOrderReturnReplaceStatusLog($returnreplace->id);

                            /**
                             * ---------------------------------
                             * UPDATING DELIVERY BOY LAT & LONG
                             * ---------------------------------
                             */
                            User::where('id', \Auth::guard('api')->id())->update([
                                'latitude' => $request->latitude,
                                'longitude' => $request->longitude,
                            ]);

                            return response()->json(['status' => 'success', 'message' => "The job status updated successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                        } else {
                            return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
