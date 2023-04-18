<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Model\Order;
use App\Model\OrderReturnReplace;
use App\Model\OrderReturnReplaceItem;
use App\User;

class ReturnReplacementsController extends Controller
{
    public function index()
    {
        $data['activemenu'] = [
            'main' => 'returnreplacements',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_return_replacement_requests')) {
            abort(401);
        }

        if (\Myhelper::hasRole(['superadmin', 'admin'])) {
            $data['return_status'] = [
                'initiated' => config('returnreplacestatus.options')['initiated'],
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'outforpickup' => config('returnreplacestatus.options')['outforpickup'],
                'outforstore' => config('returnreplacestatus.options')['outforstore'],
                'deliveredtostore' => config('returnreplacestatus.options')['deliveredtostore'],
                'rejected' => config('returnreplacestatus.options')['rejected'],
            ];

            $data['replace_status'] = [
                'initiated' => config('returnreplacestatus.options')['initiated'],
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'processed' => config('returnreplacestatus.options')['processed'],
                'intransit' => config('returnreplacestatus.options')['intransit'],
                'outfordelivery' => config('returnreplacestatus.options')['outfordelivery'],
                'outforstore' => config('returnreplacestatus.options')['outforstore'],
                'deliveredtostore' => config('returnreplacestatus.options')['deliveredtostore'],
                'rejected' => config('returnreplacestatus.options')['rejected'],
            ];
        } else if (\Myhelper::hasRole('branch')) {
            $data['return_status'] = [
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'rejected' => config('returnreplacestatus.options')['rejected']
            ];

            $data['replace_status'] = [
                'accepted' => config('returnreplacestatus.options')['accepted'],
                'processed' => config('returnreplacestatus.options')['processed'],
                'intransit' => config('returnreplacestatus.options')['intransit'],
                'rejected' => config('returnreplacestatus.options')['rejected'],
            ];
        }

        $data['martTimeslots'] = [];
        $data['restaurantPreperationTtimes'] = array(
            10 => '10 Minutes',
            20 => '20 Minutes',
            40 => '40 Minutes',
            60 => '1 Hour'
        );

        return view('dashboard.returnreplacements.index', $data);
    }

