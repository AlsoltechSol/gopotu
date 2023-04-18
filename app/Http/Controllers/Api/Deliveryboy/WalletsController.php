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
use App\Model\Order;
use App\Model\WalletReport;
use App\Model\WalletRequest;
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
                case 'riderwallet':
                case 'creditwallet':
                    $rules = [
                        'page' => 'nullable|numeric',
                        'service' => 'nullable|in:order,payout',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date|after_or_equal:start_date',
                    ];

                    $request['wallet_type'] = $type;
                    break;

                case 'earningreport':
                    $rules = [
                        'page' => 'nullable|numeric',
                        'start_date' => 'required|date',
                        'end_date' => 'required|date|after_or_equal:start_date',
                    ];

                    $request['service'] = 'order';
                    $request['wallet_type'] = 'riderwallet';
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

            $request['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d') . " 00:00:00";
            $request['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d') . " 23:59:59";

            $query = WalletReport::where('user_id', \Auth::guard('api')->id())
                ->where('wallet_type', $request->wallet_type)
                ->whereBetween('created_at', [$request->start_date, $request->end_date]);

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

            switch ($type) {
                case 'earningreport':
                    foreach ($statement as $item) {
                        if ($item->service == 'order' && $item->ref_id) {
                            $order = Order::select('code', 'type')->find($item->ref_id);

                            $item->order_code = $order->code;
                            $item->order_type = $order->type;
                        }
                    }

                    $arr = ['today', 'currentmonth', 'total'];
                    foreach ($arr as $value) {
                        $credit = WalletReport::where('wallet_type', 'riderwallet')
                            ->where('trans_type', "credit")
                            ->where('user_id', \Auth::id())
                            ->where('service', "order");

                        $debit = WalletReport::where('wallet_type', 'riderwallet')
                            ->where('trans_type', "debit")
                            ->where('user_id', \Auth::id())
                            ->where('service', "order");


                        switch ($value) {
                            case 'today':
                                $credit->whereDate('created_at', Carbon::now());
                                $debit->whereDate('created_at', Carbon::now());
                                break;

                            case 'currentmonth':
                                $credit->whereMonth('created_at', Carbon::now()->format('m'));
                                $debit->whereMonth('created_at', Carbon::now()->format('m'));
                                break;
                        }

                        $data['earning'][$value] = $credit->sum('amount') - $debit->sum('amount');
                    }
                    break;
            }

            $data['statement'] = $statement;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchRequests($type, Request $request)
    {
        $data = array();
        try {
            switch ($type) {
                case 'payout':
                    $rules = [
                        'page' => 'nullable|numeric',
                    ];

                    $request['wallet_type'] = 'riderwallet';
                    break;

                case 'deposit':
                    $rules = [
                        'page' => 'nullable|numeric',
                    ];

                    $request['wallet_type'] = 'creditwallet';
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

            $query = WalletRequest::where('user_id', \Auth::guard('api')->id())->where('wallet_type', $request->wallet_type);

            $query->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $query->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $orders = $query->skip($skip)->take(config('app.pagination_records'));
            }

            $data['requests'] = $query->get();
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function submitRequest($type, Request $request)
    {
        $data = array();
        try {
            switch ($type) {
                case 'payout':
                case 'deposit':
                    $rules = [
                        'amount' => 'required|numeric|min:1',
                        'remarks' => 'required|max:255',
                        'transaction_copy' => 'nullable|mimes:jpeg,jpg,png,gif',
                    ];
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
                case 'payout':
                case 'deposit':
                    if ($type == 'payout') {
                        $request['wallet_type'] = 'riderwallet';
                    } elseif ($type == 'deposit') {
                        $request['wallet_type'] = 'creditwallet';
                    } else {
                        abort(500);
                    }

                    $user = User::findorfail(\Auth::guard('api')->id());

                    $exist = WalletRequest::where('user_id', $user->id)->where('type', 'payout')->where('wallet_type', $request->wallet_type)->whereIn('status', ['pending'])->exists();
                    if ($exist) {
                        return response()->json(['status' => 'error', 'message' => 'A payout request has already been submitted. Please wait for admin\'s action', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    $balance = $user[$request->wallet_type];
                    if ($request->amount > $balance) {
                        return response()->json(['status' => 'error', 'message' => 'Insufficient Wallet Balance', 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    do {
                        $request['code'] = config('app.shortname') . '-' . rand(1111111111, 9999999999);
                    } while (WalletRequest::where("code", "=", $request->code)->first() instanceof WalletRequest);

                    $insert = array(
                        'user_id' => $user->id,
                        'code' => $request->code,
                        'wallet_type' => $request->wallet_type,
                        'amount' => $request->amount,
                        'remarks' => $request->remarks,
                        'status' => 'pending',
                        'adminremarks' => null,
                        'type' => 'payout',
                    );

                    if ($request->file('transaction_copy')) {
                        $file = $request->file('transaction_copy');
                        $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                        if (\Image::make($file->getRealPath())->save('uploads/wallet/' . $filename, 60)) {
                            $insert['transaction_copy'] = $filename;

                            if (isset($deletefile)) {
                                \File::delete($deletefile);
                            }
                        } else {
                            return response()->json(['status' => 'error', 'message' => 'File cannot be saved to server.', 'data' => \Myhelper::formatApiResponseData($data)]);
                        }
                    }

                    $action = WalletRequest::create($insert);
                    if ($action) {
                        $data['wallet_request'] = WalletRequest::findorfail($action->id);
                    }
                    break;
            }

            if ($action) {
                return response()->json(['status' => 'success', 'message' => 'Wallet request sent successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Please try again later', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
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
                case 'riderwallet':
                    $data['balance'] = $userdata[$type];
                    $data['withdrawn'] = WalletRequest::where('user_id', $userdata->id)->whereIn('status', ['approved'])->where('wallet_type', $type)->sum('amount');

                    $data['driver_bankdetails'] = $userdata->bankdetails;
                    break;

                case 'creditwallet':
                    $data['balance'] = $userdata[$type];
                    $data['submitted'] = WalletRequest::where('user_id', $userdata->id)->whereIn('status', ['approved'])->where('wallet_type', $type)->sum('amount');

                    $data['company_bankdetails'] = [
                        'accno' => config('fundbank.accno'),
                        'ifsccode' => config('fundbank.ifsccode'),
                        'accholder' => config('fundbank.accholder'),
                        'bankname' => config('fundbank.bankname'),
                    ];

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
