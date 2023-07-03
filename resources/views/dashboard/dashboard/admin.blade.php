@extends('layouts.app')

@section('content')

    <section class="content-header">
        <h1>
            Dashboard
            <small>Control Panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    @if( Myhelper::hasRole(['superadmin']))
        <section class="content">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['neworders'] }}</h3>
                            <p>New Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-shopping-bag"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                          
                            <h3>{{ $count['todaysales'] }}</h3>
                            <p>Today Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-inr"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['monthsales'] }}</h3>
                            <p>This Month Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['admins'] }}</h3>
                            <p>Total Admins</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-secret"></i>
                        </div>
                        <a href="{{ route('dashboard.members.index', ['type' => 'admin']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>

                
                </div>

            

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['merchants'] }}</h3>
                            <p>Total Merchants</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-tag"></i>
                        </div>
                        <a href="{{ route('dashboard.members.index', ['type' => 'branch']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

            

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['d-boy'] }}</h3>
                            <p>Total Delivery Boys</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-truck"></i>
                        </div>
                        <a href="{{ route('dashboard.members.index', ['type' => 'deliveryboy']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>

                
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['users'] }}</h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="{{ route('dashboard.members.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['todayprofits'] }}</h3>
                            <p>Today's Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <a href="{{ route('dashboard.profits.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['monthprofits'] }}</h3>
                            <p>Monthly Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <a href="{{ route('dashboard.profits.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['today_cancel_order'] }}</h3>
                            <p>Today's Cancel Order</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                        <a href="{{ route('dashboard.cancel.order') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['monthly_cancel_order'] }}</h3>
                            <p>Monthly Cancel Order</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                        <a href="{{ route('dashboard.cancel.order') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['return_order'] }}</h3>
                            <p>Return Order</p>
                        </div>
                        <div class="icon">
                            <<i class="fa fa-arrow-rotate"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            {{-- <div class="row">
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Weekly Sales Report</h3>
                        </div>
                        <div class="box-body">
                            <div class="chart">
                                <canvas id="weekly-report-chart" style="min-height: 300px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Monthly Sales Report</h3>
                        </div>
                        <div class="box-body">
                            <div class="chart">
                                <canvas id="monthly-report-chart" style="min-height: 300px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="row">
                <div class="col-md-8">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Latest Orders</h3>
                        </div>
                        <div class="box-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered m-0">
                                    <thead>
                                        <tr>
                                            <th>Placed On</th>
                                            <th>Order Code</th>
                                            <th class="text-center">Total Items</th>
                                            <th class="text-center">Payable Amount</th>
                                            <th class="text-center">Current Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($latestorders) > 0)
                                            
                                            @foreach ($latestorders as $item)
                                                <tr>
                                                    <td>{{ $item->created_at }}</td>
                                                    <td><a href="{{ route('dashboard.orders.view', ['id' => $item->id]) }}">{{ $item->code }}</a></td>
                                                    <td class="text-center">{{ count($item->order_products) }}</td>
                                                    <td class="text-center"><b>{!! config('app.currency.faicon') !!}{{$item->payable_amount}} </b></td>
                                                    <td class="text-center">
                                                        @switch($item->status)
                                                            @case('paymentinitiated')
                                                                <span class="label">{{ config('orderstatus.options')['paymentinitiated'] }}</span>
                                                                @break
                                                            @case('paymentfailed')
                                                                <span class="label bg-red">{{ config('orderstatus.options')['paymentfailed'] }}</span>
                                                                @break
                                                            @case('received')
                                                                <span class="label bg-yellow">{{ config('orderstatus.options')['received'] }}</span>
                                                                @break
                                                            @case('accepted')
                                                                <span class="label bg-purple">{{ config('orderstatus.options')['accepted'] }}</span>
                                                                @break
                                                            @case('processed')
                                                                <span class="label bg-purple">{{ config('orderstatus.options')['processed'] }}</span>
                                                                @break
                                                            @case('intransit')
                                                                <span class="label bg-navy">{{ config('orderstatus.options')['intransit'] }}</span>
                                                                @break
                                                            @case('outfordelivery')
                                                                <span class="label bg-green disabled">{{ config('orderstatus.options')['outfordelivery'] }}</span>
                                                                @break
                                                            @case('delivered')
                                                                <span class="label bg-green">{{ config('orderstatus.options')['delivered'] }}</span>
                                                                @break
                                                            @case('cancelled')
                                                            @case('returned')
                                                                <span class="label bg-red text-capitalize">{{ @config('orderstatus.options')[$item->status] ?? $item->status }}</span>
                                                                @break
                                                            @default
                                                                <span class="label text-capitalize">{{ @config('orderstatus.options')[$item->status] ?? $item->status }}</span>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center">No Orders Found</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box-footer text-right">
                            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-primary">View All Orders</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Recently Added Products</h3>
                        </div>
                        <div class="box-body p-0">
                            <ul class="products-list product-list-in-box pl-2 pr-2">
                                @foreach ($latestproducts as $item)
                                    <li class="item">
                                        <div class="product-img">
                                            <img src="{{ $item->details->image_path }}" alt="Product Image" class="img-size-50">
                                        </div>
                                        <div class="product-info">
                                            <a href="{{route('dashboard.products.edit', $item->id)}}" class="product-title">{{ $item->details->name }}
                                                <span class="badge bg-red pull-right">
                                                    @if ($item->availability == 'comingsoon')
                                                        Coming Soon
                                                    @else
                                                        {!! config('app.currency.faicon') !!} 
                                                    @endif
                                                </span>
                                            </a>
                                            <span class="product-description">
                                                {{ $item->details->category->name }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach

                            </ul>
                        </div>
                        <div class="box-footer text-center">
                            <a href="{{ route('dashboard.products.index') }}" class="uppercase">View All Products</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    
    @if (session('admin'))
    <div class="mt-5">

        <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
    </div>

    @endif

    {{-- @if( Myhelper::hasRole(['admin']))
        <section class="content">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['neworders'] }}</h3>
                            <p>New Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-shopping-bag"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                        
                            <h3>{{ $count['todaysales'] }}</h3>
                            <p>Today Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-inr"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['monthsales'] }}</h3>
                            <p>This Month Sales</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>


                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['today_cancel_order'] }}</h3>
                            <p>Today's Cancel Order</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['monthly_cancel_order'] }}</h3>
                            <p>Monthly Cancel Order</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-ban"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $count['return_order'] }}</h3>
                            <p>Return Order</p>
                        </div>
                        <div class="icon">
                            <<i class="fa fa-arrow-rotate"></i>
                        </div>
                        <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

        
        

        
        </section>
    @endif --}}

