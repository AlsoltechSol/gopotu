<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\User;
use App\Model\Role;
use App\Model\UserNotification;

class NotificationsController extends Controller
{
    public function index($type)
    {
        $data['activemenu']['main'] = 'notifications';

        $data['roles'] = Role::whereIn('slug', ['user'])->get();

        switch ($type) {
            case 'account':
                $data['activemenu']['sub'] = 'account';
                $permission = 'account_notification';

                $type = 'account';
                break;

            case 'sms':
                $data['activemenu']['sub'] = 'sms';
                $permission = 'sms_notification';

                $type = 'sms';
                break;

            case 'push':
                $data['activemenu']['sub'] = 'push';
                $permission = 'push_notification';

                $type = 'push';
                break;

            case 'email':
                $data['activemenu']['sub'] = 'email';
                $permission = 'email_notification';

                $type = 'email';
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        $data['type'] = $type;

        return view('dashboard.notifications.index', $data);
    }

    public function submit(Request $post)
    {
        switch ($post->type) {
            case 'account':
                $rules = array(
                    'user_id.*' => 'required',
                    'heading' => 'required|max:255',
                    'body' => 'required|max:255',
                );

                $permission = 'account_notification';
                break;

            case 'sms':
                $rules = array(
                    'user_id.*' => 'required',
                    'body' => 'required|max:255',
                );

                $permission = 'sms_notification';
                break;

            case 'email':
                $rules = array(
                    'user_id.*' => 'required',
                    'heading' => 'required|max:255',
                    'body' => 'required|max:255',
                );

                $permission = 'email_notification';
                break;

            case 'push':
                $rules = array(
                    'user_id.*' => 'required',
                    'heading' => 'required|max:255',
                    'body' => 'required|max:255',
                );

                $permission = 'push_notification';
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => 'Access Denied'], 401);
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
            case 'account':
                if ($post->user_id[0] == 'all') {
                    $post['user_id'] = 'all';
                } else {
                    $post['user_id'] = json_encode($post->user_id);
                }

                $action = UserNotification::create($post->all());
                if ($action) {
                    return response()->json(['status' => 'Notification sent successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again.'], 200);
                }
                break;

            case 'sms':
                if ($post->user_id[0] == 'all') {
                    $users = User::all()->pluck(['mobile']);
                    $post['user_id'] = 'all';
                } else {
                    $users = User::whereIn('id', $post->user_id)->get()->pluck(['mobile']);
                    $post['user_id'] = json_encode($post->user_id);
                }

                if (\Myhelper::sms($users, $post->body)) {
                    $action = UserNotification::create($post->all());
                    if ($action) {
                        return response()->json(['status' => 'Notification sent successfully'], 200);
                    } else {
                        return response()->json(['status' => 'Task failed. Please try again.'], 200);
                    }
                } else {
                    return response()->json(['status' => 'SMS cannot be sent.'], 200);
                }
                break;

            case 'push':
                if ($post->user_id[0] == 'all') {
                    $post['user_id'] = 'all';
                } else {
                    $post['user_id'] = $post->user_id;

                    foreach ($post->user_id as $key => $value) {
                        $action = \Myhelper::sendNotification($value, $post->heading, $post->body);
                    }
                }

                if ($action) {
                    return response()->json(['status' => 'Notification sent successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again.'], 200);
                }
                break;

            case 'email':
                if ($post->user_id[0] == 'all') {
                    $post['user_id'] = 'all';
                } else {
                    $post['user_id'] = json_encode($post->user_id);
                }

                $action = UserNotification::create($post->all());
                if ($action) {
                    return response()->json(['status' => 'Notification sent successfully'], 200);
                } else {
                    return response()->json(['status' => 'Task failed. Please try again.'], 200);
                }
                break;

            default:
                # code...
                break;
        }
    }
}
