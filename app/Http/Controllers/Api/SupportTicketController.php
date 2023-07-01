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
use App\Model\OrderReturnReplace;
use App\Model\SupportTicketSubject;
use App\Model\SupportTicket;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function fetchSubjects(Request $request)
    {
        $data = array();
        try {
            $data['subjects'] = SupportTicketSubject::all();

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function submit(Request $request)
    {
        $data = array();
        try {
            if (\Auth::guard('api')->check()) {
                $request['user_id'] = \Auth::guard('api')->id();
            }

            $rules = array(
                'user_id' => 'nullable|exists:users,id',
                'name' => 'required',
                'mobile' => 'required|digits:10',
                'alternate_mobile' => 'nullable|digits:10',
                'email' => 'required|email',
                'subject' => 'required|exists:support_ticket_subjects,type',
                'order_code' => 'nullable|exists:orders,code',
                'message' => 'nullable',
            );

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }


            switch ($request->subject) {
                case 'replaceorder':
                    if (!$request->order_code) {
                        return response()->json(['status' => 'error', 'message' => "Order ID is required for replacement request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if (!$request->message) {
                        return response()->json(['status' => 'error', 'message' => "Replacement reason is required for replacement request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                    break;

                case 'returnorder':
                    if (!$request->order_code) {
                        return response()->json(['status' => 'error', 'message' => "Order ID is required for return request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if (!$request->message) {
                        return response()->json(['status' => 'error', 'message' => "Return reason is required for return request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                    break;

                case 'cancelorder':
                    if (!$request->order_code) {
                        return response()->json(['status' => 'error', 'message' => "Order ID is required for cancellation request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }

                    if (!$request->message) {
                        return response()->json(['status' => 'error', 'message' => "Cancellation reason is required for cancellation request", 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                    break;

                default:
                    # code...
                    break;
            }

            $subject = SupportTicketSubject::where('type', $request->subject)->first();
            $ticket_code = config('app.shortname') . '-' . rand(11111111, 99999999);

            $document = array();
            $document['code'] = $ticket_code;
            $document['type'] = $request->subject;
            $document['user_id'] = $request->user_id;
            $document['name'] = $request->name;
            $document['mobile'] = $request->mobile;
            $document['alternate_mobile'] = $request->alternate_mobile;
            $document['email'] = $request->email;
            $document['subject'] = $subject->name;
            $document['order_code'] = $request->order_code;
            $document['message'] = $request->message;

            $document2 = array();
            $order = Order::where('code', $request->order_code)->first();
           // return $order;

            $document2['order_id'] = $order->id;
            $document2['code'] = $order->code;
            $document2['status'] = 'accepted';
            $document2['delivery_charge'] = $order->delivery_charge;
            $document2['deliveryboy_id'] = $order->deliveryboy_id;
            $document2['deliveryboy_id'] = $order->deliveryboy_id;


            $action = SupportTicket::create($document);
            OrderReturnReplace::create($document2);
            if ($action ) {
                $data['ticket'] = SupportTicket::find($action->id);
               
                return response()->json(['status' => 'success', 'message' => 'Request sent successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Oops!! Someting went wrong. Please try again later', 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
