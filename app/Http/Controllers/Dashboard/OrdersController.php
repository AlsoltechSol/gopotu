<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Shop;
use App\Model\WalletReport;
use App\User;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function index()
    {
        $data['activemenu'] = [
            'main' => 'orders',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_order')) {
            abort(401);
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
                    array_push($data['martTimeslots'], $t->format('Y-m-d H:i:s'));
                }
            }
        }

        $data['restaurantPreperationTtimes'] = array(
            10 => '10 Minutes',
            20 => '20 Minutes',
            40 => '40 Minutes',
            60 => '1 Hour'
        );

        if (\Myhelper::hasRole(['superadmin', 'admin'])) {
            $data['order_status'] = [
                'received' => config('orderstatus.options')['received'],
                'accepted' => config('orderstatus.options')['accepted'],
                'processed' => config('orderstatus.options')['processed'],
                'intransit' => config('orderstatus.options')['intransit'],
                'outfordelivery' => config('orderstatus.options')['outfordelivery'],
                'delivered' => config('orderstatus.options')['delivered'],
                // 'cancelled' => config('orderstatus.options')['cancelled'],
            ];
        } else if (\Myhelper::hasRole('branch')) {
            $data['order_status'] = [
                'accepted' => config('orderstatus.options')['accepted'],
                'processed' => config('orderstatus.options')['processed'],
                'intransit' => config('orderstatus.options')['intransit'],
            ];
        }

        // dd($data);

        return view('dashboard.orders.index', $data);
    }

    public function view($id, Request $post)
    {
        $data['activemenu'] = [
            'main' => 'orders',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_order')) {
            abort(401);
        }

        $order = Order::with('shop', 'user', 'order_products', 'return_replacements', 'return_replacements.returnreplacement_items')->where('id', $id)->first();
        if (!$order) {
            abort(404);
        }

        // dd($order->return_replacements->toArray());

        $data['order'] = $order;
        return view('dashboard.orders.view', $data);
    }

    
    public function cancel($id, Request $post)
    {
        $data['activemenu'] = [
            'main' => 'orders',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_order')) {
            abort(401);
        }

        $order = Order::with('shop', 'user', 'order_products', 'return_replacements', 'return_replacements.returnreplacement_items')->where('id', $id)->first();
        $order->status = 'cancelled';
        $order->save();
        if (!$order) {
            abort(404);
        }

        // dd($order->return_replacements->toArray());

        $data['order'] = $order;
        return back();
    }

    public function update(Request $post)
    {
        switch ($post->type) {
            case 'orderstatus':
                $rules = [
                    'id' => 'required|exists:orders',
                ];

                $order = Order::findorfail($post->id);
                $statusupdt_flag = true;

                switch ($order->status) {
                    case 'received':
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

                        if (\Myhelper::hasRole('branch')) {
                            $rules['status'] = 'required|in:accepted';
                        } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:accepted,cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }
                        break;

                    case 'accepted':
                        if (\Myhelper::hasRole('branch')) {
                            $rules['status'] = 'required|in:processed,intransit';
                        } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:processed,intransit,cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }

                        if (!$order->deliveryboy_id && $post->status == 'intransit') {
                            return response()->json(['status' => 'No delivery partner has been assigned for this order. Please wait while we find a delivery partner for the order. If it\'s too late, please contact our support team urgently.'], 400);
                            $statusupdt_flag = false;
                        }

                        if (!in_array($order->deliveryboy_status, ['accepted']) && $post->status == 'intransit') {
                            return response()->json(['status' => 'The delivery partner has not accepted the order. Please wait for his/her acceptance'], 400);
                            $statusupdt_flag = false;
                        }
                        break;

                    case 'processed':
                        if (\Myhelper::hasRole('branch')) {
                            $rules['status'] = 'required|in:intransit';
                        } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:intransit,cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }

                        if (!$order->deliveryboy_id && $post->status == 'intransit') {
                            return response()->json(['status' => 'No delivery partner has been assigned for this order. Please wait while we find a delivery partner for the order. If it\'s too late, please contact our support team urgently.'], 400);
                            $statusupdt_flag = false;
                        }

                        if (!in_array($order->deliveryboy_status, ['accepted']) && $post->status == 'intransit') {
                            return response()->json(['status' => 'The delivery partner has not accepted the order. Please wait for his/her acceptance'], 400);
                            $statusupdt_flag = false;
                        }
                        break;

                    case 'intransit':
                        if (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:outfordelivery,cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }

                        if (!$order->deliveryboy_id && $post->status == 'outfordelivery') {
                            return response()->json(['status' => 'No delivery partner has been assigned for this order. Please wait while we find a delivery partner for the order. If it\'s too late, please contact our support team urgently.'], 400);
                            $statusupdt_flag = false;
                        }

                        if (!in_array($order->deliveryboy_status, ['accepted']) && $post->status == 'outfordelivery') {
                            return response()->json(['status' => 'The delivery partner has not accepted the order. Please wait for his/her acceptance'], 400);
                            $statusupdt_flag = false;
                        }
                        break;

                    case 'outfordelivery':
                        if (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:delivered,cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }
                        break;

                    case 'delivered':
                        if (\Myhelper::hasRole(['superadmin', 'admin'])) {
                            $rules['status'] = 'required|in:cancelled,returned';
                        } else {
                            $statusupdt_flag = false;
                        }
                        break;
                    default:
                        $statusupdt_flag = false;
                }

                if ($statusupdt_flag == false) {
                    return response()->json(['status' => 'Status checking not allowed'], 400);
                }

                $permission = 'update_order_status';
                break;

            case 'assigndeliveryboy':
                $order = Order::findorfail($post->id);

                if ($order->deliveryboy_id) {
                    return response()->json(['status' => 'Delivery boy is already assigned for the order'], 400);
                }

                $rules = [
                    'id' => 'required|exists:orders',
                    'deliveryboy_id' => 'required|exists:users,id',
                ];

                $permission = 'assign_delivery_boy';
                break;

            default:
                return response()->json(['status' => 'Request not found'], 400);
                break;
        }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => 'Permission not Allowed'], 401);
        }

        switch ($post->type) {
            case 'orderstatus':
                $update = array();
                $update['status'] = $post->status;

                if ($update['status'] == 'accepted') {
                    switch ($order->type) {
                        case 'restaurant':
                        case 'mart':
                            $update['expected_intransit'] = Carbon::now()->addMinutes($post->order_preperation_time);
                            break;

                            // case 'mart':
                            //     $update['expected_intransit'] = Carbon::parse($post->order_ready_time);
                            //     break;
                    }

                    // $update['expected_delivery'] = Carbon::parse($update['expected_intransit'])->addMinutes(30);
                }

                $action = Order::where('id', $post->id)->update($update);
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
                    \Myhelper::updateOrderStatusLog($post->id);
                }
                break;

            case 'assigndeliveryboy':
                $deliveryboy = User::findorfail($post->deliveryboy_id);

                $check = \Myhelper::checkDeliveryBoyAvailabality($deliveryboy->id, 'order', $order->id);
                if ($check->status == false) {
                    return response()->json(['status' => $check->message], 400);
                }

                $action = Order::where('id', $post->id)->update(['deliveryboy_id' => $post->deliveryboy_id, 'deliveryboy_status' => 'pending']);
                if ($action) {
                    /**
                     * ---------------------------------
                     * MAKE ORDER ACTIONS
                     * ---------------------------------
                     */
                    \Myhelper::updateOrderStatusAction($post->id, 'deliveryassigned');
                }
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task completed successfully'], 200);
        } else {
            return response()->json(['status' => 'Task failed! Please try again later'], 400);
        }
    }

    public function ajax(Request $post)
    {
        switch ($post->type) {
            case 'order-deliveryboy-assign':
                $rules = [
                    'order_id' => 'required|exists:orders,id',
                    'km_radius' => 'required|numeric|min:1|max:100',
                ];

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => $value[0]], 400);
                    }
                }

                $order = Order::findorfail($post->order_id);

                $avail_drivers = \Myhelper::getAvailableDrivers($order->shop->shop_latitude, $order->shop->shop_longitude, $order->id, $post->km_radius, false);
                $delivery_boys = User::whereIn('id', $avail_drivers)
                    ->where('online', '1')
                    ->where('status', '1')
                    ->whereHas('role', function ($q) use ($order) {
                        $q->where('slug', 'deliveryboy');
                    })->get();

                $data['order'] = $order;
                $data['delivery_boys'] = $delivery_boys;
                return response()->json(['status' => 'Success', 'data' => $data], 200);

                break;

            default:
                return response()->json(['status' => 'Unsupported Request'], 400);
                break;
        }
    }
}
