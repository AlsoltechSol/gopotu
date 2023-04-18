<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\SupportTicket;

class ReportsController extends Controller
{
    public function index($type)
    {
        $data['activemenu'] = [
            'main' => 'reports',
            'sub' => $type,
        ];

        switch ($type) {
            case 'branchwallet':
                $view = 'walletstatement';
                $data['wallet_type'] = 'branchwallet';

                if (\Myhelper::hasRole(['branch'])) {
                    $permission = 'branchwallet_statement';
                    $data['user'] = \Auth::user();
                    $data['heading'] = 'Main Wallet Statement';

                    $data['activemenu'] = [
                        'main' => 'funds',
                        'sub' => 'branchwallet-statement',
                    ];
                } else {
                    abort(401);
                }
                break;

            case 'supporttickets':
                $permission = 'view_support_tickets';
                $view = 'supporttickets';
                break;

            default:
                abort(404);
                break;
        }

        return view('dashboard.reports.' . $view, $data);
    }

    public function update(Request $post)
    {
        switch ($post->type) {
            case 'ticketstatus':
                $rules = [
                    'id' => 'required|exists:support_tickets',
                    'status' => 'required|in:pending,progress,resolved,rejected',
                ];

                $permission = 'update_support_ticket_status';
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
            case 'ticketstatus':
                $update = array();
                $update['status'] = $post->status;

                $action = SupportTicket::where('id', $post->id)->update($update);
                if ($action) {
                    $ticket = SupportTicket::find($post->id);

                    if ($ticket->mobile) {
                        $status_text = "";
                        switch ($ticket->status) {
                            case 'progress':
                                $status_text = 'accepted';
                                break;

                            case 'resolved':
                                $status_text = 'resolved';
                                break;

                            case 'rejected':
                                $status_text = 'rejected';
                                break;

                            default:
                                $status_text = $ticket->status;
                                break;
                        }

                        $content = "Hello " . $ticket->name . ", your Support Ticket ID - " . $ticket->code . " has been " . $status_text . " by our support team. Team GoPotu";
                        \Myhelper::sms($ticket->mobile, $content);
                    }
                }
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task completed successfully'], 200);
        } else {
            return response()->json(['status' => 'Task failed! Please try again later'], 400);
        }
    }
}