    public function create(Request $request)
    {
        $data['activemenu'] = [
            'main' => 'returnreplacements',
            'sub' => 'index',
        ];

        if (!\Myhelper::can(['create_return_request', 'create_replacement_request'])) {
            abort(401);
        }

        return view('dashboard.returnreplacements.create', $data);
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'createreturnreplacementrequest':
                $rules = [
                    'order_id' => 'required|exists:orders,id',
                    'type' => 'required|in:replace,return',
                    'order_products' => 'required|array|min:1',
                    'order_products.*' => 'required|exists:order_products,id',
                ];

                switch ($post->type) {
                    case 'replace':
                        $permission = 'create_replacement_request';
                        break;

                    case 'return':
                        $permission = 'create_return_request';
                        break;
                }
                break;

            case 'statusupdate':
                $rules = [
                    'id' => 'required|exists:order_return_replaces,id',
                ];

                $returnreplace = OrderReturnReplace::findorfail($post->id);
                $statusupdt_flag = true;

                switch ($returnreplace->type) {
                    case 'return':
                        $post['status'] = $post->return_status; // Assign the Return Status to 'status'

                        switch ($returnreplace->status) {
                            case 'initiated':
                                if (\Myhelper::hasRole('branch')) {
                                    $rules['status'] = 'required|in:accepted,rejected';
                                } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                                    $rules['status'] = 'required|in:accepted,rejected';
                                } else {
                                    $statusupdt_flag = false;
                                }
                                break;

                            case 'accepted':
                            case 'outforpickup':
                            case 'outforstore':
                                if (!$returnreplace->deliveryboy_id) {
                                    return response()->json(['status' => "No delivery partner has been assigned for this $returnreplace->type order. Please wait while we find a delivery partner. If it\'s too late, please contact our support team urgently."], 400);
                                    $statusupdt_flag = false;
                                }

                                if (!in_array($returnreplace->deliveryboy_status, ['accepted'])) {
                                    return response()->json(['status' => 'The delivery partner has not accepted the job. Please wait for his/her acceptance'], 400);
                                    $statusupdt_flag = false;
                                }

                                if (\Myhelper::hasRole(['superadmin', 'admin'])) {
                                    if ($returnreplace->status == 'accepted') $rules['status'] = 'required|in:outforpickup';
                                    else if ($returnreplace->status == 'outforpickup') $rules['status'] = 'required|in:outforstore';
                                    else if ($returnreplace->status == 'outforstore') $rules['status'] = 'required|in:deliveredtostore';
                                } else {
                                    $statusupdt_flag = false;
                                }
                                break;

                            default:
                                $statusupdt_flag = false;
                        }
                        break;

                    case 'replace':
                        $post['status'] = $post->replace_status; // Assign the Replace Status to 'status'

                        switch ($returnreplace->status) {
                            case 'initiated':
                                $rules['preperation_time'] = 'required|numeric|min:1|max:60';

                                if (\Myhelper::hasRole('branch')) {
                                    $rules['status'] = 'required|in:accepted,rejected';
                                } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                                    $rules['status'] = 'required|in:accepted,rejected';
                                } else {
                                    $statusupdt_flag = false;
                                }
                                break;

                            case 'accepted':
                            case 'processed':
                            case 'intransit':
                            case 'outfordelivery':
                            case 'outforstore':
                                if ($post->status != 'processed' && !$returnreplace->deliveryboy_id) {
                                    return response()->json(['status' => "No delivery partner has been assigned for this $returnreplace->type order. Please wait while we find a delivery partner. If it\'s too late, please contact our support team urgently."], 400);
                                    $statusupdt_flag = false;
                                }

                                if ($post->status != 'processed' && !in_array($returnreplace->deliveryboy_status, ['accepted'])) {
                                    return response()->json(['status' => 'The delivery partner has not accepted the job. Please wait for his/her acceptance'], 400);
                                    $statusupdt_flag = false;
                                }

                                if (\Myhelper::hasRole('branch')) {
                                    if ($returnreplace->status == 'accepted') $rules['status'] = 'required|in:processed,intransit';
                                    else if ($returnreplace->status == 'processed') $rules['status'] = 'required|in:intransit';
                                    else $statusupdt_flag = false;
                                } elseif (\Myhelper::hasRole(['superadmin', 'admin'])) {
                                    if ($returnreplace->status == 'accepted') $rules['status'] = 'required|in:processed,intransit';
                                    else if ($returnreplace->status == 'processed') $rules['status'] = 'required|in:intransit';
                                    else if ($returnreplace->status == 'intransit') $rules['status'] = 'required|in:outfordelivery';
                                    else if ($returnreplace->status == 'outfordelivery') $rules['status'] = 'required|in:outforstore';
                                    else if ($returnreplace->status == 'outforstore') $rules['status'] = 'required|in:deliveredtostore';
                                    else $statusupdt_flag = false;
                                } else {
                                    $statusupdt_flag = false;
                                }
                                break;


                            default:
                                $statusupdt_flag = false;
                        }
                        break;

