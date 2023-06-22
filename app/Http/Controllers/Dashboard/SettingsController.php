<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Setting;
use App\Model\Shop;

class SettingsController extends Controller
{
    public function index()
    {
        $data['activemenu'] = array(
            'main' => 'settings'
        );

        $data['settings'] = Setting::findorfail(1);

        return view('dashboard.settings.index', $data);
    }

    public function submit(Request $post)
    {
        $post['id'] = 1;

        $rules = array(
            'name' => 'required',
            'title' => 'required',

            'contactmobile' => 'required|digits:10',
            'contactwhatsapp' => 'required|digits:10',
            'contactemail' => 'required|email',
            'contactaddress' => 'required',

            'deliverycharge_status' => 'required|in:enable,disable',
            'deliverycharge_perkm' => 'required|numeric|min:0',
            'deliverycharge_min' => 'nullable|numeric|min:0',
            'upto_3km' => 'nullable|numeric|min:0',
            '_3km_to_5km' => 'nullable|numeric|min:0',
            '_5km_to_8km' => 'nullable|numeric|min:0',
            'deliverycharge_freeordervalue' => 'nullable|numeric|min:0',
            'order_minval' => 'nullable|numeric|min:0',
            'order_storemindeliveryrange' => 'nullable|numeric|min:1000',
            'order_storemaxdeliveryrange' => 'nullable|numeric|min:1000',

            'smsflag' => 'required|in:1,0',
            'smssender' => 'nullable',
            'smsuser' => 'nullable',
            'smspwd' => 'nullable',

            'mailhost' => 'required',
            'mailport' => 'required',
            'mailenc' => 'required',
            'mailuser' => 'required',
            'mailpwd' => 'required',
            'mailfrom' => 'required',
            'mailname' => 'required',

            'firstorder_userwallet_type' => 'required|in:flat,percentage',
            'firstorder_userwallet_value' => 'required|numeric',
            'firstorder_parentwallet_type' => 'required|in:flat,percentage',
            'firstorder_parentwallet_value' => 'required|numeric',

            'maxwalletuse_mart' => 'required|numeric|min:0|max:100',
            'maxwalletuse_restaurant' => 'required|numeric|min:0|max:100',
            'maxwalletuse_service' => 'required|numeric|min:0|max:100',

            'userapp_version' => 'required',
            'userapp_maintenancemsg' => 'nullable',
            'branchapp_version' => 'required',
            'branchapp_maintenancemsg' => 'nullable',
            'deliveryboyapp_version' => 'required',
            'deliveryboyapp_maintenancemsg' => 'nullable',
        );

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        $currentsettings = Setting::findorfail($post->id);

        if ($post->order_storemindeliveryrange && $post->order_storemindeliveryrange != $currentsettings->order_storemindeliveryrange) {
            Shop::where('shop_delivery_radius', '<', $post->order_storemindeliveryrange)->update([
                'shop_delivery_radius' => $post->order_storemindeliveryrange
            ]);
        }

        if ($post->order_storemaxdeliveryrange) {
            Shop::where('shop_delivery_radius', '>', $post->order_storemaxdeliveryrange)->update([
                'shop_delivery_radius' => $post->order_storemaxdeliveryrange
            ]);
        }

        $action = Setting::updateorcreate(['id' => $post->id], $post->all());
        if ($action) {
            return response()->json(['status' => 'Task successfully completed'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again later'], 400);
        }
    }
}
