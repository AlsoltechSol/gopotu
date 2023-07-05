@php
    if(Myhelper::hasrole(['superadmin', 'admin'])){
        $recievedbooking = \App\Model\Order::whereNotIn('status', ['paymentinitiated', 'paymentfailed', 'delivered', 'cancelled', 'returned'])->count();
        $processingreturnreplaceorders = \App\Model\OrderReturnReplace::whereNotIn('status', ['deliveredtostore', 'rejected'])->count();
    } elseif(Myhelper::hasrole(['branch'])){
        $recievedbooking = \App\Model\Order::where('shop_id', Myhelper::getShop())->whereIn('status', ['received', 'processed', 'accepted'])->count();
        $processingreturnreplaceorders = \App\Model\OrderReturnReplace::whereHas('order', function($q){
            $q->where('shop_id', Myhelper::getShop());
        })->whereNotIn('status', ['deliveredtostore', 'rejected'])->count();
    }else{
        $recievedbooking = 0;
    }
@endphp

<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{Auth::user()->avatar}}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{Auth::user()->name}}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>

            <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'dashboard') ? 'active' : ''}}">
                <a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @if(Myhelper::can(['view_admin', 'view_user', 'view_deliveryboy', 'view_branch']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'members') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-user-circle"></i> <span>Members</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('view_user'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'user') ? 'active' : ''}}"><a href="{{route('dashboard.members.index', ['type' => 'user'])}}"><i class="fa fa-circle-o"></i> Users</a></li>
                        @endif

                        @if(Myhelper::can('view_branch'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'branch') ? 'active' : ''}}"><a href="{{route('dashboard.members.index', ['type' => 'branch'])}}"><i class="fa fa-circle-o"></i> Merchants</a></li>
                        @endif

                        @if(Myhelper::can('view_deliveryboy'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'deliveryboy') ? 'active' : ''}}"><a href="{{route('dashboard.members.index', ['type' => 'deliveryboy'])}}"><i class="fa fa-circle-o"></i> Delivery Boy</a></li>
                        @endif

                        @if(Myhelper::can('view_admin'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'admin') ? 'active' : ''}}"><a href="{{route('dashboard.members.index', ['type' => 'admin'])}}"><i class="fa fa-circle-o"></i> Admins</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::can(['view_category', 'view_brand', 'view_color', 'view_attribute', 'view_app_banner']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'master') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-cog"></i> <span>Master</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('view_category'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'category') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'category'])}}"><i class="fa fa-circle-o"></i> Categories</a></li>
                        @endif

                        @if(Myhelper::can('view_brand'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'brand') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'brand'])}}"><i class="fa fa-circle-o"></i> Brands</a></li>
                        @endif

                        @if(Myhelper::can('view_product_master'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'product') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'product'])}}"><i class="fa fa-circle-o"></i> Products</a></li>
                        @endif

                        @if(Myhelper::can('view_color'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'color') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'color'])}}"><i class="fa fa-circle-o"></i> Colors</a></li>
                        @endif

                        @if(Myhelper::can('view_attribute'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'attribute') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'attribute'])}}"><i class="fa fa-circle-o"></i> Attributes</a></li>
                        @endif

                        @if(Myhelper::can('view_app_banner'))
                            <li class="treeview {{(isset($activemenu['sub']) && in_array($activemenu['sub'], ['top-appbanner','middle-appbanner','footer-appbanner'])) ? 'active' : ''}}">
                                <a href="#"><i class="fa fa-circle-o"></i> App Banners
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'top-appbanner') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'top-appbanner'])}}"><i class="fa fa-circle-o"></i> Top App Banners</a></li>

                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'middle-appbanner') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'middle-appbanner'])}}"><i class="fa fa-circle-o"></i> Middle App Banners</a></li>

                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'footer-appbanner') ? 'active' : ''}}"><a href="{{route('dashboard.master.index', ['type' => 'footer-appbanner'])}}"><i class="fa fa-circle-o"></i> Footer App Banners</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- @if(Myhelper::hasRole(['superadmin', 'admin'])) --}}
                @if(Myhelper::can(['view_store']) || Myhelper::hasRole(['superadmin', 'admin']))
                    <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'stores') ? 'active' : ''}}">
                        <a href="{{route('dashboard.stores.index')}}"><i class="fa fa-store"></i>
                            <span>Stores</span>
                        </a>
                    </li>
                @endif
            {{-- @endif --}}

            {{-- @if(Myhelper::hasRole(['branch'])) --}}
                @if(Myhelper::can(['view_product', 'add_product', 'view_product_stock']))
                    <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'products') ? 'active menu-open' : ''}}">
                        <a href="javascript:void(0);">
                            <i class="fa fa-product-hunt"></i> <span>Mart Products</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @if(Myhelper::can('view_product'))
                                <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'index') ? 'active' : ''}}"><a href="{{route('dashboard.products.index')}}"><i class="fa fa-circle-o"></i> View All</a></li>
                            @endif

                            @if(Myhelper::hasRole(['branch']))
                                @if(Myhelper::can('add_product'))
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'add') ? 'active' : ''}}"><a href="{{route('dashboard.products.library')}}"><i class="fa fa-circle-o"></i> Add New</a></li>
                                @endif

                                @if(Myhelper::can('view_product_stock'))
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'stock') ? 'active' : ''}}"><a href="{{route('dashboard.products.stocks.index')}}"><i class="fa fa-circle-o"></i> Product Stocks</a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif

                @if(Myhelper::can(['view_food', 'add_food']))
                    <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'foods') ? 'active menu-open' : ''}}">
                        <a href="javascript:void(0);">
                            <i class="fa fa-cutlery"></i> <span>Restaurant Foods</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            @if(Myhelper::can('view_food'))
                                <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'index') ? 'active' : ''}}"><a href="{{route('dashboard.foods.index')}}"><i class="fa fa-circle-o"></i> View All</a></li>
                            @endif

                            @if(Myhelper::hasRole(['branch']))
                                @if(Myhelper::can('add_food'))
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'add') ? 'active' : ''}}"><a href="{{route('dashboard.foods.library')}}"><i class="fa fa-circle-o"></i> Add New</a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                @endif
            {{-- @endif --}}

            @if(Myhelper::can(['view_order']))
                <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'orders') ? 'active' : ''}}">
                    <a href="{{route('dashboard.orders.index')}}"><i class="fa fa-shopping-cart"></i>
                        <span>Orders</span>
                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{{$recievedbooking}}</small>
                        </span>
                    </a>
                </li>
            @endif

            @if(Myhelper::can(['view_return_replacement_requests']))
                <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'returnreplacements') ? 'active' : ''}}">
                    <a href="{{route('dashboard.returnreplacements.index')}}"><i class="fa fa-exchange"></i>
                        <span>Return & Replacement</span>
                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{{$processingreturnreplaceorders}}</small>
                        </span>
                    </a>
                </li>
            @endif

            @if(Myhelper::hasRole(['superadmin']))
            <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'foods') ? 'active menu-open' : ''}}">
                <a href="javascript:void(0);">
                    <i class="fa fa-chart-line"></i> <span>Sales Management</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    @if(Myhelper::can('view_food'))
                        <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'index') ? 'active' : ''}}"><a href="{{route('dashboard.profits.index')}}"><i class="fa fa-circle-o"></i> Profits</a></li>
                        <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'index') ? 'active' : ''}}"><a href="{{route('dashboard.cancel.order')}}"><i class="fa fa-circle-o"></i> Cancel Order</a></li>
                    @endif                 
                </ul>
            </li>
        @endif

            @if(Myhelper::can(['fund_tr_action', 'view_payoutrequest', 'branchwallet_statement']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'funds') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-wallet"></i> <span>Wallet Funds</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('fund_tr_action'))
                            {{-- <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'tr') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'tr'])}}"><i class="fa fa-circle-o"></i> Transfer & Return</a></li> --}}

                            <li class="treeview {{(isset($activemenu['sub']) && in_array($activemenu['sub'], ['tr-user', 'tr-branch', 'tr-deliveryboy'])) ? 'active' : ''}}">
                                <a href="#"><i class="fa fa-circle-o"></i> Transfer & Return
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'tr-user') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'tr-user'])}}"><i class="fa fa-circle-o"></i> User</a></li>

                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'tr-branch') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'tr-branch'])}}"><i class="fa fa-circle-o"></i> Merchant</a></li>

                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'tr-deliveryboy') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'tr-deliveryboy'])}}"><i class="fa fa-circle-o"></i> Delivery Boy</a></li>
                                </ul>
                            </li>
                        @endif

                        @if(Myhelper::can(['view_payoutrequest']))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'payoutrequest') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'payoutrequest'])}}"><i class="fa fa-circle-o"></i> Payout Requests</a></li>
                        @endif

                        @if(Myhelper::can(['view_collectionsubmitted']))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'collectionsubmitted') ? 'active' : ''}}"><a href="{{route('dashboard.funds.index', ['type' => 'collectionsubmitted'])}}"><i class="fa fa-circle-o"></i> Collection Submitted</a></li>
                        @endif

                        @if(Myhelper::hasrole('branch') && Myhelper::can('branchwallet_statement'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'branchwallet-statement') ? 'active' : ''}}"><a href="{{route('dashboard.report.index', ['type' => 'branchwallet'])}}"><i class="fa fa-circle-o"></i> Main Wallet Statement</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::hasRole(['branch']))
                @if(Myhelper::can(['shop_setting']))
                    <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'shopsettings') ? 'active' : ''}}">
                        <a href="{{route('dashboard.shopsettings.index')}}"><i class="fa fa-store-alt"></i>
                            <span>Shop Settings</span>
                        </a>
                    </li>
                @endif
            @endif

            @if(Myhelper::can(['view_sales_report', 'view_support_tickets']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'report') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-bar-chart"></i> <span>Reports & Statement</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        {{-- @if(Myhelper::can('view_sales_report'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'sales') ? 'active' : ''}}"><a href="{{route('dashboard.report.index', ['type' => 'sales'])}}"><i class="fa fa-circle-o"></i> Sales Report</a></li>
                        @endif --}}

                        @if(Myhelper::can('view_support_tickets'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'supporttickets') ? 'active' : ''}}"><a href="{{route('dashboard.report.index', ['type' => 'supporttickets'])}}"><i class="fa fa-circle-o"></i> Support Tickets</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::can(['view_coupon']))
                <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'coupon') ? 'active' : ''}}">
                    <a href="{{route('dashboard.coupon.index')}}"><i class="fa fa-percent"></i>
                        <span>Discount Coupons</span>
                    </a>
                </li>
            @endif

            @if(Myhelper::can(['view_scheme']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'resources') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-globe"></i> <span>Resources</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('view_scheme'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'scheme') ? 'active' : ''}}"><a href="{{route('dashboard.resources.index', ['type' => 'scheme'])}}"><i class="fa fa-circle-o"></i> Schemes</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::can(['view_cms', 'view_testimonial', 'view_sociallink', 'view_cancellation_reason']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'cms') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-gears"></i> <span>Content Management</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('view_cms'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'content') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'content'])}}"><i class="fa fa-circle-o"></i> CMS</a></li>
                        @endif

                        @if(Myhelper::can('view_sociallink'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'sociallink') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'sociallink'])}}"><i class="fa fa-circle-o"></i>Social Links</a></li>
                        @endif

                        {{-- @if(Myhelper::can('view_testimonial'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'testimonial') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'testimonial'])}}"><i class="fa fa-circle-o"></i> Testimonials</a></li>
                        @endif --}}

                        @if(Myhelper::can('view_cancellation_reason'))
                            <li class="treeview {{(isset($activemenu['sub']) && in_array($activemenu['sub'], ['cancellationreason-user', 'cancellationreason-branch', 'cancellationreason-deliveryboy'])) ? 'active' : ''}}">
                                <a href="#"><i class="fa fa-circle-o"></i> Order Cancellation Reasons
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'cancellationreason-user') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'cancellationreason-user'])}}"><i class="fa fa-circle-o"></i> User APP</a></li>

                                    {{-- <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'cancellationreason-branch') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'cancellationreason-branch'])}}"><i class="fa fa-circle-o"></i> Merchant APP</a></li> --}}

                                    <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'cancellationreason-deliveryboy') ? 'active' : ''}}"><a href="{{route('dashboard.cms.index', ['type' => 'cancellationreason-deliveryboy'])}}"><i class="fa fa-circle-o"></i> Delivery Boy APP</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::can(['account_notification', 'sms_notification', 'push_notification', 'email_notification']))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'notifications') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-bell"></i> <span>Notifications</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        @if(Myhelper::can('account_notification'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'account') ? 'active' : ''}}"><a href="{{route('dashboard.notifications.index', ['type' => 'account'])}}"><i class="fa fa-circle-o"></i> Account Notification</a></li>
                        @endif

                        @if(Myhelper::can('sms_notification'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'sms') ? 'active' : ''}}"><a href="{{route('dashboard.notifications.index', ['type' => 'sms'])}}"><i class="fa fa-circle-o"></i> SMS Notification</a></li>
                        @endif

                        @if(Myhelper::can('push_notification'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'push') ? 'active' : ''}}"><a href="{{route('dashboard.notifications.index', ['type' => 'push'])}}"><i class="fa fa-circle-o"></i> Push Notification</a></li>
                        @endif

                        @if(Myhelper::can('email_notification'))
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'email') ? 'active' : ''}}"><a href="{{route('dashboard.notifications.index', ['type' => 'email'])}}"><i class="fa fa-circle-o"></i> Email Notification</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::hasrole('superadmin---s'))
                <li class="treeview {{(isset($activemenu['main']) && $activemenu['main'] == 'tools') ? 'active menu-open' : ''}}">
                    <a href="javascript:void(0);">
                        <i class="fa fa-gear"></i> <span>Roles & Permission</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'roles') ? 'active' : ''}}"><a href="{{route('dashboard.tools.roles')}}"><i class="fa fa-circle-o"></i> Roles</a></li>

                        @if(App::environment() === 'local')
                            <li class="{{(isset($activemenu['sub']) && $activemenu['sub'] == 'permissions') ? 'active' : ''}}"><a href="{{route('dashboard.tools.permissions')}}"><i class="fa fa-circle-o"></i> Permissions</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Myhelper::hasrole('superadmin'))
                <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'settings') ? 'active' : ''}}">
                    <a href="{{route('dashboard.settings.index')}}"><i class="fa fa-code"></i>
                        <span>Site Settings</span>
                    </a>
                </li>
            @endif

            {{-- @if(Myhelper::hasrole('branch'))
                <li class="{{(isset($activemenu['main']) && $activemenu['main'] == 'mycommission') ? 'active' : ''}}">
                    <a href="{{route('dashboard.mycommission')}}"><i class="fa fa-coins"></i>
                        <span>My Commission</span>
                    </a>
                </li>
            @endif --}}
        </ul>
    </section>
</aside>
