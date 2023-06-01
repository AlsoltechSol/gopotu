<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Brand;
use App\Model\Cart;
use App\Model\Category;
use App\Model\Color;
use App\Model\Commission;
use App\Model\OrderProduct;
use App\Model\Product;
use App\Model\ProductAttribute;
use App\Model\ProductAttributeVariant;
use App\Model\ProductImage;
use App\Model\ProductMaster;
use App\Model\ProductVariant;
use App\Model\Scheme;
use Carbon\Carbon;

class FoodsController extends Controller
{
    public function index()
    {
        $data['activemenu'] = [
            'main' => 'foods',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_food')) {
            abort(401);
        }

        return view('dashboard.foods.index', $data);
    }

    public function library()
    {
        $data['activemenu'] = [
            'main' => 'foods',
            'sub' => 'add',
        ];

        if (!\Myhelper::can(['add_food', 'edit_food'])) {
            abort(401);
        }

        return view('dashboard.foods.library', $data);
    }

    public function add($master_id = "none")
    {
        $data['categories'] = [];
        $data['brands'] = [];

        $data['activemenu'] = [
            'main' => 'foods',
            'sub' => 'add',
        ];

        if (!\Myhelper::can('add_food') && Myhelper::hasNotRole(['branch'])) {
            abort(401);
        }

        $exist = Product::where('shop_id', \Myhelper::getShop())->where('master_id', $master_id)->first();
        if ($exist) {
            \Session::flash('warning', "You have already have this dish in showcase");
            return redirect()->route('dashboard.foods.edit', ['id' => $exist->id]);
        }
        $schemes = Scheme::all();
        $data['attributes'] = ProductAttribute::where('slug', 'quantity')->orWhere('slug', 'weight')->orderBy('name', 'ASC')->get();
        $data['product_master'] = ProductMaster::where('type', 'restaurant')->findorfail($master_id);
        return view('dashboard.foods.submit', $data, compact('schemes'));
    }

    public function edit($id)
    {
        $data['activemenu'] = [
            'main' => 'foods',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('edit_food')) {
            abort(401);
        }

        $product = Product::where('type', 'restaurant')->with('product_variants')->where('id', $id)->first();
        if (!$product) {
            abort(404);
        }

        $data['product'] = $product;
        $data['product_master'] = ProductMaster::findorfail($product->master_id);
        $data['attributes'] = ProductAttribute::where('slug', 'quantity')->orWhere('slug', 'weight')->orderBy('name', 'ASC')->get();
        $schemes = Scheme::all();
        return view('dashboard.foods.submit', $data, compact('schemes'));
    }

