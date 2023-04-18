<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

use App\Model\Brand;
use App\Model\Category;
use App\Model\Color;
use App\Model\Coupon;
use App\Model\ProductAttribute;
use App\Model\ProductAttributeVariant;
use App\User;

class CouponsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($type, $value = "none")
    {
        $data['activemenu'] = [
            'main' => 'coupon'
        ];

        if (!\Myhelper::can('view_coupon')) {
            abort(401);
        }

        $data['users'] = User::select('id', 'name')->whereHas('role', function ($q) {
            $q->where('slug', 'user');
        })->get()->pluck('name', 'id');

        return view('dashboard.coupon.index', $data);
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'new':
                $rules = [
                    'code' => 'required|unique:coupons',
                    'rewarded' => 'required|in:instant,walletafterdelivery',
                    'description' => 'required',
                    'type' => 'required|in:flat,percentage',
                    'value' => 'required|numeric|min:0',
                    'max_discount' => 'nullable|numeric|min:0',
                    'min_order' => 'nullable|numeric|min:0',
                    'max_usages' => 'nullable|numeric|min:0',
                    'valid_till' => 'nullable|date',
                ];

                $permission = 'add_coupon';
                break;

            case 'edit':
                $rules = [
                    'id' => 'required|exists:coupons',
                    'rewarded' => 'required|in:instant,walletafterdelivery',
                    'code' => 'required|unique:coupons,id,' . $post->id,
                    'description' => 'required',
                    'type' => 'required|in:flat,percentage',
                    'value' => 'required|numeric|min:0',
                    'max_discount' => 'nullable|numeric|min:0',
                    'min_order' => 'nullable|numeric|min:0',
                    'max_usages' => 'nullable|numeric|min:0',
                    'valid_till' => 'nullable|date',
                ];

                $permission = 'add_brand';
                break;

            case 'changestatus':
                $rules = [
                    'id' => 'required|exists:brands',
                ];

                $permission = 'edit_brand';
                break;

            case 'delete':
                $rules = [
                    'id' => 'required|exists:brands',
                ];

                $permission = 'delete_brand';
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

        switch ($post->operation) {
            case 'new':
            case 'edit':
                $document = array();
                $document['code'] = $post->code;
                $document['rewarded'] = $post->rewarded;
                $document['description'] = $post->description;
                $document['type'] = $post->type;
                $document['value'] = $post->value;
                $document['max_discount'] = $post->max_discount;
                $document['min_order'] = $post->min_order;
                $document['max_usages'] = $post->max_usages;
                $document['valid_till'] = ($post->valid_till != null) ? Carbon::parse($post->valid_till) : null;

                if ($post->has('applied_for_users') && $post->applied_for_users != null) {
                    $document['applied_for_users'] = json_encode($post->applied_for_users);
                } else {
                    $document['applied_for_users'] = null;
                }

                $action = Coupon::updateorcreate(['id' => $post->id], $document);
                break;

            case 'changestatus':
                $coupon = Coupon::findorfail($post->id);
                if ($coupon->status == '1') {
                    $coupon->status = '0';
                } else {
                    $coupon->status = '1';
                }

                $action = $coupon->save();
                break;

            case 'delete':
                $coupon = Coupon::findorfail($post->id);
                $action = $coupon->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }
}
