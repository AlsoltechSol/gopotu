<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\Myhelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    public function fetchData($type, $fetch = 'all', $id = 'none', Request $request)
    {
        switch ($type) {
            case 'user':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->where('slug', 'user');
                });

                $request['searchdata'] = [];
                break;

            case 'mobile':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->where('slug', 'user');
                });

                $request['searchdata'] = [];
                break;

            case 'order_id':
                $query = DB::table('orders')->select('code');

                $request['searchdata'] = [];
                break;

            case 'city':
                $query = DB::table('orders')->select('cust_location');

                $request['searchdata'] = [];
                break;

            case 'admin':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->where('slug', 'admin');
                });

                $request['searchdata'] = [];
                break;

            case 'deliveryboy':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->where('slug', 'deliveryboy');
                });

                $request['searchdata'] = [];
                break;

            case 'branch':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->where('slug', 'branch');
                });

                $request['searchdata'] = [];
                break;

            case 'stores':
                $query = \App\Model\Shop::with('user');
                $request['searchdata'] = [];
                break;

            case 'brands':
                $query = \App\Model\Brand::query();
                $request['searchdata'] = [];
                break;

            case 'categories':
                $query = \App\Model\Category::with('parent_category');

                if ($request->has('parent_id') && $request->parent_id == null) {
                    $query->where('parent_id', null);
                }

                $request['searchdata'] = ['parent_id'];
                break;

            case 'colors':
                $query = \App\Model\Color::query();
                $request['searchdata'] = [];
                break;

            case 'productmaster':
                $query = \App\Model\ProductMaster::with('category', 'brand')->orderBy('created_at', 'desc');
                $request['searchdata'] = ['type'];
                break;

            case 'attributes':
                $query = \App\Model\ProductAttribute::query();
                $request['searchdata'] = [];
                break;

            case 'attribute_varaints':
                $query = \App\Model\ProductAttributeVariant::with('attribute');
                $request['searchdata'] = ['attribute_id'];
                break;

            case 'martproductlibrary':
                $query = \App\Model\ProductMaster::where('type', 'mart')->with('category', 'brand')->where('status', '1');
                $request['searchdata'] = [];
                break;

            case 'martproducts':
                $query = \App\Model\Product::with('product_variants', 'details', 'shop')->where('products.type', 'mart')->orderBy('created_at', 'desc');

                if (\Myhelper::hasNotRole(['admin', 'superadmin'])) {
                    $query->where('shop_id', \Myhelper::getShop());
                }

                $request['searchdata'] = [];
                break;

            case 'restaurantproductlibrary':
                $query = \App\Model\ProductMaster::where('type', 'restaurant')->with('category', 'brand')->where('status', '1');
                $request['searchdata'] = [];
                break;

            case 'restaurantproducts':
                $query = \App\Model\Product::where('products.type', 'restaurant')->with('product_variants', 'details', 'shop')->orderBy('created_at', 'desc');

                if (\Myhelper::hasNotRole(['admin', 'superadmin'])) {
                    $query->where('shop_id', \Myhelper::getShop());
                }

                $request['searchdata'] = [];
                break;

            case 'martstock_variants':
                $query = \App\Model\ProductVariant::with('product', 'color_details')->whereHas('product', function ($q) {
                    $q->where('type', 'mart');
                });

                if (\Myhelper::hasNotRole(['admin', 'superadmin'])) {
                    $query->whereHas('product', function ($q) {
                        $q->where('shop_id', \Myhelper::getShop());
                    });
                }

                $request['searchdata'] = ['product_id'];
                break;

            case 'product_variants':
                $query = \App\Model\ProductVariant::with('product', 'color_details');

                if (\Myhelper::hasNotRole(['admin', 'superadmin'])) {
                    $query->whereHas('product', function ($q) {
                        $q->where('shop_id', \Myhelper::getShop());
                    });
                }

                $request['searchdata'] = ['product_id'];
                break;

            case 'orders':


                $query = \App\Model\Order::with('user', 'deliveryboy', 'shop');

                if (\Myhelper::hasRole(['branch'])) {
                    $query->where('shop_id', \Myhelper::getShop());
                    $query->whereIn('status', ['received', 'accepted', 'processed', 'intransit', 'outfordelivery', 'delivered', 'cancelled', 'returned']);
                } else {
                    $query->whereIn('status', ['received', 'accepted', 'processed', 'intransit', 'outfordelivery', 'delivered', 'cancelled', 'returned']);
                }

                $request['searchdata'] = ['user_id', 'type', 'status', 'cust_mobile', 'id'];

                $request['datasearchcolumns'] = [
                    'id', 'code', 'cust_mobile', 'cust_name', 'status', 'cust_address', 'user_id', 'user.email', 'user.name', 'user.mobile', 'payment_mode'
                ];

                $start_date = Carbon::now()->format('Y-m-d') . " 00:00:00";
                $end_date = Carbon::now()->format('Y-m-d') . " 23:59:59";
                break;

            case 'orderdeliveryboylogs':
                $query = \App\Model\OrderDeliveryboyLog::with('deliveryboy', 'order');

                $request['searchdata'] = ['order_id'];
                $request['datasearchcolumns'] = [];
                break;

            case 'orderreturnreplacedeliveryboylogs':
                $query = \App\Model\OrderReturnReplaceDeliveryboyLog::with('deliveryboy', 'orderreturnreplace.order');

                if ($request->has('order_id')) {
                    $query->whereHas('orderreturnreplace', function ($q) use ($request) {
                        $q->whereHas('order', function ($qr) use ($request) {
                            $qr->where('id', $request->order_id);
                        });
                    });
                }

                $request['searchdata'] = [];
                $request['datasearchcolumns'] = [];
                break;

            case 'returnreplacements':
                $query = \App\Model\OrderReturnReplace::with('order', 'deliveryboy', 'order.user', 'order.shop');

                if (\Myhelper::hasRole(['branch'])) {
                    $query->whereHas('order', function ($q) {
                        $q->where('shop_id', \Myhelper::getShop());
                    });
                }

                $request['searchdata'] = ['order.user_id', 'type', 'status'];

                $request['datasearchcolumns'] = [
                    'id', 'order_id', 'code', 'status', 'type',
                ];

                $start_date = Carbon::now()->format('Y-m-d') . " 00:00:00";
                $end_date = Carbon::now()->format('Y-m-d') . " 23:59:59";
                break;

            case 'fundtrmembers':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->whereIn('slug', ['user', 'branch', 'deliveryboy']);
                });

                $request['searchdata'] = ['role_id'];
                break;

            case 'payoutrequests':
                $query = \App\Model\WalletRequest::with('user')->where('type', 'payout')->whereIn('wallet_type', ['branchwallet', 'riderwallet']);

                if (\Myhelper::hasrole(['branch', 'deliveryboy'])) {
                    $query->where('user_id', \Auth::id());
                }

                $request['searchdata'] = [];
                break;

            case 'collectionsubmitted':
                $query = \App\Model\WalletRequest::with('user')->where('type', 'payout')->whereIn('wallet_type', ['creditwallet']);

                if (\Myhelper::hasrole(['branch', 'deliveryboy'])) {
                    $query->where('user_id', \Auth::id());
                }

                $request['searchdata'] = [];
                break;

            case 'walletstatement':
                $query = \App\Model\WalletReport::with('user');

                if (\Myhelper::hasrole(['branch', 'deliveryboy'])) {
                    $query->where('user_id', \Auth::id());
                }

                $request['searchdata'] = ['ref_id', 'service'];

                if ($request->startendtime_filter != 'false') {
                    $start_date = Carbon::now()->format('Y-m-d') . " 00:00:00";
                    $end_date = Carbon::now()->format('Y-m-d') . " 23:59:59";
                }
                break;

            case 'supporttickets':
                $query = \App\Model\SupportTicket::query();
                $request['searchdata'] = ['status'];
                break;

            case 'coupons':
                $query = \App\Model\Coupon::withCount('coupon_used');
                $request['searchdata'] = [];
                break;

            case 'schemes':
                $query = \App\Model\Scheme::query();
                $request['searchdata'] = [];
                break;

            case 'contents':
                $query = \App\Model\CmsContent::query();
                $request['searchdata'] = [];
                break;

            case 'testimonials':
                $query = \App\Model\TestimonialContent::query();
                $request['searchdata'] = [];
                break;

            case 'sociallinks':
                $query = \App\Model\SociallinkMaster::query();
                $request['searchdata'] = [];
                break;

            case 'cancellationreasons':
                $query = \App\Model\OrderCancellationReason::query();
                $request['searchdata'] = ['usertype'];
                break;

            case 'notificationusers':
                $query = \App\User::whereHas('role', function ($q) {
                    $q->whereIn('slug', ['user']);
                });

                $request['searchdata'] = ['status'];
                break;

            case 'roles':
                $query = \App\Model\Role::query();
                $request['searchdata'] = [];
                break;

            case 'permissions':
                $query = \App\Model\Permission::query();
                $request['searchdata'] = [];
                break;

            default:
                abort(404, 'Invalid request recieved');
        }

        if ($id != 'none') {
            $query->where('id', $id);
        }

        if ($request->has('daterange') && $request->daterange != "") {
            $request['daterange'] = explode('-', str_replace(' ', '', $request->daterange));

            if (isset($request->daterange[0])) {
                $start_date = Carbon::parse($request->daterange[0])->format('Y-m-d') . " 00:00:00";
            }

            if (isset($request->daterange[1])) {
                $end_date = Carbon::parse($request->daterange[1])->format('Y-m-d') . " 23:59:59";
            }
        }

        if (!in_array($fetch, ['single', 'special'])) {
            if (isset($start_date) && isset($end_date)) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }


        if ($request->has('datasearchcolumns') && count($request->datasearchcolumns) > 0) {
            $request['searchtext'] = @$request->search['value'];
            if ($request->searchtext) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->datasearchcolumns as $value) {
                        if (strpos($value, '.') !== false) {
                            $tempattr = explode('.', $value);
                            $q->orWhereHas($tempattr[0], function ($qr) use ($tempattr, $request) {
                                $qr->where($tempattr[1], 'like', '%' . $request->searchtext . '%');
                            });
                        } else {
                            $q->orWhere($value, 'like', $request->searchtext . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchtext . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchtext);
                        }
                    }
                });
            }
        }


        $input = $request->all();
        foreach ($request->searchdata as $key => $value) {
            if (isset($input[$value]) && $input[$value] != '') {
                if (strpos($value, '.') !== false) {
                    $tempattr = explode('.', $value);
                    $query->whereHas($tempattr[0], function ($q) use ($tempattr, $input, $value) {
                        if (is_array($input[$value])) {
                            $q->whereIn($tempattr[1], $input[$value]);
                        } else {
                            $q->where($tempattr[1], $input[$value]);
                        }
                    });
                } else {
                    if (is_array($input[$value])) {
                        $query->whereIn($value, $input[$value]);
                    } else {
                        $query->where($value, $input[$value]);
                    }
                }
            }
        }

        switch ($fetch) {
            case 'single':
                return response()->json(['result' => $query->first()], 200);
                break;

            case 'select':
                switch ($type) {
                    case 'user':
                        return response()->json(['result' => $query->pluck('name', 'id')], 200);
                        break;
                    case 'mobile':
                        return response()->json(['result' => $query->pluck('mobile', 'id')], 200);
                        break;
                    case 'order_id':
                        return response()->json(['result' => $query->pluck('code')], 200);
                        break;
                    case 'city':
                        return response()->json(['result' => $query->pluck('cust_location')], 200);
                        break;
                }
                break;
        }

        if (request()->ajax()) {
            return datatables()->of($query)->make(true);
        }
    }
}
