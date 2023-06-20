<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Shop;
use App\User;
use Carbon\Carbon;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $data['activemenu'] = array(
            'main' => 'stores'
        );

        // if (!\Myhelper::can('view_store')) {
        //     abort(401);
        // }



        return view('dashboard.stores.index', $data);
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'changestatus':
            case 'changefeatured':
                $rules = [
                    'id' => 'required|exists:shops',
                ];

                $permission = 'edit_store';
                break;

            default:
                return response()->json(['status' => 'Invalid Request'], 400);
                break;
        }

        // if (isset($permission) && !\Myhelper::can($permission)) {
        //     return response()->json(['status' => 'Permission not allowed.'], 400);
        // }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        switch ($post->operation) {
            case 'changestatus':
                $shop = Shop::findorfail($post->id);
                if ($shop->status == '1') {
                    $shop->status = '0';
                } else {
                    $shop->status = '1';
                }

                $action = $shop->save();
                break;

            case 'changefeatured':
                $shop = Shop::findorfail($post->id);
                if ($shop->is_featured == '1') {
                    $shop->is_featured = '0';
                } else {
                    $shop->is_featured = '1';
                }

                $action = $shop->save();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }
}
