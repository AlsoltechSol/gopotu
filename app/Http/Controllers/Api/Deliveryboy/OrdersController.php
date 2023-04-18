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
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function running(Request $request)
    {
        $data = array();
        try {
            $deliveryboy = User::find(\Auth::guard('api')->id());

            // GET RUNNING REGULAR ORDER
            $order = Order::with('shop')->where('deliveryboy_id', \Auth::guard('api')->id())->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery'])->first();
            if ($order) {
                /**
                 * Calculation Distance & Duration from origin and destination
                 */

                $distance = [
                    'deliveryboytoshop' => [
                        'distance_value' => 0,
                        'distance_text' => "0 km",
                        'duration_value' => 0,
                        'duration_text' => "0 min",
                    ],
                    'shoptocustomer' => [
                        'distance_value' => 0,
                        'distance_text' => "0 km",
                        'duration_value' => 0,
                        'duration_text' => "0 min",
                    ],
                    'total'  => [
                        'distance_value' => 0,
                        'distance_text' => "0 km",
                        'duration_value' => 0,
                        'duration_text' => "0 min",
                    ],
                    'deliveryboytocustomer' => [
                        'distance_value' => 0,
                        'distance_text' => "0 km",
                        'duration_value' => 0,
                        'duration_text' => "0 min",
                    ],
                ];

                if ($deliveryboy->latitude && $deliveryboy->longitude && $order->shop->shop_latitude && $order->shop->shop_longitude) {
                    $deliveryboytoshop_dist = \Myhelper::getDistanceMatric($deliveryboy->latitude, $deliveryboy->longitude, $order->shop->shop_latitude, $order->shop->shop_longitude);
                    if ($deliveryboytoshop_dist) {
                        $distance['deliveryboytoshop'] = [
                            'distance_value' => $deliveryboytoshop_dist->distance_value,
                            'distance_text' => $deliveryboytoshop_dist->distance_text,
                            'duration_value' => $deliveryboytoshop_dist->duration_value,
                            'duration_text' => $deliveryboytoshop_dist->duration_text,
                        ];
                    }
                }

                if ($order->shop->shop_latitude && $order->shop->shop_longitude && $order->cust_latitude && $order->cust_longitude) {
                    $shoptocustomer_dist = \Myhelper::getDistanceMatric($order->shop->shop_latitude, $order->shop->shop_longitude, $order->cust_latitude, $order->cust_longitude);
                    if ($shoptocustomer_dist) {
                        $distance['shoptocustomer'] = [
                            'distance_value' => $shoptocustomer_dist->distance_value,
                            'distance_text' => $shoptocustomer_dist->distance_text,
                            'duration_value' => $shoptocustomer_dist->duration_value,
                            'duration_text' => $shoptocustomer_dist->duration_text,
                        ];
                    }
                }

                $total_distance = $distance['shoptocustomer']['distance_value'] + $distance['deliveryboytoshop']['distance_value'];
                $total_duration = $distance['shoptocustomer']['duration_value'] + $distance['deliveryboytoshop']['duration_value'];

                $distance['total'] = [
                    'distance_value' => $total_distance,
                    'distance_text' => round($total_distance / 1000, 1) . " km",
                    'duration_value' => $total_duration,
                    'duration_text' => round($total_duration / 60) . " mins",
                ];

                if ($order->deliveryboy->latitude && $order->deliveryboy->longitude && $order->cust_latitude && $order->cust_longitude) {
                    $deliveryboytocustomer_dist = \Myhelper::getDistanceMatric($order->deliveryboy->latitude, $order->deliveryboy->longitude, $order->cust_latitude, $order->cust_longitude);
                    if ($deliveryboytocustomer_dist) {
                        $distance['deliveryboytocustomer'] = [
                            'distance_value' => $deliveryboytocustomer_dist->distance_value,
                            'distance_text' => $deliveryboytocustomer_dist->distance_text,
                            'duration_value' => $deliveryboytocustomer_dist->duration_value,
                            'duration_text' => $deliveryboytocustomer_dist->duration_text,
                        ];
                    }
                }

                $order->distance = $distance;
            } else {
                // return response()->json(['status' => 'success', 'message' => "Currently there is no running order", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            // GET RUNNING RETURN/REPLACE JOB
            $returnreplace = OrderReturnReplace::with('order', 'order.shop')->where('deliveryboy_id', \Auth::guard('api')->id())->whereIn('status', ['initiated', 'accepted', 'processed', 'intransit', 'outfordelivery', 'outforpickup', 'outforstore'])->first();
            if ($returnreplace) {
            } else {
                // return response()->json(['status' => 'success', 'message' => "Currently there is no running return replace job", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['order'] = $order;
            $data['returnreplace'] = $returnreplace;
            $data['deliveryboy'] = \Auth::user();
            return response()->json(['status' => 'success', 'message' => "Success", 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
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

            $orders = Order::with('order_products', 'shop');

            switch ($request->status) {
                case 'new':
                    $orders->whereIn('deliveryboy_status', ['pending']);

                case 'active':
                    $request['order_status'] = ["received", "processed", "accepted", "intransit", "outfordelivery"];
                    $status_checking = true;
                    $user_restricted = true;
                    break;

                case 'completed':
                    $request['order_status'] = ["delivered", "cancelled", "returned"];
                    $status_checking = true;
                    $user_restricted = true;
                    break;

                case 'rejected':
                    $orders->whereHas('deliveryboy_logs', function ($q) {
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
                $orders->where('deliveryboy_id', \Auth::guard('api')->id());
            }

            if ($status_checking == true) {
                $orders->whereIn('status', $request->order_status);
            }

            if ($request->has('type') && $request->type) {
            }

            /** Filter By Types */
            if ($request->has('type') && $request->type != null) {
                if (is_array($request->type)) {
                    $orders->whereIn('type', $request->type);
                } else {
                    $orders->where('type', $request->type);
                }
            }

            $orders = $orders->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $orders->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $orders = $orders->skip($skip)->take(config('app.pagination_records'));
            }

            $data['orders'] = $orders->get();
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
                'order_id' => 'required|exists:orders,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $order = Order::with('order_products', 'shop', 'deliveryboy')->where('deliveryboy_id', \Auth::guard('api')->id())->where('id', $request->order_id)->first();
            if (!$order) {
                if (OrderDeliveryboyLog::where('order_id', $request->order_id)->where('deliveryboy_id', \Auth::guard('api')->id())->where('status', 'rejected')->exists()) {
                    $order = Order::with('order_products', 'shop', 'deliveryboy')->where('id', $request->order_id)->first();
                }

                if (!$order) {
                    return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $data['order'] = $order;
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
                        'order_id' => 'required|exists:orders,id',
                        'latitude' => 'required|numeric',
                        'longitude' => 'required|numeric',
                    ];

                    $order = Order::where('deliveryboy_id', \Auth::guard('api')->id())->findorfail($request->order_id);
                    $statusupdt_flag = true;

                    if ($request->status == 'reachedstore') {
                        if ($order->deliveryboy_status != 'accepted') {
                            $statusupdt_flag = false;
                            return response()->json(['status' => 'error', 'message' => "Please accept the order first, before start heading towards the store", 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        if ($order->deliveryboy_reachedstore) {
                            $statusupdt_flag = false;
                            return response()->json(['status' => 'error', 'message' => "You have already reached the store, please pickup the order and be ready for out of the delivery", 'data' => \Myhelper::formatApiResponseData($data)]);
                        }

                        if ($order->deliveryboy_id != \Auth::guard('api')->id()) {
                            $statusupdt_flag = false;
                            return response()->json(['status' => 'error', 'message' => "The order is not assigned to you", 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                    } else {
                        switch ($order->status) {
                            case 'received':
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
                        }
                    }

                    if ($statusupdt_flag == false) {
                        return response()->json(['status' => 'error', 'message' => "Updating status for this order is not available", 'data' => \Myhelper::formatApiResponseData($data)]);
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

                    switch ($request->status) {
                        case 'accepted':
                            $document = [
                                'order_id' => $request->order_id,
                                'deliveryboy_id' => \Auth::guard('api')->id(),
                                'status' => 'accepted',
                                'description' => null,
                            ];

                            $distance = \Myhelper::locationDistance($request->latitude, $request->longitude, $order->shop->shop_latitude, $order->shop->shop_longitude, 'km');
                            if ($distance > 100) {
                                return response()->json(['status' => 'success', 'message' => "Your location is out of range", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }

                            $update['deliveryboy_status'] = 'accepted';
                            $action = Order::where('id', $request->order_id)->update($update);
                            if ($action) {
                                OrderDeliveryboyLog::create($document);

                                /**
                                 * ---------------------------------
                                 * MAKE ORDER ACTIONS
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusAction($order->id, 'deliveryaccepted');

                                /**
                                 * ---------------------------------
                                 * UPDATE ORDER STATUS LOG
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusLog($order->id, 'deliveryboyassigned');

                                /**
                                 * ---------------------------------
                                 * UPDATING DELIVERY BOY LAT & LONG
                                 * ---------------------------------
                                 */
                                User::where('id', \Auth::guard('api')->id())->update([
                                    'latitude' => $request->latitude,
                                    'longitude' => $request->longitude,
                                ]);

                                return response()->json(['status' => 'success', 'message' => "Order accepted successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                            } else {
                                return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                            break;

                        case 'rejected':
                            $document = [
                                'order_id' => $request->order_id,
                                'deliveryboy_id' => \Auth::guard('api')->id(),
                                'status' => 'rejected',
                                'description' => $request->rejection_reason,
                            ];

                            $update['deliveryboy_status'] = null;
                            $update['deliveryboy_id'] = null;
                            $action = Order::where('id', $request->order_id)->update($update);
                            if ($action) {
                                OrderDeliveryboyLog::create($document);

                                /**
                                 * ---------------------------------
                                 * MAKE ORDER ACTIONS
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusAction($order->id, 'deliveryrejected');

                                return response()->json(['status' => 'success', 'message' => "Order rejected successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                            } else {
                                return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                            break;

                        case 'reachedstore':
                            $update['deliveryboy_reachedstore'] = 1;

                            $action = Order::where('id', $request->order_id)->update($update);
                            if ($action) {
                                /**
                                 * ---------------------------------
                                 * MAKE ORDER ACTIONS
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusAction($order->id, 'deliveryreachedstore');

                                /**
                                 * ---------------------------------
                                 * UPDATE ORDER STATUS LOG
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusLog($order->id, 'deliveryreachedstore');

                                return response()->json(['status' => 'success', 'message' => "The status updated successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                            } else {
                                return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                            break;

                        default:
                            $update['status'] = $request->status;

                            $action = Order::where('id', $request->order_id)->update($update);
                            if ($action) {
                                /**
                                 * ---------------------------------
                                 * MAKE ORDER ACTIONS
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusAction($order->id);

                                /**
                                 * ---------------------------------
                                 * UPDATE ORDER STATUS LOG
                                 * ---------------------------------
                                 */
                                \Myhelper::updateOrderStatusLog($request->order_id);

                                return response()->json(['status' => 'success', 'message' => "Order status updated successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                            } else {
                                return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                            }
                            break;
                    }
                    break;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
