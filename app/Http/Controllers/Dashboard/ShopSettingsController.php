<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Myhelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Model\Shop;
use App\User;
use Carbon\Carbon;

class ShopSettingsController extends Controller
{
    public function index($shop_id = 'none', Request $request)
    {
        if ($shop_id == 'none') {
            $data['shop'] = Shop::findorfail(\Myhelper::getShop());
            $data['activemenu'] = array(
                'main' => 'shopsettings'
            );
        } else {
            $data['shop'] = Shop::findorfail($shop_id);
            $data['activemenu'] = array(
                'main' => 'stores'
            );
        }

        if ($data['shop']->id != \Myhelper::getShop()) {
            if (\Myhelper::hasrole(['branch'])) {
                abort(404);
            } elseif (\Myhelper::hasrole(['superadmin', 'admin'])) {
                // if (!\Myhelper::can('edit_store')) {
                //     abort(401);
                // }
            }
        }

        $admins = User::where('role_id', 2)->get();

        return view('dashboard.shopsettings.index', $data, compact('admins'));
    }

    public function submit(Request $post)
    {
        $rules = array(
            'shop_id' => 'required|exists:shops,id',
            'shop_name' => 'required',
            'shop_tagline' => 'nullable',
            'shop_image' => 'nullable|mimes:jpeg,jpg,png,gif,webp|dimensions:width=350,height=200',
            'shop_mobile' => 'required|digits:10',
            'shop_whatsapp' => 'required|digits:10',
            'shop_email' => 'required|email',
            'shop_location' => 'required',
            'shop_delivery_radius' => 'required|numeric',
            'shop_latitude' => 'required|numeric',
            'shop_longitude' => 'required|numeric',
            'shop_address_line1' => 'nullable',
            'shop_address_line2' => 'nullable',
            'shop_postal_code' => 'required|digits:6',
            'shop_city' => 'nullable',
            'shop_state' => 'required|exists:state_masters,state_code',
            'shop_country' => 'nullable',
           // 'admin_id' => 'required'
        );

        if (config('app.order_storemindeliveryrange')) {
            $rules['shop_delivery_radius'] .= '|min:' . config('app.order_storemindeliveryrange');
        }

        if (config('app.order_storemaxdeliveryrange')) {
            $rules['shop_delivery_radius'] .= '|max:' . config('app.order_storemaxdeliveryrange');
        }

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        // $shop_id = \Myhelper::getShop();
        $shop = Shop::findorfail($post->shop_id);

        if ($shop->id != \Myhelper::getShop()) {
            if (\Myhelper::hasrole(['branch'])) {
                return response()->json(['status' => 'Not Found'], 404);
            } elseif (\Myhelper::hasrole(['superadmin', 'admin'])) {
                if (!\Myhelper::can('edit_store')) {
                    return response()->json(['status' => 'Unauthorized Action'], 401);
                }
            }
        }

        if ($shop->shop_logo != null) {
            $deletefile = 'uploads/shop/' . $shop->shop_logo;
        }

        $document = array();
        $document['shop_name'] = $post->shop_name;
        $document['shop_tagline'] = $post->shop_tagline;
        $document['shop_mobile'] = $post->shop_mobile;
        $document['shop_whatsapp'] = $post->shop_whatsapp;
        $document['shop_email'] = $post->shop_email;
        $document['shop_location'] = $post->shop_location;
        $document['shop_delivery_radius'] = $post->shop_delivery_radius;
        $document['shop_latitude'] = $post->shop_latitude;
        $document['shop_longitude'] = $post->shop_longitude;
        $document['admin_id'] = $post->admin_id;

        if ($post->file('shop_image') && \Myhelper::hasrole(['superadmin', 'admin'])) {
            $file = $post->file('shop_image');
            $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

            // if (\Image::make($file->getRealPath())->resize(350, 200)->save('uploads/shop/' . $filename, 60)) {
            if (\Image::make($file->getRealPath())->save('uploads/shop/' . $filename)) {
                $document['shop_logo'] = $filename;

                if (isset($deletefile)) {
                    \File::delete($deletefile);
                }
            } else {
                return response()->json(['status' => 'File cannot be saved to server.'], 400);
            }
        }

        $document['shop_address'] = json_encode((object)[
            'address_line1' => $post->shop_address_line1,
            'address_line2' => $post->shop_address_line2,
            'postal_code' => $post->shop_postal_code,
            'city' => $post->shop_city,
            'state' => $post->shop_state,
            'country' => $post->shop_country,
        ]);

        $action = Shop::where('id', $post->shop_id)->update($document);
        if ($action) {
            return response()->json(['status' => 'Task successfully completed'], 200);
        } else {
            return response()->json(['status' => 'Task failed. Please try again later'], 400);
        }
    }

    public function changeOnlineStatus(Request $post)
    {
        $rules = array(
            'id' => 'required|exists:shops,id',
        );

        if (isset($rules)) {
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => $value[0]], 400);
                }
            }
        }

        $shop = Shop::findorfail($post->id);

        if ($shop->id != \Myhelper::getShop()) {
            if (\Myhelper::hasrole(['branch'])) {
                return response()->json(['status' => 'Not Found'], 404);
            } elseif (\Myhelper::hasrole(['superadmin', 'admin'])) {
                if (!\Myhelper::can('edit_store')) {
                    return response()->json(['status' => 'Unauthorized Action'], 401);
                }
            }
        }

        if ($shop->online) {
            $shop->online = '0';
            $successmsg = "Store closed successfully. The store will stop receiving new orders";
        } else {
            $shop->online = '1';
            $successmsg = "Store opened successfully. The store will start receiving new orders";
        }

        $shop->save();

        \Session::flash('success', $successmsg);
        return response()->json(['status' => $successmsg], 200);
    }
}
