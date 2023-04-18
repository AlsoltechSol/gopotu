<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PagesController@index')->name('index');
Route::get('app/share', 'PagesController@appShare')->name('appshare');

Route::get('invoice/{type}/{id?}', 'InvoicesCotntroller@generate')->name('invoice.order');

Route::any('orderpayment/init', 'OrderPaymentController@initOrderPayment')->name('orderpay.initpayment');
Route::any('orderpayment/processed', 'OrderPaymentController@processOrderPayment')->name('orderpay.processedpayment');
Route::any('orderpayment/success', 'OrderPaymentController@successOrderPayment')->name('orderpay.successpayment');
Route::any('orderpayment/failed', 'OrderPaymentController@failedOrderPayment')->name('orderpay.failedpayment');
Route::any('orderpayment/redirect', 'OrderPaymentController@redirectPayment')->name('orderpay.redirectpayment');

Route::any('cashorderpayment/init', 'CashOrderPaymentController@initOrderPayment')->name('cashorderpay.initpayment');
Route::any('cashorderpayment/processed', 'CashOrderPaymentController@processOrderPayment')->name('cashorderpay.processedpayment');
Route::any('cashorderpayment/success', 'CashOrderPaymentController@successOrderPayment')->name('cashorderpay.successpayment');
Route::any('cashorderpayment/failed', 'CashOrderPaymentController@failedOrderPayment')->name('cashorderpay.failedpayment');