    public function submit(Request $post)
    {
        switch ($post->operation) {
            case 'new':
                $permission = "add_food";

                $rules = [
                    'master_id' => 'required|exists:product_masters,id',
                ];
                break;

            case 'edit':
                $permission = "edit_food";

                $rules = [
                    'id' => 'required|exists:products,id',
                    'master_id' => 'required|exists:product_masters,id',
                ];
                break;

            case 'changestatus':
            case 'master':
            case 'verify':
            case 'changetopoffer':
                $permission = "edit_food";

                $rules = [
                    'id' => 'required|exists:products',
                ];
                break;

            case 'delete':
                $permission = "delete_food";

                $rules = [
                    'id' => 'required|exists:products',
                ];
                break;

            default:
                return response()->json(['status' => 'Unsupported Request.'], 400);
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
                $product_document = array();
                $product_document['type'] = 'restaurant';
                $product_document['availability'] = 'instock';
                $product_document['shop_id'] = \Myhelper::getShop();
                $product_document['master_id'] = $post->master_id;
                $product_document['food_type'] = $post->food_type;

                $productvariant_document = array();
                foreach ($post->price as $key => $item) {
                    if (!$post->price[$key]) {
                        return response()->json(['status' => 'Product price is required for Row ' . ($key + 1)], 400);
                    }

                    if ($post->offeredprice[$key] && $post->offeredprice[$key] >= $post->price[$key]) {
                        return response()->json(['status' => 'Product offered price cannot be greater than or equals for Row ' . ($key + 1)], 400);
                    }

                    if ($post->price[$key] < 0 || $post->offeredprice[$key] < 0) {
                        return response()->json(['status' => 'All value should be positive for Row ' . ($key + 1)], 400);
                    }

                    $product_master = ProductMaster::where('id', $product_document['master_id'])->first();

                    $scheme_id = $product_master->scheme_id;

                    if ($scheme_id == 0) {
                        $scheme_id = $product_master->category->scheme_id;
                    }

                    $commission = Commission::where('scheme_id', $scheme_id)->where('provider_id', '1')->first();

                    $commission_type = '';
                    $commission_value = '';
                    $listing_price = '';

                    if (isset($commission)) {
                        $commission_type = $commission->type;
                        $commission_value = $commission->value;
                    }

                    if ($product_master->type == 'mart') {

                        if ($commission_type == 'flat') {
                            $listing_price =  $post->offeredprice[$key] + $commission_value;
                            if ($listing_price > (float)$post->price[$key]) {
                                $listing_price = (float)$post->price[$key];
                            }
                        } else {

                            $listing_price =  floor($post->offeredprice[$key] + 0.01 * $post->offeredprice[$key] * $commission_value);
                      
                          //  $test = (float)$post->price[$key];
                            // dd((float)$post->price[$key]/2);
                           // dd($listing_price/2);

                            if ($listing_price > (float)$post->price[$key]) {
                               
                                $listing_price = (float)$post->price[$key];
                            }
                           // $listing_price = $post->price[$key];
                        }
                    } else {
                        if ($commission_type == 'flat') {
                            $listing_price =  $post->offeredprice[$key] + $commission_value;
                        } else {
                            $listing_price =  floor($post->offeredprice[$key] + 0.01 * $post->offeredprice[$key] * $commission_value);
                           
                        }
                    }

                    $productvariant_document[] = array(
                        'price' => $post->price[$key],
                        'offeredprice' => $post->offeredprice[$key],
                        'listingprice' => $listing_price,
                       
                    );
                }

                $product_create = Product::create($product_document);
                if ($product_create) {
                    foreach ($productvariant_document as $key => $variant) {
                        $productvariant_document[$key]['product_id'] = $product_create->id;
                    }

                    $productvariant_create = ProductVariant::insert($productvariant_document);
                    if ($productvariant_create) {
                        \Session::flash('success', 'Food uploaded to the database succesfully');
                        return response()->json(['status' => 'Food uploaded to the database succesfully'], 200);
                    }
                } else {
                    return response()->json(['status' => 'Food cannot be inserted to the database'], 400);
                }

                break;

            case 'edit':
                $product = Product::findorfail($post->id);

                $product_document = array();
                $product_document['type'] = 'restaurant';
                $product_document['availability'] = 'instock';
                $product_document['master_id'] = $post->master_id;
                $product_document['food_type'] = $post->food_type;

                $productvariant_document = array();
                foreach ($post->price as $key => $item) {
                    if (!$post->price[$key]) {
                        return response()->json(['status' => 'Product price is required for Row ' . ($key + 1)], 400);
                    }

                    if ($post->offeredprice[$key] && $post->offeredprice[$key] >= $post->price[$key]) {
                        return response()->json(['status' => 'Product offered price cannot be greater than or equals for Row ' . ($key + 1)], 400);
                    }

                    if ($post->price[$key] < 0 || $post->offeredprice[$key] < 0) {
                        return response()->json(['status' => 'All value should be positive for Row ' . ($key + 1)], 400);
                    }

                    
                    $product_master = ProductMaster::where('id', $product_document['master_id'])->first();


                    $scheme_id = $product_master->scheme_id;

                    if ($scheme_id == 0) {
                        $scheme_id = $product_master->category->scheme_id;
                    }

                    $commission = Commission::where('scheme_id', $scheme_id)->where('provider_id', '1')->first();

                    $commission_type = '';
                    $commission_value = '';
                    $listing_price = '';

                    if (isset($commission)) {
                        $commission_type = $commission->type;
                        $commission_value = $commission->value;
                    }

                    if ($product_master->type == 'mart') {

                        if ($commission_type == 'flat') {
                            $listing_price =  $post->offeredprice[$key] + $commission_value;
                            if ($listing_price > (float)$post->price[$key]) {
                                $listing_price = (float)$post->price[$key];
                            }
                        } else {

                            $listing_price =  floor($post->offeredprice[$key] + 0.01 * $post->offeredprice[$key] * $commission_value);
                      
                          //  $test = (float)$post->price[$key];
                            // dd((float)$post->price[$key]/2);
                           // dd($listing_price/2);

                            if ($listing_price > (float)$post->price[$key]) {
                               
                                $listing_price = (float)$post->price[$key];
                            }
                           // $listing_price = $post->price[$key];
                        }
                    } else {
                        if ($commission_type == 'flat') {
                            $listing_price =  $post->offeredprice[$key] + $commission_value;
                        } else {
                            $listing_price =  floor($post->offeredprice[$key] + 0.01 * $post->offeredprice[$key] * $commission_value);
                           
                        }
                    }


                    $productvariant_document[] = array(
                        'id' => $post->variant_id[$key],
                        'price' => $post->price[$key],
                        'offeredprice' => $post->offeredprice[$key],
                        'listingprice' => $listing_price,
                       
                    );
                }

                $product_update = Product::where('id', $product->id)->update($product_document);
                if ($product_update) {
                    ProductVariant::where('product_id', $product->id)->delete();

                    foreach ($productvariant_document as $key => $variant_doc) {
                        if (@$variant_doc['id'] && $variant_doc['id'] != null) {
                            $variant_doc['deleted_at'] = null;
                            ProductVariant::where('id', $variant_doc['id'])->withTrashed()->update($variant_doc);
                        } else {
                            ProductVariant::insert($variant_doc);
                        }
                    }

                    \Session::flash('success', 'Food updated succesfully');
                    return response()->json(['status' => 'Food updated succesfully'], 200);
                } else {
                    return response()->json(['status' => 'Food cannot be updated to the database'], 400);
                }

                break;

            case 'master':
                $product = Product::findorfail($post->id);

                if ($product->master_status == '1') {
                    $product->master_status = '0';
                } else {
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'restaurant') {
                        return response()->json(['status' => 'The merchant is currently not assigned for mart sales'], 400);
                    }

                    $product->master_status = '1';
                }

                $action = $product->save();
                break;
                    
            case 'verify':
                $product = Product::findorfail($post->id);

                if ($product->verification_status == '1') {
                    $product->verification_status = '0';
                } else {
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'restaurant') {
                        return response()->json(['status' => 'The merchant is currently not assigned for mart sales'], 400);
                    }

                    $product->verification_status = '1';
                }

                $action = $product->save();
                break;

            case 'changestatus':
                $product = Product::findorfail($post->id);
                if ($product->status == '1') {
                    $product->status = '0';
                } else {
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'restaurant') {
                        return response()->json(['status' => 'The merchant is currently not assigned for restaurant sales'], 400);
                    }

                    $product->status = '1';
                }

                $action = $product->save();
                break;

            case 'changetopoffer':
                $product = Product::findorfail($post->id);
                if ($product->top_offer == '1') {
                    $product->top_offer = '0';
                } else {
                    $product->top_offer = '1';
                }

                $action = $product->save();
                break;

            case 'delete':
                $product = Product::findorfail($post->id);
                $action = $product->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }
}
