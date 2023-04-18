<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Commission;
use App\Model\Scheme;
use App\Model\Provider;

class ResourcesController extends Controller
{
    public function index($type, $value = 'none')
    {
        $data['activemenu'] = array(
            'main' => 'resources',
            'sub' => $type
        );

        switch ($type) {
            case 'scheme':
                $permission = 'view_scheme';
                $view = 'schemes';
                break;

            case 'scheme-adminordercharge':
                $permission = 'update_scheme_charge';
                $view = 'scheme_charges';

                $provider_type = 'order_admincharges';
                $scheme = Scheme::findorfail($value);
                $providers = Provider::where('type', $provider_type)->get();

                foreach ($providers as $key => $provider) {
                    $comm = Commission::where('scheme_id', $scheme->id)->where('provider_id', $provider->id)->first();
                    if ($comm) {
                        $provider->chargetype = $comm->type;
                        $provider->chargevalue = $comm->value;
                    } else {
                        $provider->chargetype = 'flat';
                        $provider->chargevalue = 0;
                    }
                }

                $data['activemenu']['sub'] = 'scheme';
                $data['heading'] = 'Admin Order Charges';
                $data['provider_type'] = $provider_type;
                $data['scheme'] = $scheme;
                $data['providers'] = $providers;
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(401);
        }

        return view('dashboard.resources.' . $view, $data);
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'scheme-new':
                $rules = [
                    'name' => 'required',
                    'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_scheme';
                break;

            case 'scheme-edit':
                $rules = [
                    'id' => 'required|exists:schemes',
                    'name' => 'required',
                    'icon' => 'nullable|mimes:jpeg,jpg,png,gif',
                ];

                $permission = 'add_scheme';
                break;

            case 'scheme-changestatus':
                $rules = [
                    'id' => 'required|exists:schemes',
                ];

                $permission = 'edit_scheme';
                break;

            case 'scheme-delete':
                $rules = [
                    'id' => 'required|exists:schemes',
                ];

                $permission = 'delete_scheme';
                break;

            case 'scheme-commissionupdate':
            case 'scheme-chargeupdate':
                $rules = [
                    'scheme_id' => 'required|exists:schemes,id',
                    'product' => 'required|in:order_admincharges',
                ];

                $permission = 'update_scheme_charge';
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
            case 'scheme-edit':
                $scheme = Scheme::findorfail($post->id);
            case 'scheme-new':
                $document = array();
                $document['name'] = $post->name;

                $action = Scheme::updateorcreate(['id' => $post->id], $document);
                break;

            case 'scheme-changestatus':
                $scheme = Scheme::findorfail($post->id);
                if ($scheme->status == '1') {
                    $scheme->status = '0';
                } else {
                    $scheme->status = '1';
                }

                $action = $scheme->save();
                break;

            case 'scheme-delete':
                $scheme = Scheme::findorfail($post->id);
                $action = $scheme->delete();
                break;

            case 'scheme-commissionupdate':
            case 'scheme-chargeupdate':
                foreach ($post->provider_id as $key => $value) {
                    $pass = true;

                    if ($post->value[$key] < 0) {
                        $pass = false;
                        $update[$post->provider_id[$key]] = "Value should be positive";
                    }

                    $slabtype = $post->type[$key];

                    if ($pass) {
                        $update[$value] = Commission::updateOrCreate(
                            [
                                'scheme_id'         => $post->scheme_id,
                                'provider_id'       => $post->provider_id[$key],
                                'product'           => $post->product,
                            ],
                            [
                                'scheme_id'         => $post->scheme_id,
                                'provider_id'       => $post->provider_id[$key],
                                'type'              => $slabtype,
                                'value'             => $post->value[$key],
                                'product'           => $post->product
                            ]
                        );
                    }
                }

                return response()->json(['status' => 'success', 'result' => $update], 200);
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }
}
