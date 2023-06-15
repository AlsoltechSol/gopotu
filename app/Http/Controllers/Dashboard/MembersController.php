<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Model\Role;
use App\Model\Permission;
use App\Model\UserPermission;
use App\Model\DefaultPermission;
use App\Model\Scheme;
use App\Model\Shop;
use App\User;
use GuzzleHttp\Client;

class MembersController extends Controller
{
    public function index($type)
    {
        $data['activemenu']['main'] = 'members';

        $role = Role::query();

        switch ($type) {
            case 'user':
                $data['activemenu']['sub'] = 'user';
                $permission = 'view_user';

                $role->where('slug', 'user');
                break;

            case 'deliveryboy':
                $data['activemenu']['sub'] = 'deliveryboy';
                $permission = 'view_deliveryboy';

                $role->where('slug', 'deliveryboy');
                break;

            case 'admin':
                $data['activemenu']['sub'] = 'admin';
                $permission = 'view_admin';

                $role->where('slug', 'admin');
                break;

            case 'branch':
                $data['activemenu']['sub'] = 'branch';
                $permission = 'view_branch';

                $role->where('slug', 'branch');
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        $data['role'] = $role->first();
        $data['type'] = $type;

        return view('dashboard.members.index', $data);
    }

    public function add($type)
    {
        $data['activemenu']['main'] = 'members';

        $role = Role::query();

        switch ($type) {
            case 'user':
                $data['activemenu']['sub'] = 'user';
                $permission = 'add_user';
                $view = 'adduser';

                $role->where('slug', 'user');
                break;

            case 'deliveryboy':
                $data['activemenu']['sub'] = 'deliveryboy';
                $permission = 'add_deliveryboy';
                $view = 'adddeliveryboy';

                $role->where('slug', 'deliveryboy');
                break;

            case 'admin':
                $data['activemenu']['sub'] = 'admin';
                $permission = 'add_admin';
                $view = 'addadmin';

                $role->where('slug', 'admin');
                break;

            case 'branch':
                $data['activemenu']['sub'] = 'branch';
                $permission = 'add_branch';
                $view = 'addbranch';

                $data['schemes'] = Scheme::all();

                $role->where('slug', 'branch');
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        $data['role'] = $role->first();
        $data['type'] = $type;

        return view('dashboard.members.' . $view, $data);
    }

    public function create($type, Request $post)
    {
        switch ($type) {
            case 'admin':
                $rules = array(
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'mobile' => 'required|digits:10|unique:users',
                );
                break;

            case 'deliveryboy':
                $rules = array(
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'mobile' => 'required|digits:10|unique:users',
                );
                break;

            case 'user':
                $rules = array(
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'mobile' => 'required|digits:10|unique:users',
                );
                break;

            case 'branch':
                $rules = array(
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'mobile' => 'required|digits:10|unique:users',
                    //'scheme_id' => 'required|exists:schemes,id',
                    'business_category' => 'required|in:mart,restaurant',
                );
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 404);
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

        // $password = strstr($post->email, '@', true);
        $password = rand(11111111, 99999999);
        $post['password'] = bcrypt($password);

        $role = Role::where('slug', $type)->first();
        $post['role_id'] = $role->id;

        if (in_array($role->slug, ['user'])) {
            do {
                $post['referral_code'] = config('app.shortname') . '-' . rand(11111111, 99999999);
            } while (User::where("referral_code", "=", $post->referral_code)->first() instanceof User);
        }

        $otp_rand = rand(1000, 9999);
        $client = new Client();

        $response = $client->get('smsapi.syscogen.com/rest/services/sendSMS/sendGroupSms?AUTH_KEY=2946464c2021d8e0b1277bed83cd9f&message='.$otp_rand.'&senderId=DEMOOS&routeId=1&mobileNos='.$post->mobile.'&smsContentType=english&entityid=1001238677144196147&tmid=140200000022&templateid=NoneedIfAddedInPanel');
        $data = $post->all();
        $data['otp'] = $otp_rand;
        $action = User::create($data);
        if ($action) {
            $content = 'Hey ' . $post->name . ', welcome to the GoPotu family. Your account has been created successfully by our Administrator Team. Use ' . $post->email . ' and ' . $password . ' as the email and password respectively to sign in to your account. Thanks, GoPotu Team';
            \Myhelper::sms($post->mobile, $content);

            $permissions = DefaultPermission::where('role_id', $role->id)->get();
            foreach ($permissions as $key => $value) {
                UserPermission::insert([
                    'user_id' => $action->id,
                    'permission_id' => $value->permission_id
                ]);
            }

            $data['credentials'] = (object)[
                'email' => $post->email,
                'mobile' => $post->mobile,
                'password' => $password,
            ];

            return response()->json(['status' => 'User created successfully.', 'data' => $data], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again later.'], 400);
        }
    }

    public function changeaction(Request $post)
    {
        switch ($post->role) {
            case 'admin':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_admin';
                break;

            case 'branch':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_branch';
                break;

            case 'user':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_user';
                break;

            case 'deliveryboy':
                $rules = array(
                    'id' => 'required',
                );

                $permission = 'edit_deliveryboy';
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

        switch ($post->role) {
            case 'admin':
            case 'branch':
            case 'user':
            case 'deliveryboy':
                $user = User::findorfail($post->id);
                if ($user->status) {
                    $post['status'] = '0';
                } else {
                    $post['status'] = '1';
                }

                $action = User::updateorcreate(['id' => $post->id], $post->all());
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again later'], 400);
        }
    }

    public function permission($id)
    {
        $id = base64_decode($id);

        $user = User::findorfail($id);

        $data['activemenu'] = array(
            'main' => 'members',
            'sub' => $user->role->slug,
        );

        if (!\Myhelper::can('edit_' . $user->role->slug)) {
            abort(401);
        }

        $data['default'] = UserPermission::where('user_id', $user->id)->pluck('permission_id')->toArray();

        // $arr = Permission::select('type')->distinct()->get()->pluck('type');
        $arr = ["members", "stores", "master", "mart", "restaurant", "orders", "return_replacement", "funds", "settings", "reports", "coupon", "resources", "cms", "notification"];

        if ($user->role->slug == 'branch') {
            $ch_arr = ["mart", "restaurant"];
            foreach ($ch_arr as $key => $value) {
                if ($user->business_category != $value) {
                    array_splice($arr, (array_search($value, $arr)), 1);
                }
            }
        }

        foreach ($arr as $key => $value) {
            $data['permissions'][$value] = Permission::where('type', $value)->where('role_id', 'LIKE', '%"' . $user->role_id . '"%')->get();
        }

        $data['user'] = $user;
        return view('dashboard.members.permissions', $data);
    }

    public function permissionsubmit(Request $post)
    {
        $rules = array(
            'user_id' => 'required',
        );

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        $oldatas = UserPermission::where('user_id', $post->user_id)->delete();

        if ($post->permissions != '') {
            foreach ($post->permissions as $permission_id) {
                UserPermission::insert([
                    'user_id' => $post->user_id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        return response()->json(['status' => 'Task successfullly completed'], 200);
    }

    public function verifyOtp(Request $request){
        $user = User::where('mobile', $request->mobile)->first();
        $otp = $user->otp;

        if ($otp == $request->otp){
           
            $user->otp_verified_status = '1';
            $user->save();
           
           // dd($user);
            return response()->json([
                'status' => 200,
                'message' => 'Otp validated successfully'
                
            ]);
        }else{
            return response()->json([
                'status' => 401,
                'message' => 'Otp validation failed'
            ]);
        }
    }
}