@endsection



@push('script')
    <script src="https://adminlte.io/themes/v3/plugins/chart.js/Chart.min.js"></script>
    {{-- <script>
        $(function() {
            new Chart($('#weekly-report-chart').get(0).getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        /* @foreach ($weeksales as $item) */
                            '{{$item['label']}}',
                        /* @endforeach */
                    ],
                    datasets: [{
                            label: 'This Week Sales',
                            backgroundColor: 'rgba(60,141,188,0.9)',
                            borderColor: 'rgba(60,141,188,0.8)',
                            // pointRadius: true,
                            pointColor: '#3b8bba',
                            pointStrokeColor: 'rgba(60,141,188,1)',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: 'rgba(60,141,188,1)',
                            data: [
                                /* @foreach ($weeksales as $item) */
                                    parseInt('{{$item['currentweek']}}'),
                                /* @endforeach */
                            ]
                        },
                        {
                            label: 'Last Week Sales',
                            backgroundColor: 'rgba(210, 214, 222, 1)',
                            borderColor: 'rgba(210, 214, 222, 1)',
                            // pointRadius: true,
                            pointColor: 'rgba(210, 214, 222, 1)',
                            pointStrokeColor: '#c1c7d1',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: 'rgba(220,220,220,1)',
                            data: [
                                /* @foreach ($weeksales as $item) */
                                parseInt('{{$item['lastweek']}}'),
                                /* @endforeach */
                            ]
                        },
                    ]
                },
            })

            new Chart($('#monthly-report-chart').get(0).getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        /* @foreach ($monthsales as $item) */
                            '{{$item['label']}}',
                        /* @endforeach */
                    ],
                    datasets: [{
                            label: 'This Year',
                            backgroundColor: 'rgba(60,141,188,0.9)',
                            borderColor: 'rgba(60,141,188,0.8)',
                            // pointRadius: true,
                            fill: false,
                            pointColor: '#3b8bba',
                            pointStrokeColor: 'rgba(60,141,188,1)',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: 'rgba(60,141,188,1)',
                            data: [
                                /* @foreach ($monthsales as $item) */
                                    parseInt('{{$item['currentyear']}}'),
                                /* @endforeach */
                            ]
                        },
                        {
                            label: 'Last Year',
                            backgroundColor: 'rgba(210, 214, 222, 1)',
                            borderColor: 'rgba(210, 214, 222, 1)',
                            // pointRadius: true,
                            fill: false,
                            pointColor: 'rgba(210, 214, 222, 1)',
                            pointStrokeColor: '#c1c7d1',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: 'rgba(220,220,220,1)',
                            data: [
                                /* @foreach ($monthsales as $item) */
                                parseInt('{{$item['lastyear']}}'),
                                /* @endforeach */
                            ]
                        },
                    ]
                },
            })
        });
    </script> --}}
@endpush
