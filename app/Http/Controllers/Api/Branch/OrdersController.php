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
use App\Model\Order;
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

    public function fetch(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
                'status' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $orders = Order::with('order_products')->withCount('return_replacements')->where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()));

            /** Filter By Status */
            if ($request->has('status') && $request->status != null) {
                switch ($request->status) {
                    case 'new':
                        $request['status'] = ["received"];
                        break;

                    case 'active':
                        $request['status'] = ["accepted", "processed", "intransit"];
                        break;

                    case 'completed':
                        $request['status'] = ["outfordelivery", "delivered"];
                        break;

                    case 'cancelled':
                        $request['status'] = ["cancelled", "returned"];
                        break;
                }

                // dd($request->status);

                if (is_array($request->status)) {
                    $orders->whereIn('status', $request->status);
                } else {
                    $orders->where('status', $request->status);
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

            $orders = $orders->get();

            $data['orders'] = $orders;

            /** SEND TIMESLOTS FOR UPDATE */
            $data['martTimeslots'] = array();
            $timeslots = [
                '10:00:00',
                '12:00:00',
                '14:00:00',
                '16:00:00',
                '18:00:00',
                '20:00:00',
            ];

            for ($i = 0; $i < 2; $i++) {
                $date = Carbon::now()->addDays($i);
                foreach ($timeslots as $slot) {
                    $t = Carbon::parse($date->format('Y-m-d') . ' ' . $slot);
                    if ($t->gt(Carbon::now())) {
                        array_push($data['martTimeslots'], [
                            'key' => $t->format('Y-m-d H:i:s'),
                            'label' => $t->format('d M y - h:i A'),
                        ]);
                    }
                }
            }


            $data['restaurantPreperationTtimes'] = array(
                ['key' => 10, 'label' => '10 Minutes'],
                ['key' => 20, 'label' => '20 Minutes'],
                ['key' => 40, 'label' => '40 Minutes'],
                ['key' => 60, 'label' => '1 Hour']
            );

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

            $order = Order::with('order_products', 'shop', 'deliveryboy', 'return_replacements')->where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()))->where('id', $request->order_id)->first();
            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'Order not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            if ($order->deliveryboy && $order->deliveryboy_status != 'accepted') {
                $order->deliveryboy = null;
            }

            $data['martTimeslots'] = array();
            $timeslots = [
                '10:00:00',
                '12:00:00',
                '14:00:00',
                '16:00:00',
                '18:00:00',
                '20:00:00',
            ];

            for ($i = 0; $i < 2; $i++) {
                $date = Carbon::now()->addDays($i);
                foreach ($timeslots as $slot) {
                    $t = Carbon::parse($date->format('Y-m-d') . ' ' . $slot);
                    if ($t->gt(Carbon::now())) {
                        array_push($data['martTimeslots'], [
                            'key' => $t->format('Y-m-d H:i:s'),
                            'label' => $t->format('d M y - h:i A'),
                        ]);
                    }
                }
            }


            $data['restaurantPreperationTtimes'] = array(
                ['key' => 10, 'label' => '10 Minutes'],
                ['key' => 20, 'label' => '20 Minutes'],
                ['key' => 40, 'label' => '40 Minutes'],
                ['key' => 60, 'label' => '1 Hour']
            );

            $order->branch_amount = $order->item_total - $order->admin_charge;

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
                    ];

                    $order = Order::where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()))->findorfail($request->order_id);
                    $statusupdt_flag = true;

                    switch ($order->status) {
                        case 'received':
                            $rules['status'] = 'required|in:accepted,cancelled';

                            if ($request->status == 'cancelled') {
                                if ($order->payment_mode != "cash") {
                                    return response()->json(['status' => 'error', 'message' => "You can only cancel cash order, to cancel prepaid orders, contact our support team", 'data' => \Myhelper::formatApiResponseData($data)]);
                                    $statusupdt_flag = false;
                                }
                            }

                            if ($request->status == 'accepted') {
                                switch ($order->type) {
                                    case 'restaurant':
                                    case 'mart':
                                        $rules['order_preperation_time'] = 'required|numeric|min:1|max:60';
                                        break;

                                        // case 'mart':
                                        //     $rules['order_ready_time'] = 'required|after:' . Carbon::now()->format('H:i:s');
                                        //     break;

                                    default:
                                        $statusupdt_flag = false;
                                        break;
                                }
                            }

                            break;

                        case 'accepted':
                            $rules['status'] = 'required|in:processed,intransit';

                            if (!$order->deliveryboy_id && $request->status == 'intransit') {
                                return response()->json(['status' => 'error', 'message' => 'No delivery partner has been assigned for this order. Please wait while we find a delivery partner for the order. If it\'s too late, please contact our support team urgently.', 'data' => \Myhelper::formatApiResponseData($data)]);
                                $statusupdt_flag = false;
                            }

                            if (!in_array($order->deliveryboy_status, ['accepted']) && $request->status == 'intransit') {
                                return response()->json(['status' => 'error', 'message' => 'The delivery partner has not accepted the order. Please wait for his/her acceptance', 'data' => \Myhelper::formatApiResponseData($data)]);
                                $statusupdt_flag = false;
                            }
                            break;

                        case 'processed':
                            $rules['status'] = 'required|in:intransit';

                            if (!$order->deliveryboy_id && $request->status == 'intransit') {
                                return response()->json(['status' => 'error', 'message' => 'No delivery partner has been assigned for this order. Please wait while we find a delivery partner for the order. If it\'s too late, please contact our support team urgently.', 'data' => \Myhelper::formatApiResponseData($data)]);
                                $statusupdt_flag = false;
                            }

                            if (!in_array($order->deliveryboy_status, ['accepted']) && $request->status == 'intransit') {
                                return response()->json(['status' => 'error', 'message' => 'The delivery partner has not accepted the order. Please wait for his/her acceptance', 'data' => \Myhelper::formatApiResponseData($data)]);
                                $statusupdt_flag = false;
                            }
                            break;


                        default:
                            $statusupdt_flag = false;
                    }

                    if ($statusupdt_flag == false) {
                        return response()->json(['status' => 'error', 'message' => "Updating status for this order is not available", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    break;

                case 'returnreplace-status':
                    $rules = [
                        'returnreplace_id' => 'required|exists:order_return_replaces,id',
                    ];

                    $returnreplace = OrderReturnReplace::whereHas('order', function ($q) {
                        $q->where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()));
                    })->findorfail($request->returnreplace_id);
                    $statusupdt_flag = true;

                    switch ($returnreplace->type) {
                        case 'return':
                            switch ($returnreplace->status) {
                                case 'initiated':
                                    $rules['status'] = 'required|in:accepted,rejected';
                                    break;

                                default:
                                    $statusupdt_flag = false;
                            }
                            break;

                        case 'replace':
                            switch ($returnreplace->status) {
                                case 'initiated':
                                    $rules['preperation_time'] = 'required|numeric|min:1|max:60';
                                    $rules['status'] = 'required|in:accepted,rejected';
                                    break;

                                case 'accepted':
                                case 'processed':
                                    if ($request->status != 'processed' && !$returnreplace->deliveryboy_id) {
                                        return response()->json(['status' => 'error', 'message' => "No delivery partner has been assigned for this $returnreplace->type order. Please wait while we find a delivery partner. If it\'s too late, please contact our support team urgently.", 'data' => \Myhelper::formatApiResponseData($data)]);
                                        $statusupdt_flag = false;
                                    }

                                    if ($request->status != 'processed' && !in_array($returnreplace->deliveryboy_status, ['accepted'])) {
                                        return response()->json(['status' => 'error', 'message' => "The delivery partner has not accepted the job. Please wait for his/her acceptance", 'data' => \Myhelper::formatApiResponseData($data)]);
                                        $statusupdt_flag = false;
                                    }

                                    if ($returnreplace->status == 'accepted') $rules['status'] = 'required|in:processed,intransit';
                                    else if ($returnreplace->status == 'processed') $rules['status'] = 'required|in:intransit';
                                    else $statusupdt_flag = false;
                                    break;

                                default:
                                    $statusupdt_flag = false;
                            }
                            break;

                        default:
                            return response()->json(['status' => 'error', 'message' => "Invalid returnreplace.type", 'data' => \Myhelper::formatApiResponseData($data)]);
                            break;
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
                    $update['status'] = $request->status;

                    if ($update['status'] == 'accepted') {
                        switch ($order->type) {
                            case 'restaurant':
                            case 'mart':
                                $update['expected_intransit'] = Carbon::now()->addMinutes($request->order_preperation_time);
                                break;

                                // case 'mart':
                                //     $update['expected_intransit'] = Carbon::parse($request->order_ready_time);
                                //     break;
                        }

                        // $update['expected_delivery'] = $update['expected_intransit']->addMinutes(30);
                    }

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

                case 'returnreplace-status':
                    $update = array();
                    $update['status'] = $request->status;

                    if ($update['status'] == 'accepted' && $returnreplace->type == 'replace') {
                        switch ($returnreplace->order->type) {
                            case 'restaurant':
                            case 'mart':
                                $update['expected_intransit'] = Carbon::now()->addMinutes($request->preperation_time);
                                break;

                                // case 'mart':
                                //     $update['expected_intransit'] = Carbon::parse($request->order_ready_time);
                                //     break;
                        }

                        $update['expected_delivery'] = Carbon::parse($update['expected_intransit'])->addMinutes(30);
                    }

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

                        return response()->json(['status' => 'success', 'message' => "Return replace status updated successfully", 'data' => \Myhelper::formatApiResponseData($data)]);
                    } else {
                        return response()->json(['status' => 'error', 'message' => "Oops!! Something went wrong. Please try again later", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchReturnReplacements(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'page' => 'nullable|numeric',
                'order_id' => 'nullable',
                'status' => 'nullable',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplacements = OrderReturnReplace::with('order', 'returnreplacement_items')
                ->whereHas('order', function ($q) {
                    $q->where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()));
                });

            /** Filter By Order */
            if ($request->has('order_id') && $request->order_id != null) {
                if (is_array($request->order_id)) {
                    $returnreplacements->whereIn('order_id', $request->order_id);
                } else {
                    $returnreplacements->where('order_id', $request->order_id);
                }
            }

            /** Filter By Status */
            if ($request->has('status') && $request->status != null) {
                if (is_array($request->status)) {
                    $returnreplacements->whereIn('status', $request->status);
                } else {
                    $returnreplacements->where('status', $request->status);
                }
            }

            $returnreplacements = $returnreplacements->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $returnreplacements->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $returnreplacements = $returnreplacements->skip($skip)->take(config('app.pagination_records'));
            }

            $returnreplacements = $returnreplacements->get();

            $data['returnreplacements'] = $returnreplacements;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function returnReplacementDetails(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'returnreplacement_id' => 'required|exists:order_return_replaces,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $returnreplace = OrderReturnReplace::with('order', 'returnreplacement_items')
                ->where('id', $request->returnreplacement_id)
                ->whereHas('order', function ($q) {
                    $q->where('shop_id', \Myhelper::getShop(\Auth::guard('api')->id()));
                })->first();

            if (!$returnreplace) {
                return response()->json(['status' => 'error', 'message' => 'Return/Replacement details not found', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $data['returnreplace'] = $returnreplace;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
