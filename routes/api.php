<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('callback/paytmorderpayment', 'CallbackController@paytmorderpayment')->name('callback.paytmorderpayment');

Route::any('scheduler/assignriderfororder', 'SchedulerController@assignRiderForOrder')->name('scheduler.assignriderfororder');
Route::any('scheduler/estimatedeliverytime', 'SchedulerController@estimateDeliveryTime')->name('scheduler.estimatedeliverytime');

Route::any('auth/login', 'Api\AuthController@login');
Route::any('auth/send-otp', 'Api\AuthController@sendOtp');
Route::any('auth/otp-login', 'Api\AuthController@otpLogin');
Route::any('auth/register', 'Api\AuthController@register');
Route::any('auth/signup', 'Api\AuthController@register');
Route::any('auth/voktoesache', 'Api\AuthController@register');
Route::any('auth/account', 'Api\AuthController@getAccountDetails')->middleware('auth:api');
Route::any('auth/password/reset', 'Api\AuthController@resetPassword');
Route::any('auth/logout', 'Api\AuthController@logout');

Route::any('guest/login', 'Api\GuestController@login');

Route::any('otp/{type}/verify', 'Api\OtpVerificationController@verify');
Route::any('otp/{type}/resend', 'Api\OtpVerificationController@resend');

Route::any('user/notifications', 'Api\UserController@notifications')->middleware('auth:api');
Route::any('user/update/{type}', 'Api\UserController@update')->middleware('auth:api');
Route::any('user/address/{type}', 'Api\UserController@addressSubmit')->middleware('bothapiguest');

Route::any('homepage/load', 'Api\FrontendController@loadHomePage')->middleware('bothapiguest');
Route::any('shoppage/load', 'Api\FrontendController@fetchShopPage')->middleware('bothapiguest');
Route::any('search/submit', 'Api\FrontendController@submitSearch')->middleware('bothapiguest');
Route::any('categories/fetch', 'Api\FrontendController@fetchCategories');
Route::any('states/fetch', 'Api\FrontendController@fetchStates');
Route::any('{usertype}/cancellation-reasons/fetch', 'Api\FrontendController@fetchCancellationReasons');

Route::any('products/browse', 'Api\ProductsController@browse')->middleware('bothapiguest');
Route::any('product/details', 'Api\ProductsController@details')->middleware('bothapiguest');
Route::any('product/variant-details', 'Api\ProductsController@variantDetails');

Route::any('cart/add', 'Api\CartController@addToCart')->middleware('bothapiguest');
Route::any('cart/remove', 'Api\CartController@removeFromCart')->middleware('bothapiguest');
Route::any('cart/list', 'Api\CartController@cartList')->middleware('bothapiguest');
Route::any('cart/count', 'Api\CartController@cartCount')->middleware('bothapiguest');
Route::any('cart/coupon-add', 'Api\CartController@addCoupon')->middleware('bothapiguest');

