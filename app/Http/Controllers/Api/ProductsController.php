<?php

namespace App\Http\Controllers\Api;

date_default_timezone_set('Asia/Kolkata');
/*
 * Add error_reporting to track error in code
 */
error_reporting(E_ALL);

header('Content-Type:application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: *");
/*
 * Additional header for app
 */
header('Content-Type:application/json');

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Category;
use App\Model\ProductVariant;

class ProductsController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function browse(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'page' => 'nullable|numeric',
                'category_id' => 'nullable',
                'brand_id' => 'nullable',
                'searchkey' => 'nullable',
                'shop_id' => 'nullable',
                'type' => 'nullable|in:mart,restaurant'
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $products = Product::with('details', 'product_variants', 'shop')->where('status', '1')->where('master_status', '1')->orderBy('priority','desc');
            //return $products;
            /** Filter By Type */
            if ($request->has('type') && $request->type != null) {
                $products->where('type', $request->type);
            }

            /** Search by keyword */
            if ($request->has('searchkey') && $request->searchkey != null) {
                $request['datasearchcolumns'] = ['details.name'];

                $products->whereIn('availability', ['instock', 'comingsoon']);

                $products->where(function ($q) use ($request) {
                    foreach ($request->datasearchcolumns as $value) {
                        if (strpos($value, '.') !== false) {
                            $tempattr = explode('.', $value);
                            $q->orWhereHas($tempattr[0], function ($qr) use ($tempattr, $request) {
                                $qr->where($tempattr[1], 'like', '%' . $request->searchkey . '%');
                            });
                        } else {
                            $q->orWhere($value, 'like', $request->searchkey . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchkey . '%');
                            $q->orWhere($value, 'like', '%' . $request->searchkey);
                        }
                    }
                });
            }

            /** Filter By Shops */
            if ($request->has('shop_id') && $request->shop_id != null) {
                if (is_array($request->shop_id)) {
                    $products->whereIn('shop_id', $request->shop_id);
                } else {
                    $products->where('shop_id', $request->shop_id);
                }
            }

            /** Filter By Categories */
            // if ($request->has('category_id') && $request->category_id != null) {
            //     if (is_array($request->category_id)) {
            //         $products->whereIn('category_id', $request->category_id);
            //     } else {
            //         $products->where('category_id', $request->category_id);
            //     }
            // }

            /** Filter By Categories */
            if ($request->has('category_id') && $request->category_id != null) {
                $cat_array = [];

                if (is_array($request->category_id)) {
                    foreach ($request->category_id as $key => $category_id) {
                        array_push($cat_array, $category_id);

                        foreach (Category::where('parent_id', $category_id)->where('status', 1)->pluck('id') as $key => $value) {
                            array_push($cat_array, $value);
                        }
                    }
                } else {
                    array_push($cat_array, $request->category_id);

                    foreach (Category::where('parent_id', $request->category_id)->where('status', 1)->pluck('id') as $key => $value) {
                        array_push($cat_array, $value);
                    }
                }

                $products->whereHas('details', function ($q) use ($cat_array) {
                    $q->whereIn('category_id', $cat_array);
                });
            }

            /** Filter By Brands */
            if ($request->has('brand_id') && $request->brand_id != null) {
                if (is_array($request->brand_id)) {
                    $products->whereIn('brand_id', $request->brand_id);
                } else {
                    $products->where('brand_id', $request->brand_id);
                }
            }

            // $products = $products->orderBy('name', 'ASC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $products->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $products = $products->skip($skip)->take(config('app.pagination_records'));
            }

            $products = $products->get();

            $default_address = \Myhelper::getDefaultAddress($request->user_id, $request->guest_id);
            if (!$default_address) {
                return response()->json(['status' => 'addresspending', 'message' => "Please add your address first", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            foreach ($products as $key => $product) {
                $product->deliverable = @\Myhelper::validatePurchaseLocation($default_address->id, $product->shop_id)->status ?? false;
            }

            $data['products'] = $products;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function details(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'product_id' => 'required|exists:products,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $default_address = \Myhelper::getDefaultAddress($request->user_id, $request->guest_id);
            if (!$default_address) {
                return response()->json(['status' => 'addresspending', 'message' => "Please add your address first", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $product = Product::with('details', 'product_variants')->where('id', $request->product_id)->where('status', '1')->first();
            if (!$product) {
                return response()->json(['status' => 'error', 'message' => 'Product not available currently', 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $product_images = array();
            if ($product->details->image_path != null) {
                array_push($product_images, $product->details->image_path);
            }

            foreach ($product->details->gallery_images as $key => $image) {
                array_push($product_images, $image->image_path);
            }

            $product->details->product_images = $product_images;
            $product->deliverable = @\Myhelper::validatePurchaseLocation($default_address->id, $product->shop_id)->status ?? false;

            $related_products = [];
            if ($product) {
                $related_products = Product::with('details', 'product_variants')
                    ->where('id', '!=', $product->id)
                    ->where('status', 1)
                    ->where('shop_id', $product->shop_id)
                    ->whereHas('details', function ($q) use ($product) {
                        $q->where('category_id', $product->details->category_id);
                    })->get();
            }

            $data['related_products'] = $related_products;
            $data['product'] = $product;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function variantDetails(Request $request)
    {
        $data = array();
        try {
            if ($request->has('variant_id') && $request->variant_id != null) {
                $rules = [
                    'variant_id' => 'required|exists:product_variants,id',
                ];

                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }

                $product_variant = ProductVariant::where('id', $request->variant_id)->first();
                if (!$product_variant) {
                    return response()->json(['status' => 'error', 'message' => 'Selected variant is invalid', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } else {
                $rules = [
                    'product_id' => 'required|exists:products,id',
                    'variant' => 'nullable',
                    'color' => 'nullable',
                ];

                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                    }
                }

                $product = Product::where('id', $request->product_id)->where('status', '1')->first();
                if (!$product) {
                    return response()->json(['status' => 'error', 'message' => 'Product not available currently', 'data' => \Myhelper::formatApiResponseData($data)]);
                }

                if (!$request->has('variant')) {
                    $request['variant'] = null;
                }

                if (!$request->has('color')) {
                    $request['color'] = null;
                }

                $product_variant = ProductVariant::where('product_id', $product->id)->where('variant', $request->variant)->where('color', $request->color)->first();
                if (!$product_variant) {
                    return response()->json(['status' => 'error', 'message' => 'The selected variant is currently unavailable', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $product_variant->continue = true;

            if ($product_variant->status != '1' && $product_variant->deleted_at != null) {
                $product_variant->continue = false;
            }

            // $data['product'] = $product;
            $data['variant_details'] = $product_variant;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
