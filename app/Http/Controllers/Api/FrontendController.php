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
use App\Model\AppBanner;
use App\Model\Category;
use App\Model\CmsContent;
use App\Model\Order;
use App\Model\OrderCancellationReason;
use App\Model\Product;
use App\Model\Shop;
use App\Model\SociallinkMaster;
use App\Model\UserDocument;

class FrontendController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function loadHomePage(Request $request)
    {
        $data = array();

        try {
            $rules = array(
                'type' => 'required|in:mart,restaurant'
            );

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

            // dd($default_address->toArray());


            $nearest_shop_ids = \Myhelper::getAvailableShops($default_address->latitude, $default_address->longitude);

            $nearest_shops = Shop::whereIn('id', $nearest_shop_ids);
            $featured_shops = Shop::where('is_featured', '1')->whereIn('id', $nearest_shop_ids);


            if ($request->has('type') && $request->type) {
                $nearest_shops = $nearest_shops->has('products', '>', 0)->whereHas('products', function ($q) use ($request) {
                    $q->where('type', $request->type);
                    $q->where('status', 1);
                });

                $featured_shops = $nearest_shops->has('products', '>', 0)->whereHas('products', function ($q) use ($request) {
                    $q->where('type', $request->type);
                    $q->where('status', 1);
                });
            }

            switch ($request->type) {
                case 'restaurant':
                case 'mart':
                    $nearest_shops = $nearest_shops->where('online', '1');
                    $featured_shops = $featured_shops->where('online', '1');
                    break;
            }

            $nearest_shops = $nearest_shops->get();
            foreach ($nearest_shops as $key => $shop) {
                $shop->availableproducts = Product::where('shop_id', $shop->id)->where('type', $request->type)->where('status', 1)->count();
                $shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($shop->shop_latitude, $shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
                $shop->avg_rating = \Myhelper::getShopAvgRating($shop->id);
            }

            $featured_shops = $featured_shops->get();
            foreach ($featured_shops as $key => $shop) {
                $shop->availableproducts = Product::where('shop_id', $shop->id)->where('type', $request->type)->where('status', 1)->count();
                $shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($shop->shop_latitude, $shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
                $shop->avg_rating = \Myhelper::getShopAvgRating($shop->id);
            }

            $data['nearest_shops'] = $nearest_shops;
            $data['featured_shops'] = $featured_shops;
            $data['default_address'] = $default_address;
            $data['contact_details'] = config('contact');
            $data['appbanners'] = array(
                'top' => AppBanner::where('position', 'top')->where('type', $request->type)->where('status', '1')->get(),
                'middle' => AppBanner::where('position', 'middle')->where('status', '1')->get(),
            );
            $data['social_links'] = SociallinkMaster::where('status', '1')->get();

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchCategories(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'parent_id' => 'nullable|exists:categories,id',
                'type' => 'required|in:mart,restaurant,service',
                'shop_id' => 'nullable|exists:shops,id',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $categories = Category::where('status', '1')->where('type', $request->type);
            if ($request->has('parent_id') && $request->parent_id != null) {
                $categories->where('parent_id', $request->parent_id);
            } else {
                $categories->where('parent_id', null);
            }

            if ($request->has('shop_id') && $request->shop_id != null) {
                $final_cats = array();
                $available_cats = $categories->get();

                foreach ($available_cats as $key => $category) {
                    $cat_array = [];
                    array_push($cat_array, $category->id);

                    foreach (Category::where('parent_id', $category->id)->where('status', 1)->pluck('id') as $key => $value) {
                        array_push($cat_array, $value);
                    }

                    $products = Product::where('shop_id', $request->shop_id)
                        ->where('type', $request->type)
                        ->where('status', 1)
                        ->whereHas('details', function ($q) use ($cat_array) {
                            $q->whereIn('category_id', $cat_array);
                        })->count();

                    if ($products > 0) {
                        array_push($final_cats, $category->id);
                    }
                }

                $categories = Category::whereIn('id', $final_cats);
            }

            $data['categories'] = $categories->orderBy('name', 'ASC')->get();

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function submitSearch(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'type' => 'required|in:mart,restaurant,service',
                'searchkeyword' => 'required',
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

            $nearest_shop_ids = \Myhelper::getAvailableShops($default_address->latitude, $default_address->longitude);

            /**
             * -------------
             * SEARCH SHOP -
             * -------------
             *  */

            $shops = Shop::whereIn('id', $nearest_shop_ids)->where('status', '1');
            $products = Product::with('details', 'shop', 'product_variants')->whereIn('shop_id', $nearest_shop_ids)->where('status', '1')->whereIn('availability', ['instock', 'comingsoon']);

            if ($request->has('type') && $request->type) {
                switch ($request->type) {
                    case 'restaurant':
                    case 'mart':
                        $shops->where('online', '1');
                        break;
                }

                $shops->has('products', '>', 0)->whereHas('products', function ($q) use ($request) {
                    $q->where('type', $request->type);
                    $q->where('status', 1);
                });

                $products->where('type', $request->type);
            }

            if ($request->has('searchkeyword') && $request->searchkeyword) {
                $shops->where(function ($q) use ($request) {
                    $q->orWhere('shop_name', 'LIKE', '%' . $request->searchkeyword . '%');
                });

                $products->whereHas('details', function ($q) use ($request) {
                    $q->where(function ($qr) use ($request) {
                        $qr->orWhere('name', 'LIKE', '%' . $request->searchkeyword . '%');
                        $qr->orWhere('description', 'LIKE', '%' . $request->searchkeyword . '%');
                        $qr->orWhereHas('category', function ($qry) use ($request) {
                            $qry->where('name', 'LIKE', '%' . $request->searchkeyword . '%');
                        });
                    });
                });
            }

            $shops = $shops->get();
            foreach ($shops as $key => $shop) {
                // $shop->availableproducts = Product::where('shop_id', $shop->id)->where('type', $request->type)->where('status', 1)->count();
                $shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($shop->shop_latitude, $shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
                $shop->avg_rating = \Myhelper::getShopAvgRating($shop->id);
            }

            $products = $products->get();
            foreach ($products as $key => $product) {
                if ($product->shop) {
                    // $product->shop->availableproducts = Product::where('shop_id', $product->shop->id)->where('type', $request->type)->where('status', 1)->count();
                    $product->shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($product->shop->shop_latitude, $product->shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
                    $product->shop->avg_rating = \Myhelper::getShopAvgRating($product->shop->id);
                }
            }

            $data['shops'] = $shops;
            $data['products'] = $products;
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchShopPage(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'shop_id' => 'required|exists:shops,id',
                'type' => 'required|in:mart,restaurant'
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

            $final_cats = array();
            $available_cats = Category::where('status', '1')->where('type', $request->type)->where('is_featured', 1)->get();
            foreach ($available_cats as $key => $category) {
                $cat_array = [];
                array_push($cat_array, $category->id);

                foreach (Category::where('parent_id', $category->id)->where('status', 1)->pluck('id') as $key => $value) {
                    array_push($cat_array, $value);
                }

                $products = Product::where('shop_id', $request->shop_id)
                    ->where('type', $request->type)
                    ->where('status', 1)
                    ->whereHas('details', function ($q) use ($cat_array) {
                        $q->whereIn('category_id', $cat_array);
                    })->count();

                if ($products > 0) {
                    array_push($final_cats, $category->id);
                }
            }

            $topoffered_products = Product::with('details', 'product_variants')
                ->where('shop_id', $request->shop_id)
                ->where('status', '1')
                ->where('top_offer', 1)
                ->where('type', $request->type);

            $shop = Shop::findorfail($request->shop_id);
            $shop->availableproducts = Product::where('shop_id', $shop->id)->where('type', $request->type)->where('status', 1)->count();
            $shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($shop->shop_latitude, $shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
            $shop->avg_rating = \Myhelper::getShopAvgRating($shop->id);
            $shop->deliverable = @\Myhelper::validatePurchaseLocation($default_address->id, $shop->id)->status ?? false;

            $userdoc = UserDocument::where('user_id', $shop->user_id)->first();
            $shop->document = (object) [
                'tradelicense_number' => @$userdoc->tradelicense_number ?? "",
                'fssaireg_number' => @$userdoc->fssaireg_number ?? "",
                'gstin_number' => @$userdoc->gstin_number ?? "",
            ];

            $data['shop'] = $shop;
            $data['featured_categories'] = Category::whereIn('id', $final_cats)->where('parent_id', null)->get();
            $data['topoffered_products'] = $topoffered_products->get();
            $data['appbanners'] = array(
                'top' => AppBanner::where('position', 'top')->where('type', $request->type)->where('status', '1')->get(),
                'middle' => AppBanner::where('position', 'middle')->where('status', '1')->get(),
                'footer' => AppBanner::where('position', 'footer')->where('status', '1')->get(),
            );

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    /***
     * ==========================
     * GLOBAL APP ROUTES >>>>>>>>
     * ==========================
     * ==========================
     */

    public function cmsContents(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'slug' => 'required|exists:cms_contents,slug'
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $data['cmscontent'] = CmsContent::where('slug', $request->slug)->first();

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function cmsSocialLinks(Request $request)
    {
        $data = array();

        try {
            $data['social_links'] = SociallinkMaster::where('status', '1')->get();
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function companyDetails(Request $request)
    {
        $data = array();

        try {
            $data['contact_details'] = config('contact');
            $data['firstorder'] = config('firstorder');

            $data['referral_text'] = "Refer GOPOTU to a friend and get " . ((config('firstorder.parentwallet.type') == 'percentage') ? config('firstorder.parentwallet.value') . "%" :  "flat Rs. " . config('firstorder.parentwallet.value')) . " & your friend will get " . ((config('firstorder.userwallet.type') == 'percentage') ? config('firstorder.userwallet.value') . "%" :  "flat Rs. " . config('firstorder.userwallet.value')) . " as wallet cash back on your friend's first completed order.";

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchAppBanners(Request $request)
    {
        $data = array();
        try {
            $data['appbanners'] = array(
                'top' => AppBanner::where('position', 'top')->where('status', '1')->get(),
                'middle' => AppBanner::where('position', 'middle')->where('status', '1')->get(),
                'footer' => AppBanner::where('position', 'footer')->where('status', '1')->get(),
            );

            return response()->json(['status' => 'success', 'message' => "Success", 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchStates(Request $request)
    {
        $data = array();
        try {
            $data['states'] = \DB::table('state_masters')->orderBy('state_name', 'ASC')->get();

            return response()->json(['status' => 'success', 'message' => "Success", 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function fetchCancellationReasons(Request $request, $usertype)
    {
        $data = array();
        try {
            switch ($usertype) {
                case 'deliveryboy':
                case 'user':
                    $data['reasons'] = OrderCancellationReason::where('usertype', $usertype)->where('status', '1')->orderBy('description', 'ASC')->get();
                    break;

                default:
                    abort(404);
                    break;
            }

            return response()->json(['status' => 'success', 'message' => "Success", 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function getAppSettings(Request $request, $type)
    {
        $data = array();
        try {
            $data = array();

            try {
                $data['contact_details'] = config('contact');

                switch ($type) {
                    case 'user':
                        $data['firstorder'] = config('firstorder');
                        $data['appsettings'] = config('appsettings.userapp');

                        $data['referral_text'] = "Refer GOPOTU to a friend and get " . ((config('firstorder.parentwallet.type') == 'percentage') ? config('firstorder.parentwallet.value') . "%" :  "flat Rs. " . config('firstorder.parentwallet.value')) . " & your friend will get " . ((config('firstorder.userwallet.type') == 'percentage') ? config('firstorder.userwallet.value') . "%" :  "flat Rs. " . config('firstorder.userwallet.value')) . " as wallet cash back on your friend's first completed order.";
                        break;

                    case 'branch':
                        $data['appsettings'] = config('appsettings.branchapp');
                        break;

                    case 'deliveryboy':
                        $data['appsettings'] = config('appsettings.deliveryboyapp');
                        break;

                    default:
                        return response()->json(['status' => 'error', 'message' => "Invalid Request", 'data' => \Myhelper::formatApiResponseData($data)]);
                        break;
                }
                return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