                    default:
                        return response()->json(['status' => "Invalid returnreplace.type"], 400);
                        break;
                }

                if ($statusupdt_flag == false) {
                    return response()->json(['status' => 'Status updating not allowed'], 400);
                }

                $permission = 'update_return_replacement_status';
                break;

            case 'assigndeliveryboy':
                $returnreplace = OrderReturnReplace::findorfail($post->id);

                if ($returnreplace->deliveryboy_id) {
                    return response()->json(['status' => 'Delivery boy is already assigned'], 400);
                }

                $rules = [
                    'id' => 'required|exists:order_return_replaces,id',
                    'deliveryboy_id' => 'required|exists:users,id',
                ];

                $permission = 'assign_delivery_boy';
                break;

            case 'adminremarksupdate':
                $rules = [
                    'id' => 'required|exists:order_return_replaces,id',
                    'adminremarks' => 'nullable',
                ];

                $permission = 'update_return_replacement_status';

                if (\Myhelper::hasNotRole(['superadmin', 'admin'])) {
                    return response()->json(['status' => "Permission Denied"], 400);
                }
                break;

            default:
                return response()->json(['status' => "Invalid Request"], 400);
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
            return response()->json(['status' => "Permission Denied"], 400);
        }

        switch ($post->operation) {
            case 'createreturnreplacementrequest':
                if (count($post->order_products) < 1) {
                    return response()->json(['status' => "Please select atleast one product to create $post->type request"], 400);
                }

                if (OrderReturnReplace::where('order_id', $post->order_id)->whereNotIn('status', ['deliveredtostore', 'rejected'])->exists()) {
                    return response()->json(['status' => "A request is already submitted and running for this order"], 400);
                }

                do {
                    $post['code'] = config('app.shortname') . '-' . rand(1111111111, 9999999999);
                } while (OrderReturnReplace::where("code", "=", $post->code)->first() instanceof OrderReturnReplace);

                $document = [
                    'order_id' => $post->order_id,
                    'type' => $post->type,
                    'code' => $post->code,
                    'status' => "initiated",
                    'delivery_charge' => \Myhelper::calculateReturnRefundDeliveryCharge($post->order_id, $post->type),
                ];

                $action = OrderReturnReplace::create($document);
                if ($action) {
                    \Myhelper::updateReturnReplaceStatusAction($action->id);

                    foreach ($post->order_products as $value) {
                        $item = [
                            'returnreplace_id' => $action->id,
                            'orderproduct_id' => $value,
                        ];

                        OrderReturnReplaceItem::create($item);
                    }
                }
                break;

            case 'statusupdate':
                $update = array();
                $update['status'] = $post->status;

                if ($update['status'] == 'accepted' && $returnreplace->type == 'replace') {
                    switch ($returnreplace->order->type) {
                        case 'restaurant':
                        case 'mart':
                            $update['expected_intransit'] = Carbon::now()->addMinutes($post->preperation_time);
                            break;

                            // case 'mart':
                            //     $update['expected_intransit'] = Carbon::parse($post->order_ready_time);
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
                }
                break;

            case 'assigndeliveryboy':
                $deliveryboy = User::findorfail($post->deliveryboy_id);

                $check = \Myhelper::checkDeliveryBoyAvailabality($deliveryboy->id, 'returnreplace', $returnreplace->id);
                if ($check->status == false) {
                    return response()->json(['status' => $check->message], 400);
                }

                $action = OrderReturnReplace::where('id', $post->id)->update(['deliveryboy_id' => $post->deliveryboy_id, 'deliveryboy_status' => 'pending']);
                if ($action) {
                    \Myhelper::updateReturnReplaceStatusAction($post->id, 'deliveryassigned');
                }
                break;

            case 'adminremarksupdate':
                $action = OrderReturnReplace::where('id', $post->id)->update(['adminremarks' => @$post->adminremarks ?? null]);
                if ($action) {
                    return response()->json(['status' => "Remarks updated successfully"], 200);
                } else {
                    return response()->json(['status' => "Remarks updated successfully"], 200);
                }
                break;

            default:
                return response()->json(['status' => "Invalid Request"], 400);
                break;
        }

        if ($action) {
            return response()->json(['status' => "Task Completed"], 200);
        } else {
            return response()->json(['status' => "Task failed. Please try again later"], 400);
        }
    }

    public function ajax(Request $post)
    {
        switch ($post->type) {
            case 'ordersearch':
                $rules = [
                    'keyword' => 'nullable',
                ];

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => $value[0]], 400);
                    }
                }

                $data['orders'] = [];
                if ($post->has('keyword') && $post->keyword) {
                    $orders = Order::with('order_products')->whereIn('status', ['delivered'])
                        ->where(function ($q) use ($post) {
                            $q->orWhere('code', 'LIKE', '%' . $post->keyword . '%');
                        })->get();

                    $data['orders'] = $orders;
                }

                return response()->json($data, 200);
                break;

            case 'returnrefund-deliveryboy-assign':
                $rules = [
                    'returnreplace_id' => 'required|exists:order_return_replaces,id',
                    'km_radius' => 'required|numeric|min:1|max:100',
                ];

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => $value[0]], 400);
                    }
                }

                $returnreplace = OrderReturnReplace::with('order')->findorfail($post->returnreplace_id);

                $avail_drivers = \Myhelper::getAvailableDrivers($returnreplace->order->cust_latitude, $returnreplace->order->cust_longitude, $returnreplace->id, $post->km_radius, false, 'returnreplace');
                $delivery_boys = User::whereIn('id', $avail_drivers)
                    ->where('online', '1')
                    ->where('status', '1')
                    ->whereHas('role', function ($q) {
                        $q->where('slug', 'deliveryboy');
                    })->get();

                $data['returnreplace'] = $returnreplace;
                $data['delivery_boys'] = $delivery_boys;
                return response()->json(['status' => 'Success', 'data' => $data], 200);
                break;

            case 'calculate-delivery-charge':
                $rules = [
                    'order_id' => 'required|exists:orders,id',
                    'mode' => 'required|in:return,replace',
                ];

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => $value[0]], 400);
                    }
                }

                $data['delivery_charge'] = \Myhelper::calculateReturnRefundDeliveryCharge($post->order_id, $post->mode);
                return response()->json(['status' => 'Success', 'data' => $data], 200);
                break;

            default:
                return response()->json(['status' => 'Unsupported Request'], 400);
                break;
        }
    }
}
