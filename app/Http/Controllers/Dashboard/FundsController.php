<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Role;
use App\Model\WalletReport;
use App\Model\WalletRequest;
use App\User;

class FundsController extends Controller
{
    public function index($type)
    {
        $data['activemenu'] = [
            'main' => 'funds',
            'sub' => $type,
        ];

        switch ($type) {
            case 'tr-user':
            case 'tr-branch':
            case 'tr-deliveryboy':
                $view = 'tr';
                $permission = 'fund_tr_action';

                if (\Myhelper::hasNotRole(['superadmin', 'admin'])) {
                    abort(401);
                }

                if ($type == "tr-user") {
                    $data['role'] = Role::where('slug', 'user')->first();
                } else if ($type == "tr-branch") {
                    $data['role'] = Role::where('slug', 'branch')->first();
                } else if ($type == "tr-deliveryboy") {
                    $data['role'] = Role::where('slug', 'deliveryboy')->first();
                } else {
                    abort(404);
                }
                break;

            case 'payoutrequest':
                $view = 'payoutrequest';
                $permission = 'view_payoutrequest';
                break;

            case 'collectionsubmitted':
                $view = 'collectionsubmitted';
                $permission = 'view_collectionsubmitted';
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.funds.' . $view, $data);
    }

    public function submit(Request $post)
    {
        switch ($post->type) {
            case 'fundtransfer':
            case 'fundreturn':
                if (\Myhelper::hasNotRole(['superadmin', 'admin'])) {
                    return response()->json(['status' => 'Permission denied'], 400);
                }

                $permission = 'fund_tr_action';

                $rules = [
                    'user_id' => 'required|exists:users,id',
                    'wallet_type' => 'required|in:branchwallet,riderwallet,creditwallet,userwallet',
                    'amount' => 'required|numeric|min:1',
                    'remarks' => 'required|max:255',
                ];
                break;

            case 'submitpayoutrequest':
                if (\Myhelper::hasNotRole(['branch', 'deliveryboy'])) {
                    return response()->json(['status' => 'Permission denied'], 400);
                }

                $permission = 'view_payoutrequest';

                $rules = [
                    'wallet_type' => 'required|in:branchwallet',
                    'amount' => 'required|numeric|min:1',
                    'remarks' => 'required|max:255',
                ];
                break;

            case 'editpayoutrequeststatus':
                if (\Myhelper::hasNotRole(['superadmin', 'admin'])) {
                    return response()->json(['status' => 'Permission denied'], 400);
                }

                $permission = 'edit_payoutrequest_status';

                $rules = [
                    'request_id' => 'required|exists:wallet_requests,id',
                    'status' => 'required|in:approved,rejected',
                    'adminremarks' => 'nullable|max:255',
                ];
                break;

            case 'editcollectionsubmittedstatus':
                if (\Myhelper::hasNotRole(['superadmin', 'admin'])) {
                    return response()->json(['status' => 'Permission denied'], 400);
                }

                $permission = 'edit_collectionsubmitted_status';

                $rules = [
                    'request_id' => 'required|exists:wallet_requests,id',
                    'status' => 'required|in:approved,rejected',
                    'adminremarks' => 'nullable|max:255',
                ];
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => 'Permission not allowed.'], 400);
        }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        switch ($post->type) {
            case 'fundtransfer':
            case 'fundreturn':
                $user = User::findorfail($post->user_id);

                switch ($user->role->slug) {
                    case 'user':
                        if (!in_array($post->wallet_type, ['userwallet'])) {
                            return response()->json(['status' => 'Invalid wallet selected'], 400);
                        }
                        break;

                    case 'branch':
                        if (!in_array($post->wallet_type, ['branchwallet'])) {
                            return response()->json(['status' => 'Invalid wallet selected'], 400);
                        }
                        break;

                    case 'deliveryboy':
                        if (!in_array($post->wallet_type, ['riderwallet', 'creditwallet'])) {
                            return response()->json(['status' => 'Invalid wallet selected'], 400);
                        }
                        break;

                    default:
                        return response()->json(['status' => 'Operation for selected user is not allowed'], 400);
                        break;
                }

                $balance = $user[$post->wallet_type];

                if ($post->type == 'fundtransfer') {
                    $trans_type = 'credit';
                } else {
                    $trans_type = 'debit';

                    if ($post->amount > $balance) {
                        return response()->json(['status' => 'Insufficient wallet balance on user wallet'], 400);
                    }
                }

                $report = array(
                    'user_id' => $user->id,
                    'ref_id' => null,
                    'wallet_type' => $post->wallet_type,
                    'balance' => $balance,
                    'trans_type' => $trans_type,
                    'amount' => $post->amount,
                    'remarks' => $post->remarks,
                    'service' => $post->type,
                );

                if ($trans_type == 'credit') {
                    $transaction = User::where('id', $user->id)->increment($post->wallet_type, $post->amount);
                } else {
                    $transaction = User::where('id', $user->id)->decrement($post->wallet_type, $post->amount);
                }

                if ($transaction) {
                    $action = WalletReport::create($report);
                    if ($action) {
                        return response()->json(['status' => 'Transaction completed successfully.'], 200);
                    } else {
                        return response()->json(['status' => 'Transaction report cannot be generated.'], 400);
                    }
                } else {
                    return response()->json(['status' => 'Transaction cannot be completed.'], 400);
                }

                break;

            case 'submitpayoutrequest':
                $user = User::findorfail(\Auth::id());

                $exist = WalletRequest::where('type', 'payout')->whereIn('status', ['pending'])->exists();
                if ($exist) {
                    return response()->json(['status' => 'A payout request has already submitted. Please wait for admin\'s action'], 400);
                }

                switch ($user->role->slug) {
                    case 'branch':
                        if (!in_array($post->wallet_type, ['branchwallet'])) {
                            return response()->json(['status' => 'Invalid wallet selected'], 400);
                        }
                        break;

                    case 'deliveryboy':
                        if (!in_array($post->wallet_type, ['riderwallet', 'creditwallet'])) {
                            return response()->json(['status' => 'Invalid wallet selected'], 400);
                        }
                        break;

                    default:
                        return response()->json(['status' => 'Operation for selected user is not allowed'], 400);
                        break;
                }

                $balance = $user[$post->wallet_type];
                if ($post->amount > $balance) {
                    return response()->json(['status' => 'Insufficient wallet balance in your wallet'], 400);
                }

                do {
                    $post['code'] = config('app.shortname') . '-' . rand(1111111111, 9999999999);
                } while (WalletRequest::where("code", "=", $post->code)->first() instanceof WalletRequest);

                $insert = array(
                    'user_id' => $user->id,
                    'code' => $post->code,
                    'wallet_type' => $post->wallet_type,
                    'amount' => $post->amount,
                    'remarks' => $post->remarks,
                    'status' => 'pending',
                    'adminremarks' => null,
                    'type' => 'payout',
                );

                $action = WalletRequest::create($insert);
                if ($action) {
                    return response()->json(['status' => 'Request sent to the administrative team successfully.'], 200);
                } else {
                    return response()->json(['status' => 'Request cannot be sent.'], 400);
                }

                break;

            case 'editpayoutrequeststatus':
            case 'editcollectionsubmittedstatus':
                $wallet_request = WalletRequest::findorfail($post->request_id);

                if (!in_array($wallet_request->status, ['pending'])) {
                    return response()->json(['status' => 'The request cannot be updated'], 400);
                }

                $update['status'] = $post->status;
                $update['adminremarks'] = $post->adminremarks;

                if ($post->status == 'approved') {
                    $user = User::findorfail($wallet_request->user_id);

                    if ($wallet_request->amount > $user[$wallet_request->wallet_type]) {
                        return response()->json(['status' => 'Insufficient wallet balance on user\'s wallet'], 400);
                    }

                    $report = array(
                        'user_id' => $user->id,
                        'ref_id' => $wallet_request->id,
                        'wallet_type' => $wallet_request->wallet_type,
                        'balance' => $user[$wallet_request->wallet_type],
                        'trans_type' => 'debit',
                        'amount' => $wallet_request->amount,
                        'remarks' => 'Payout Request ' . $wallet_request->code . ' has been accepted and dispatched to your Bank Account',
                        'service' => 'request',
                    );

                    $exist = WalletReport::where('ref_id', $wallet_request->id)->where('service', 'request')->where('user_id', $user->id)->exists();
                    if (!$exist) {
                        $transaction = User::where('id', $user->id)->decrement($wallet_request->wallet_type, $wallet_request->amount);
                        if ($transaction) {
                            WalletReport::create($report);
                        } else {
                            return response()->json(['status' => 'Transaction failed. Please try again later'], 400);
                        }
                    }
                }

                $action = WalletRequest::where('id', $post->request_id)->update($update);
                if ($action) {
                    return response()->json(['status' => 'Payout request ' . $post->status . ' successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again later'], 400);
                }
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
                break;
        }
    }
}
