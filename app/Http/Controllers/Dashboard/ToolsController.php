<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Role;
use App\Model\Permission;
use App\Model\DefaultPermission;

class ToolsController extends Controller
{
    public function roles()
    {
        $data['activemenu'] = array(
            'main' => 'tools',
            'sub' => 'roles',
        );

        $data['roles'] = Role::all();

        return view('dashboard.tools.roles', $data);
    }

    public function submitrole(Request $post)
    {
        switch ($post->type) {
            case 'edit':
                $rules = array(
                    'id' => 'required',
                    'name' => 'required',
                );
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

        switch ($post->type) {
            case 'edit':
                $action = Role::updateorcreate(['id' => $post->id], $post->all());
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task completed successfully'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again'], 400);
        }
    }

    public function permissions()
    {
        $data['activemenu'] = array(
            'main' => 'tools',
            'sub' => 'permissions',
        );

        $data['permissions'] = Permission::all();
        $data['roles'] = Role::whereIn('slug', ['admin', 'deliveryboy', 'branch'])->get();

        return view('dashboard.tools.permissions', $data);
    }

    public function submitpermission(Request $post)
    {
        $post['slug'] = strtolower(str_replace(' ', '_', $post->slug));
        $post['type'] = strtolower($post->type);

        switch ($post->operation) {
            case 'new':
                $rules = array(
                    'name' => 'required',
                    'slug' => 'required|unique:permissions',
                    'type' => 'required',
                    'role_id' => 'required',
                );
                break;

            case 'edit':
                $rules = array(
                    'id' => 'required',
                    'name' => 'required',
                    'slug' => 'required|unique:permissions,slug,' . $post->id,
                    'type' => 'required',
                    'role_id' => 'required',
                );
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

        switch ($post->operation) {
            case 'new':
            case 'edit':
                if ($post->has('role_id')) {
                    $post['role_id'] = json_encode($post->role_id);
                }

                $action = Permission::updateorcreate(['id' => $post->id], $post->all());
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task completed successfully'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again'], 400);
        }
    }

    public function rolepermissions($role_id)
    {
        $data['activemenu'] = array(
            'main' => 'tools',
            'sub' => 'roles',
        );

        $data['role'] = Role::findorfail($role_id);
        $data['default'] = DefaultPermission::where('role_id', $role_id)->pluck('permission_id')->toArray();

        // $arr = Permission::select('type')->distinct()->get()->pluck('type');
        $arr = ["members", "stores", "master", "mart", "restaurant", "orders", "return_replacement", "funds", "settings", "reports", "coupon", "resources", "cms", "notification"];
        foreach ($arr as $key => $value) {
            $data['permissions'][$value] = Permission::where('type', $value)->where('role_id', 'LIKE', '%"' . $role_id . '"%')->get();
        }

        return view('dashboard.tools.rolepermissions', $data);
    }

    public function rolepermissionssubmit(Request $post)
    {
        $rules = array(
            'role_id' => 'required',
        );

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        $oldatas = DefaultPermission::where('role_id', $post->role_id)->delete();

        foreach ($post->permissions as $permission_id) {
            DefaultPermission::insert([
                'role_id' => $post->role_id,
                'permission_id' => $permission_id,
            ]);
        }

        return response()->json(['status' => 'Task completed successfully'], 200);
    }
}