Route::middleware('auth:api', 'checkuser', 'checkrole:user')->group(function () {
    Route::any('wishlist/submit', 'Api\WishlistController@submitWishlist')->middleware('auth:api');
    Route::any('wishlist/list', 'Api\WishlistController@wishlistList')->middleware('auth:api');
    Route::any('wishlist/count', 'Api\WishlistController@wishlistCount')->middleware('auth:api');

    Route::any('order/create', 'Api\CheckoutController@create')->middleware('auth:api');
    Route::any('order/fetch', 'Api\CheckoutController@fetch')->middleware('auth:api');
    Route::any('order/details', 'Api\CheckoutController@details')->middleware('auth:api');
    Route::any('order/return-replacement/fetch', 'Api\CheckoutController@fetchReturnReplacements')->middleware('auth:api');
    Route::any('order/return-replacement/details', 'Api\CheckoutController@returnReplacementDetails')->middleware('auth:api');
    Route::any('order/review-submit', 'Api\CheckoutController@submitReview')->middleware('auth:api');
    Route::any('order/cancel-submit', 'Api\CheckoutController@cancelSubmit')->middleware('auth:api');
    Route::any('order/cash-order/initiate-payment', 'Api\CheckoutController@initiatePaymentforCashOrder')->middleware('auth:api');
    Route::any('order/details/timer', 'Api\CheckoutController@timerDetails')->middleware('auth:api');
    Route::get('/user-return-replace-items', [App\Http\Controllers\Api\CheckoutController::class, 'getItems']);

    // Route::any('order/payment-callback', 'Api\CheckoutController@orderPaymentCallback')->middleware('auth:api');
    // Route::any('order/cash-order/initiate-payment', 'Api\CheckoutController@initiatePaymentforCashOrder')->middleware('auth:api');
    // Route::any('order/cash-order/payment-callback', 'Api\CheckoutController@paymentCallbackforCashOrder')->middleware('auth:api');

    Route::any('wallet/statement/{type}', 'Api\WalletsController@fetchStatement');
    Route::any('wallet/dashboard/{type}', 'Api\WalletsController@getDashboard');
    Route::post('name-update', 'Api\AuthController@nameUpdate');
});

Route::any('cms/contents', 'Api\FrontendController@cmsContents');
Route::any('cms/sociallinks', 'Api\FrontendController@cmsSocialLinks');
Route::any('company/details', 'Api\FrontendController@companyDetails');
Route::any('app/banners', 'Api\FrontendController@fetchAppBanners');
Route::any('app/{type}/settings', 'Api\FrontendController@getAppSettings');

Route::any('support-ticket/fetch-subjects', 'Api\SupportTicketController@fetchSubjects');
Route::any('support-ticket/submit', 'Api\SupportTicketController@submit');

Route::prefix('/branch')->namespace('Api\Branch')->group(function () {
    Route::any('auth/login', 'AuthController@login');
    Route::any('auth/logout', 'AuthController@logout');

    Route::middleware('auth:api', 'checkuser', 'checkrole:branch')->group(function () {
        Route::any('auth/account', 'AuthController@getAccountDetails');
        Route::any('account/update/{type}', 'AccountController@update');

        Route::any('order/fetch', 'OrdersController@fetch');
        Route::any('order/details', 'OrdersController@details');
        Route::any('order/{type}/update', 'OrdersController@update');
        Route::any('order/return-replacement/fetch', 'OrdersController@fetchReturnReplacements')->middleware('auth:api');
        Route::any('order/return-replacement/details', 'OrdersController@returnReplacementDetails')->middleware('auth:api');
    });
});

Route::prefix('/deliveryboy')->namespace('Api\Deliveryboy')->group(function () {
    Route::any('auth/login', 'AuthController@login');
    Route::any('auth/logout', 'AuthController@logout');

    Route::middleware('auth:api', 'checkuser', 'checkrole:deliveryboy')->group(function () {
        Route::any('auth/account', 'AuthController@getAccountDetails');
        Route::any('account/update/{type}', 'AccountController@update');

        Route::any('order/running', 'OrdersController@running');
        Route::any('order/fetch', 'OrdersController@fetch');
        Route::any('order/details', 'OrdersController@details');
        Route::any('order/{type}/update', 'OrdersController@update');

        Route::any('returnreplace/fetch', 'ReturnReplaceController@fetch');
        Route::any('returnreplace/details', 'ReturnReplaceController@details');
        Route::any('returnreplace/{type}/update', 'ReturnReplaceController@update');

        Route::any('wallet/statement/{type}', 'WalletsController@fetchStatement');
        Route::any('wallet/request/{type}', 'WalletsController@fetchRequests');
        Route::any('wallet/request/{type}/submit', 'WalletsController@submitRequest');
        Route::any('wallet/dashboard/{type}', 'WalletsController@getDashboard');
    });
});

Route::any('/{any}', function () {
    return response()->json(['status' => 'error', 'message' => "The API requested is not found", 'data' => \Myhelper::formatApiResponseData([])]);
})->where('any', '.*');
