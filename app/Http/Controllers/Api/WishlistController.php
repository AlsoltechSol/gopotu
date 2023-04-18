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
use App\Model\ShopWishlist;
use App\User;

class WishlistController extends Controller
{
    public function __construct()
    {
        if ("OPTIONS" === $_SERVER['REQUEST_METHOD']) {
            die();
        }
    }

    public function submitWishlist(Request $request)
    {
        $data = array();

        try {
            $_wishlistCount = $this->wishlistCount($request);
            if ($_wishlistCount->status() == 200) {
                $_wishlistCountData = $_wishlistCount->getData();
                if ($_wishlistCountData->status == "success") {
                    $data['wishlistcount'] = $_wishlistCountData->data->wishlistcount;
                }
            }

            $rules = [
                'shop_id' => 'required|exists:shops,id',
                'type' => 'nullable|in:mart,restaurant,service',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $_isexist = ShopWishlist::where('user_id', \Auth::guard('api')->id())->where('shop_id', $request->shop_id)->first();
            if ($_isexist) {
                $action = $_isexist->delete();

                if ($action) {
                    $_wishlistCount = $this->wishlistCount($request);
                    if ($_wishlistCount->status() == 200) {
                        $_wishlistCountData = $_wishlistCount->getData();
                        if ($_wishlistCountData->status == "success") {
                            $data['wishlistcount'] = $_wishlistCountData->data->wishlistcount;
                        }
                    }

                    return response()->json(['status' => 'success', 'message' => 'Shop removed from the wishlist successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Please try again later.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            } else {
                $action = ShopWishlist::updateorcreate(
                    ['user_id' => \Auth::guard('api')->id(), 'shop_id' => $request->shop_id],
                    ['user_id' => \Auth::guard('api')->id(), 'shop_id' => $request->shop_id]
                );

                if ($action) {
                    $_wishlistCount = $this->wishlistCount($request);
                    if ($_wishlistCount->status() == 200) {
                        $_wishlistCountData = $_wishlistCount->getData();
                        if ($_wishlistCountData->status == "success") {
                            $data['wishlistcount'] = $_wishlistCountData->data->wishlistcount;
                        }
                    }

                    return response()->json(['status' => 'success', 'message' => 'Shop added to the wishlist successfully', 'data' => \Myhelper::formatApiResponseData($data)]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Oops!! Something went wrong. Please try again later.', 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function wishlistList(Request $request)
    {
        $data = array();

        try {
            $rules = [
                'page' => 'nullable|numeric',
                'type' => 'nullable|in:mart,restaurant,service',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $default_address = \Myhelper::getDefaultAddress(\Auth::user()->id, null);
            if (!$default_address) {
                return response()->json(['status' => 'addresspending', 'message' => "Please add your address first", 'data' => \Myhelper::formatApiResponseData($data)]);
            }

            $wishlists = ShopWishlist::with('shop')->where('user_id', \Auth::id());

            if ($request->has('type') && $request->type) {
                $wishlists->whereHas('shop', function ($q) use ($request) {
                    $q->whereHas('user', function ($qr) use ($request) {
                        $qr->where('business_category', $request->type);
                    });
                });
            }

            $wishlists = $wishlists->orderBy('created_at', 'DESC');

            /** Pagination */
            if ($request->has('page') && $request->page != null) {
                $data['per_page'] = config('app.pagination_records');
                $data['current_page'] = $request->page;
                $data['total_items'] = $wishlists->count();

                $skip = ($request->page - 1) * config('app.pagination_records');
                $wishlists = $wishlists->skip($skip)->take(config('app.pagination_records'));
            }

            $wishlists = $wishlists->get();

            foreach ($wishlists as $key => $wishlist) {
                if ($wishlist->shop) {
                    $wishlist->shop->availableproducts = Product::where('shop_id', $wishlist->shop->id)->where('type', $request->type)->where('status', 1)->count();
                    $wishlist->shop->distanceaway = \Myhelper::showTwoDecimalNumber(\Myhelper::locationDistance($wishlist->shop->shop_latitude, $wishlist->shop->shop_longitude, $default_address->latitude, $default_address->longitude, 'km'));
                    $wishlist->shop->avg_rating = \Myhelper::getShopAvgRating($wishlist->shop->id);

                    $branch = User::find($wishlist->shop->user_id);
                    if ($branch) {
                        $wishlist->shop->business_category = $branch->business_category;
                    }
                }
            }

            $data['wishlists'] = $wishlists;

            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }

    public function wishlistCount(Request $request)
    {
        $data = array();
        try {
            $rules = [
                'type' => 'nullable|in:mart,restaurant,service',
            ];

            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    return response()->json(['status' => 'error', 'message' => $value[0], 'data' => \Myhelper::formatApiResponseData($data)]);
                }
            }

            $wishlists = ShopWishlist::where('user_id', \Auth::guard('api')->id());

            if ($request->has('type') && $request->type) {
                $wishlists->whereHas('shop', function ($q) use ($request) {
                    $q->whereHas('user', function ($qr) use ($request) {
                        $qr->where('business_category', $request->type);
                    });
                });
            }

            $data['wishlistcount'] = $wishlists->count();
            return response()->json(['status' => 'success', 'message' => 'Success', 'data' => \Myhelper::formatApiResponseData($data)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'data' => \Myhelper::formatApiResponseData($data)]);
        }
    }
}
