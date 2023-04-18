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

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-lg-6 col-12">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['neworders'] }}</h3>
                                <p>New Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-bag"></i>
                            </div>
                            <a href="{{route('dashboard.orders.index')}}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-6 col-12">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['totaldelivered'] }}</h3>
                                <p>Total Delivered</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-truck"></i>
                            </div>
                            <a href="{{route('dashboard.orders.index')}}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Weekly Delivered Report</h3>
                            </div>
                            <div class="box-body">
                                <div class="chart">
                                    <canvas id="weekly-delivered-chart" style="min-height: 300px"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
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
                                            @foreach ($latestorders as $item)
                                                <tr>
                                                    <td>{{ $item->created_at }}</td>
                                                    <td><a href="{{ route('dashboard.orders.view', ['id' => $item->id]) }}">{{ $item->code }}</a></td>
                                                    <td class="text-center">{{ count($item->order_products) }}</td>
                                                    <td class="text-center">
                                                        @if($item->payment_mode == 'online')
                                                            <span class="badge bg-blue">PAID</span>
                                                        @elseif($item->payment_mode == 'cash')
                                                            <b>{!! config('app.currency.faicon') !!}{{$item->payable_amount}} </b>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @switch($item->status)
                                                            @case('paymentinitiated')
                                                                <span class="label">Payment Initiated</span>
                                                                @break
                                                            @case('paymentfailed')
                                                                <span class="label bg-red">Payment Failed</span>
                                                                @break
                                                            @case('received')
                                                                <span class="label bg-yellow">Order Placed</span>
                                                                @break
                                                            @case('accepted')
                                                                <span class="label bg-purple">Order Accepted</span>
                                                                @break
                                                            @case('intransit')
                                                                <span class="label bg-navy">Order In-Progress</span>
                                                                @break
                                                            @case('outfordelivery')
                                                                <span class="label bg-green disabled">Out for Delivery</span>
                                                                @break
                                                            @case('delivered')
                                                                <span class="label bg-green">Delivered</span>
                                                                @break
                                                            @case('cancelled')
                                                            @case('returned')
                                                                <span class="label bg-red text-capitalize">{{$item->status}}</span>
                                                                @break
                                                            @default
                                                                <span class="label text-capitalize">{{$item->status}}</span>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer text-right">
                                <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-primary">View All Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection


@push('script')
    <script src="https://adminlte.io/themes/v3/plugins/chart.js/Chart.min.js"></script>
    <script>
        $(function() {
            new Chart($('#weekly-delivered-chart').get(0).getContext('2d'), {
                type: 'line',
                data: {
                    labels: [
                        /* @foreach ($weeklydelivered as $item) */
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
                                /* @foreach ($weeklydelivered as $item) */
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
                                /* @foreach ($weeklydelivered as $item) */
                                parseInt('{{$item['lastweek']}}'),
                                /* @endforeach */
                            ]
                        },
                    ]
                },
            })
        });
    </script>
@endpush
