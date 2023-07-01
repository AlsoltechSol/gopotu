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
use App\Model\Shop;
use Carbon\Carbon;

class ProductsController extends Controller
{
    public function index()
    {
        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_product')) {
            abort(401);
        }

        $shops = Shop::all();

        return view('dashboard.products.index', compact('shops'));
    }

    public function view($id = "none")
    {
        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('view_product')) {
            abort(401);
        }

        $product = Product::where('type', 'mart')->findorfail($id);
        $leftincart = Cart::whereHas('variant', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->count();


        $data['product'] = $product;
        $data['leftincart'] = $leftincart;

        $data['totalsales'] = OrderProduct::where('product_id', $product->id)->whereHas('order', function ($q) {
            $q->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered']);
        })->sum('sub_total');

        $data['monthsales'] = OrderProduct::where('product_id', $product->id)->whereHas('order', function ($q) {
            $q->whereIn('status', ['received', 'processed', 'accepted', 'intransit', 'outfordelivery', 'delivered']);
            $q->whereMonth('created_at', Carbon::now());
            $q->whereYear('created_at', Carbon::now());
        })->sum('sub_total');

        return view('dashboard.products.view', $data);
    }

    public function library()
    {
        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'add',
        ];

        if (!\Myhelper::can(['add_product', 'edit_product'])) {
            abort(401);
        }

        return view('dashboard.products.library', $data);
    }

    public function add($master_id = "none")
    {
        $data['categories'] = [];
        $data['brands'] = [];

        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'add',
        ];

        if (!\Myhelper::can('add_product') && Myhelper::hasNotRole(['branch'])) {
            abort(401);
        }

        $exist = Product::where('shop_id', \Myhelper::getShop())->where('master_id', $master_id)->first();
        if ($exist) {
            \Session::flash('warning', "You have already have this product in showcase");
            return redirect()->route('dashboard.products.edit', ['id' => $exist->id]);
        }

        $data['product_master'] = ProductMaster::where('type', 'mart')->findorfail($master_id);
        $data['colors'] = Color::orderBy('name', 'ASC')->get();
        $data['attributes'] = ProductAttribute::orderBy('name', 'ASC')->get();
        $schemes = Scheme::all();
        return view('dashboard.products.submit', $data, compact('schemes'));
    }

    public function edit($id)
    {
        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'index',
        ];

        if (!\Myhelper::can('edit_product')) {
            abort(401);
        }

        $product = Product::where('type', 'mart')->with('product_variants')->where('id', $id)->first();
        if (!$product) {
            abort(404);
        }

        if ($product->product_variants && count($product->product_variants) > 0) {
            foreach ($product->product_variants as $key => $variant) {
                $variant->color_name = '';
                if ($variant->color) {
                    $variant->color_name = Color::where('code', $variant->color)->pluck('name')->first();
                }
            }
        }

        // dd($product->toArray());

        $data['product'] = $product;
        $data['product_master'] = ProductMaster::findorfail($product->master_id);
        $data['colors'] = Color::orderBy('name', 'ASC')->get();
        $data['attributes'] = ProductAttribute::orderBy('name', 'ASC')->get();

        $schemes = Scheme::all();
        return view('dashboard.products.submit', $data, compact('schemes'));
    }

    public function submit(Request $post)
    {
        // return 'hi';
        // return $post;
        switch ($post->operation) {
            case 'new':
                $permission = "add_product";

                $rules = [
                    'availability' => 'required|in:instock,comingsoon',
                    'master_id' => 'required|exists:product_masters,id',
                ];
                break;

            case 'edit':
                $permission = "edit_product";

                $rules = [
                    'id' => 'required|exists:products,id',
                    'master_id' => 'required|exists:product_masters,id',
                ];
                break;

            case 'changestatus':
            case 'master':
            case 'verify':
            case 'changetopoffer':
                $permission = "edit_product";

                $rules = [
                    'id' => 'required|exists:products',
                ];
                break;

            case 'delete':
                $permission = "delete_product";

                $rules = [
                    'id' => 'required|exists:products',
                ];
                break;

            case 'product-image-upload':
                $permission = "edit_product";

                $rules = [
                    'id' => 'required|exists:products,id',
                    'file' => 'required|mimes:jpeg,jpg,png,gif,webp',
                ];
                break;

            case 'product-image-delete':
                $permission = "edit_product";

                $rules = [
                    'id' => 'required|exists:product_images,id',
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
                if ($post->available_variant && (!$post->variants || count($post->variants) < 1)) {
                    return response()->json(['status' => 'Please select a variant option to continue'], 400);
                }

                $product_document = array();
                $product_document['type'] = 'mart';
                $product_document['availability'] = $post->availability;
                $product_document['shop_id'] = \Myhelper::getShop();
                $product_document['master_id'] = $post->master_id;
                $product_document['colors'] = json_encode($post->available_colors);
                $product_document['variant'] = $post->available_variant;
                $product_document['variant_options'] = json_encode($post->variants);

                $productvariant_document = array();
                foreach ($post->price as $key => $item) {
                    if (in_array($post->availability, ['instock', 'outofstock'])) {
                        if (!$post->price[$key]) {
                            return response()->json(['status' => 'Product price is required for Row ' . ($key + 1)], 400);
                        }

                        // if (!$post->offeredprice[$key]) {
                        //     return response()->json(['status' => 'Product offered price is required for Row ' . ($key + 1)], 400);
                        // }

                        if ($post->offeredprice[$key] && $post->offeredprice[$key] > $post->price[$key]) {
                            return response()->json(['status' => 'Product offered price cannot be greater than for Row ' . ($key + 1)], 400);
                        }

                        if (!$post->quantity[$key]) {
                            return response()->json(['status' => 'Product quantity is required for Row ' . ($key + 1)], 400);
                        }

                        if ($post->price[$key] < 0 || $post->offeredprice[$key] < 0 || $post->quantity[$key] < 0) {
                            return response()->json(['status' => 'All value should be positive for Row ' . ($key + 1)], 400);
                        }

                        if ($post->sku[$key] == null) {
                            return response()->json(['status' => 'Please enter SKU for Row ' . ($key + 1)], 400);
                        }
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
                        'product_id' => null,
                        'variant' => $post->variant[$key],
                        'color' => $post->color[$key],
                        'price' => $post->price[$key],
                        'offeredprice' => $post->offeredprice[$key],
                        'listingprice' => $listing_price,
                        'quantity' => $post->quantity[$key],
                        'sku' => $post->sku[$key],
                    );
                }

                $product_create = Product::create($product_document);
                if ($product_create) {
                    if (in_array($post->availability, ['instock', 'outofstock'])) {
                        foreach ($productvariant_document as $key => $variant) {
                            $productvariant_document[$key]['product_id'] = $product_create->id;
                        }

                        $productvariant_create = ProductVariant::insert($productvariant_document);
                        if ($productvariant_create) {
                            \Session::flash('success', 'Product uploaded to the database succesfully');
                            return response()->json(['status' => 'Product uploaded to the database succesfully'], 200);
                        }
                    } else {
                        \Session::flash('success', 'Product uploaded to the database succesfully');
                        return response()->json(['status' => 'Product uploaded to the database succesfully'], 200);
                    }
                } else {
                    return response()->json(['status' => 'Product cannot be inserted to the database'], 400);
                }

                break;

            case 'edit':
                $product = Product::findorfail($post->id);




                if ($product->variant && (!$post->variants || count($post->variants) < 1)) {

                    return response()->json(['status' => 'Please select a variant option to continue'], 400);
                }



                $product_document = array();
                $product_document['type'] = 'mart';
                $product_document['colors'] = json_encode($post->available_colors);
                $product_document['variant_options'] = json_encode($post->variants);
                $product_document['master_id'] = $product->master_id;

                // dd($product_document['master_id']);

                if (!$product->variant) {
                    $product_document['variant'] = $post->available_variant;
                }


                $productvariant_document = array();




                foreach ($post->price as $key => $item) {



                    if (in_array($post->availability, ['instock', 'outofstock'])) {


                        if (!$post->price[$key]) {
                            return response()->json(['status' => 'Product price is required for Row ' . ($key + 1)], 400);
                        }

                        // if (!$post->offeredprice[$key]) {
                        //     return response()->json(['status' => 'Product offered price is required for Row ' . ($key + 1)], 400);
                        // }

                        if ($post->offeredprice[$key] && $post->offeredprice[$key] > $post->price[$key]) {
                            return response()->json(['status' => 'Product offered price cannot be greater than for Row ' . ($key + 1)], 400);
                        }

                        if (!$post->quantity[$key]) {
                            return response()->json(['status' => 'Product quantity is required for Row ' . ($key + 1)], 400);
                        }

                        if ($post->price[$key] < 0 || $post->offeredprice[$key] < 0 || $post->quantity[$key] < 0) {
                            return response()->json(['status' => 'All value should be positive for Row ' . ($key + 1)], 400);
                        }

                        if ($post->sku[$key] == null) {
                            return response()->json(['status' => 'Please enter SKU for Row ' . ($key + 1)], 400);
                        }
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
                        'product_id' => $product->id,
                        'variant' => $post->variant[$key],
                        'color' => $post->color[$key],
                        'price' => $post->price[$key],
                        'offeredprice' => $post->offeredprice[$key],
                        'listingprice' => $listing_price,
                        'quantity' => $post->quantity[$key],
                        'sku' => $post->sku[$key],
                    );

                    // dd($productvariant_document);
                }

                $product_update = Product::where('id', $product->id)->update($product_document);

                if ($product_update) {
                    if (in_array($post->availability, ['instock', 'outofstock'])) {
                        ProductVariant::where('product_id', $product->id)->delete();

                        foreach ($productvariant_document as $key => $variant_doc) {
                            if (@$variant_doc['id'] && $variant_doc['id'] != null) {
                                $variant_doc['deleted_at'] = null;
                                ProductVariant::where('id', $variant_doc['id'])->withTrashed()->update($variant_doc);
                            } else {
                                ProductVariant::insert($variant_doc);
                            }
                        }
                    }

                    \Session::flash('success', 'Product updated succesfully');
                    return response()->json(['status' => 'Product updated succesfully'], 200);

                    // $productvariant_create = ProductVariant::insert($productvariant_document);
                    // if ($productvariant_create) {
                    //     \Session::flash('success', 'Product updated succesfully');
                    //     return response()->json(['status' => 'Product updated succesfully'], 200);
                    // }
                } else {
                    return response()->json(['status' => 'Product cannot be updated to the database'], 400);
                }

                break;

            case 'changestatus':
                $product = Product::findorfail($post->id);

                if ($product->status == '1') {
                    $product->status = '0';
                } else {
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'mart') {
                        return response()->json(['status' => 'The merchant is currently not assigned for mart sales'], 400);
                    }

                    $product->status = '1';
                }

                $action = $product->save();
                break;
            case 'master':
                $product = Product::findorfail($post->id);

                if ($product->master_status == '1') {
                    $product->master_status = '0';
                } else {
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'mart') {
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
                    if ($product->shop->user->role->slug == 'branch' && $product->shop->user->business_category != 'mart') {
                        return response()->json(['status' => 'The merchant is currently not assigned for mart sales'], 400);
                    }

                    $product->verification_status = '1';
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

            case 'product-image-upload':
                if ($post->file('file')) {
                    $file = $post->file('file');
                    $filename = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

                    if (\Image::make($file->getRealPath())->resize(500, 500)->save('uploads/product/gallery/' . $filename)) {
                        $document = array();
                        $document['product_id'] = $post->id;
                        $document['image'] = $filename;

                        $action = ProductImage::create($document);
                    } else {
                        return response()->json(['status' => 'File cannot be saved to server.'], 400);
                    }
                } else {
                    $action = false;
                }
                break;

            case 'product-image-delete':
                $productimage = ProductImage::findorfail($post->id);
                if ($productimage->image) {
                    $deletefile = 'uploads/product/gallery/' . $productimage->image;

                    if (isset($deletefile)) {
                        \File::delete($deletefile);
                    }
                }

                $action = $productimage->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }

    public function ajaxMethods(Request $post)
    {
        switch ($post->type) {
            case 'fetch-categories':
                $_catquery = Category::with('sub_categories')->where('parent_id', NULL);

                if ($post->has('searchtext') && $post->searchtext != null) {
                    $_catquery->where(function ($q) use ($post) {
                        $q->orWhere('name', 'LIKE', '%' . $post->searchtext . '%');
                        $q->orWhereHas('sub_categories', function ($qr) use ($post) {
                            $qr->where('name', 'LIKE', '%' . $post->searchtext . '%');
                        });
                    });
                }

                $categories = array();
                $categories[] = ["id" => "", "text" => "Select from the dropdown"];
                foreach ($_catquery->orderBy('name', 'ASC')->get() as $lv1_key => $level1) {
                    $temp =  ["id" => $level1->id, "text" => $level1->name, "disabled" => true];

                    // $categories[] = $temp;

                    if (count($level1->sub_categories) > 0) {
                        $temp['children'] = [];
                        foreach ($level1->sub_categories as $lv2_key => $level2) {
                            $temp['children'][] = ["id" => $level2->id, "text" => '- ' . $level2->name];
                        }
                    }

                    $categories[] = $temp;
                }

                return response()->json(['categories' => $categories], 200);
                break;

            case 'color-details':
                $colors = Color::whereIn('code', $post->colors)->orderBy('name', 'ASC')->get();
                return response()->json(['colors' => $colors], 200);
                break;

            case 'fetch-gallery':
                $images = ProductImage::where('product_id', $post->product_id)->get();
                return response()->json(['images' => $images], 200);
                break;

            case 'attribute-variants':
                $variants = ProductAttributeVariant::with('attribute')->whereHas('attribute', function ($q) use ($post) {
                    $q->where('slug', $post->slug);
                })->orderBy('created_at', 'ASC')->get();
                return response()->json(['variants' => $variants], 200);
                break;

            default:
                return response()->json(['status' => 'Unsupported Request.'], 400);
                break;
        }
    }

    /**
     * Stocks Function
     */
    public function stocks()
    {
        $data['activemenu'] = [
            'main' => 'products',
            'sub' => 'stock',
        ];

        if (!\Myhelper::can('view_product_stock')) {
            abort(401);
        }

        return view('dashboard.products.stock', $data);
    }

    public function stockSubmit(Request $post)
    {
        switch ($post->operation) {
            case 'edit':
                $permission = "product_stock_update";

                $rules = [
                    'id' => 'required|exists:product_variants',
                    'price' => 'required|numeric|min:1',
                    'offeredprice' => 'required|numeric|min:1',
                   // 'listingprice' => 'required|numeric',
                    'quantity' => 'required|numeric|min:0',
                ];

                

                if ($post->offeredprice >= $post->price) {
                    return response()->json(['status' => 'The offered price must be less than the product price.'], 400);
                }
                break;

            case 'changestatus':
                $permission = "product_stock_change_status";

                $rules = [
                    'id' => 'required|exists:product_variants',
                ];
                break;

            case 'delete':
                $permission = "product_stock_delete";

                $rules = [
                    'id' => 'required|exists:product_variants',
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
            case 'edit':
                $document = array();
                $document['price'] = $post->price;
                $document['offeredprice'] = $post->offeredprice;
                $document['listingprice'] = $post->listingprice;

                $document['quantity'] = $post->quantity;

                $action = ProductVariant::where('id', $post->id)->update($document);
                break;

            case 'changestatus':
                $variant = ProductVariant::findorfail($post->id);
                if ($variant->status == '1') {
                    $variant->status = '0';
                } else {
                    $variant->status = '1';
                }

                $action = $variant->save();
                break;

            case 'delete':
                $variant = ProductVariant::findorfail($post->id);
                $action = $variant->delete();
                break;
        }

        if ($action) {
            return response()->json(['status' => 'Task successfully completed.'], 200);
        } else {
            return response()->json(['status' => 'Task cannot be completed.'], 400);
        }
    }

    public function getSchemes(Category $category)
    {
        $scheme = $category->scheme;


        return response()->json([
            'scheme' => $scheme
        ]);
    }

    public function updateProduct(ProductMaster $product, Request $request)
    {
        $product->scheme_id = $request->scheme_id;
        $product->save();
        return response()->json([
            'message' => 'Scheme Updated'
        ]);
    }
}