Route::get('/dashboard/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login/submit', 'Auth\LoginController@login')->name('login.submit');
Route::any('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::prefix('/dashboard')->name('dashboard.')->namespace('Dashboard')->middleware('auth', 'checkuser', 'checkrole:superadmin|admin|deliveryboy|branch')->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/profile/{id?}', 'HomeController@profile')->name('profile');
    Route::post('/profile', 'HomeController@updateProfile')->name('profile');
    Route::get('/mycommission', 'HomeController@mycommission')->middleware('auth', 'checkuser', 'checkrole:branch')->name('mycommission');

    Route::any('/common/fetchdata/{type?}/{fetch?}/{id?}', 'CommonController@fetchdata')->name('fetchdata');

    //== Members Routes == //
    Route::prefix('/members')->name('members.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/index/{type}', 'MembersController@index')->name('index');
        Route::get('/add/{type}', 'MembersController@add')->name('add');
        Route::post('/create/{type}', 'MembersController@create')->name('create');
        Route::post('/change-action/submit', 'MembersController@changeaction')->name('changeaction');
        Route::get('/permissions/{id?}', 'MembersController@permission')->name('permission');
        Route::post('/permissions/submit', 'MembersController@permissionsubmit')->name('permissionsubmit');
    });

    //== Shop Settings Routes == //
    Route::prefix('/stores')->name('stores.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/index', 'StoreController@index')->name('index');
        Route::post('/submit', 'StoreController@submit')->name('submit');
    });

    //== Master Routes == //
    Route::prefix('/master')->name('master.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/{type}/index/{value?}', 'MasterController@index')->name('index');
        Route::get('/{type}/add', 'MasterController@add')->name('add');
        Route::get('/{type}/edit/{id?}', 'MasterController@edit')->name('edit');
        Route::post('/submit', 'MasterController@submit')->name('submit');
        Route::post('/ajax', 'MasterController@ajaxMethods')->name('ajax');
    });

    //== Product Routes == //
    Route::prefix('/products')->name('products.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/index', 'ProductsController@index')->name('index');
        Route::get('/library', 'ProductsController@library')->name('library');
        Route::get('/add/{master_id?}', 'ProductsController@add')->name('add');
        Route::get('/edit/{id?}', 'ProductsController@edit')->name('edit');
        Route::post('/ajax-methods', 'ProductsController@ajaxMethods')->name('ajax');
        Route::post('/submit', 'ProductsController@submit')->name('submit');
        Route::get('/view/{id?}', 'ProductsController@view')->name('view');

        Route::get('/stocks', 'ProductsController@stocks')->name('stocks.index');
        Route::post('/stocks/submit', 'ProductsController@stockSubmit')->name('stock.submit');
      
    });

    //== Food Routes == //
    Route::prefix('/foods')->name('foods.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/index', 'FoodsController@index')->name('index');
        Route::get('/library', 'FoodsController@library')->name('library');

        Route::get('/add/{master_id?}', 'FoodsController@add')->name('add');
        Route::get('/edit/{id?}', 'FoodsController@edit')->name('edit');
        Route::post('/ajax-methods', 'FoodsController@ajaxMethods')->name('ajax');
        Route::post('/submit', 'FoodsController@submit')->name('submit');
        Route::get('/view/{id?}', 'FoodsController@view')->name('view');
    });

    //== Order Routes == //
    Route::prefix('/orders')->name('orders.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/index', 'OrdersController@index')->name('index');
        Route::post('/update', 'OrdersController@update')->name('update');
        Route::get('/view/{id?}', 'OrdersController@view')->name('view');
        Route::post('/ajax', 'OrdersController@ajax')->name('ajax');
    });

    //== Return Replacement Routes == //
    Route::prefix('/return-replacements')->name('returnreplacements.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/index', 'ReturnReplacementsController@index')->name('index');
        Route::get('/create', 'ReturnReplacementsController@create')->name('create');
        Route::post('/submit', 'ReturnReplacementsController@submit')->name('submit');
        Route::post('/ajax', 'ReturnReplacementsController@ajax')->name('ajax');
    });

    //== Funds Routes == //
    Route::prefix('/funds')->name('funds.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/{type}/index/{value?}', 'FundsController@index')->name('index');
        Route::post('submit', 'FundsController@submit')->name('submit');
    });

    //== Reports Routes == //
    Route::prefix('/report')->name('report.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/{type}/index/{value?}', 'ReportsController@index')->name('index');
        Route::post('/update', 'ReportsController@update')->name('update');
    });

    //== Shop Settings Routes == //
    Route::prefix('/shop-settings')->name('shopsettings.')->middleware('checkrole:superadmin|admin|branch')->group(function () {
        Route::get('/index/{shop_id?}', 'ShopSettingsController@index')->name('index');
        Route::post('/submit', 'ShopSettingsController@submit')->name('submit');
        Route::post('/change-onlinestatus', 'ShopSettingsController@changeOnlineStatus')->name('changeonlinestatus');
    });

    //== Coupon Routes == //
    Route::prefix('/coupon')->name('coupon.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/index', 'CouponsController@index')->name('index');
        Route::post('/submit', 'CouponsController@submit')->name('submit');
    });

    //== Resources Routes == //
    Route::prefix('/resources')->name('resources.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/{type}/index/{value?}', 'ResourcesController@index')->name('index');
        Route::post('submit', 'ResourcesController@submit')->name('submit');
    });

    //== CMS Routes == //
    Route::prefix('/cms')->name('cms.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/{type}', 'CmsController@index')->name('index');
        Route::get('/{type}/edit/{id?}', 'CmsController@edit')->name('edit');
        Route::post('/content/submit', 'CmsController@submitcms')->name('submitcms');
    });

    //== Notification Routes == //
    Route::prefix('/notifications')->name('notifications.')->middleware('checkrole:superadmin|admin')->group(function () {
        Route::get('/index/{type}', 'NotificationsController@index')->name('index');
        Route::post('/submit', 'NotificationsController@submit')->name('submit');
    });

    //== Tools Routes == //
    Route::prefix('/tools')->name('tools.')->middleware('checkrole:superadmin')->group(function () {
        Route::get('/roles', 'ToolsController@roles')->name('roles');
        Route::post('/roles/submit', 'ToolsController@submitrole')->name('submitrole');
        Route::get('/permissions', 'ToolsController@permissions')->name('permissions');
        Route::post('/permissions/submit', 'ToolsController@submitpermission')->name('submitpermission');
        Route::get('/role/permissions/{role_id?}', 'ToolsController@rolepermissions')->name('rolepermissions');
        Route::post('/role/permissions/submit', 'ToolsController@rolepermissionssubmit')->name('rolepermissions.submit');
    });

    //== Settings Routes == //
    Route::prefix('/settings')->name('settings.')->middleware('checkrole:superadmin')->group(function () {
        Route::get('/index', 'SettingsController@index')->name('index');
        Route::post('/submit', 'SettingsController@submit')->name('submit');
    });

    Route::prefix('/debug')->name('debug.')->middleware('checkrole:superadmin')->group(function () {
        Route::get('/checkpaytmstatus', 'DebugController@checkpaytmstatus')->name('checkpaytmstatus');
        Route::get('/sendpushnotification', 'DebugController@sendpushnotification')->name('sendpushnotification');
        Route::get('/syncguestwithuser', 'DebugController@syncguestwithuser')->name('syncguestwithuser');
        Route::get('/checkorderstatuslog', 'DebugController@checkorderstatuslog')->name('checkorderstatuslog');
        Route::get('/generatereferralcode', 'DebugController@generatereferralcode')->name('generatereferralcode');
    });
});

Route::get('/category-schemes/{category}', [App\Http\Controllers\Dashboard\ProductsController::class, 'getSchemes']);

Route::get('/{any}', 'PagesController@cmsPages')->where('any', '.*')->name('pages.cms');

