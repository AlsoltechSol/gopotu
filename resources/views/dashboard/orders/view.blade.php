@section('pageheader', 'Order #' . $order->code)
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Order Management
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="{{route('dashboard.orders.index')}}">Orders Management</li>
            <li class="active">{{$order->code}}</li>
        </ol>
    </section>

    <br>

    <section class="invoice" style="margin: 10px 15px;">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="page-header">
                    <i class="fa fa-store-alt"></i> GOPOTU
                    <small class="pull-right">Date: {{$order->created_at}}</small>
                </h2>
            </div>
        </div>

        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                From
                <address>
                    <strong>{{ $order->shop->shop_name }}</strong><br>
                    {{ $order->shop->shop_address->address_line1 ? $order->shop->shop_address->address_line1.',' : '' }}
                    {{ $order->shop->shop_address->address_line2 ? $order->shop->shop_address->address_line2.',' : '' }}
                    {{ $order->shop->shop_address->postal_code ? $order->shop->shop_address->postal_code.',' : '' }}
                    {{ $order->shop->shop_address->city ? $order->shop->shop_address->city.',' : '' }}
                    {{ $order->shop->shop_address->state ? $order->shop->shop_address->state : '' }}<br>
                    Phone: {{ $order->shop->shop_mobile }}<br>
                    Email: {{ $order->shop->shop_email }}
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                To
                <address>
                    <strong>{{ $order->cust_name }}</strong><br>
                    {{ $order->cust_address->address_line1 ? $order->cust_address->address_line1.',' : '' }}
                    {{ $order->cust_address->address_line2 ? $order->cust_address->address_line2.',' : '' }}
                    {{ $order->cust_address->postal_code ? $order->cust_address->postal_code.',' : '' }}
                    {{ $order->cust_address->city ? $order->cust_address->city.',' : '' }}
                    {{ $order->cust_address->state ? $order->cust_address->state : '' }}<br>
                    Phone: {{ $order->cust_mobile }}<br>
                    Email: {{ $order->user->email }}
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                {{-- <a href="{{route('invoice.order', ['order_id' => encrypt($order->id)])}}">Download</a> --}}

                Order Code: <b> #{{$order->code}}</b><br>
                Payment Mode: <b class="text-uppercase">{{$order->payment_mode}}</b><br>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Qty</th>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Unit Price</th>
                            <th>Gross Amount</th>
                            <th>Taxable Amount</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th>IGST</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->order_products as $item)
                      
                            <tr>
                                <td>{{$item->quantity}}</td>
                                

                                <td>
                                    {{$item->product->details->name}}
                                    @if($item->variant_selected->color_name)
                                        &nbsp;| Color: {{$item->variant_selected->color_name}}
                                    @endif

                                    @if($item->variant_selected->variant_name)
                                        &nbsp;| {{$item->variant_selected->variant_name}}
                                    @endif
                                </td>

                                <td>
                                    <img src="{{$item->product->details->image_path}}" height="50px" alt="">
                                    
                                 
                                </td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$item->price, 2, '.', '') }}</td>

                                @php
                                    $grossamount = round($item->sub_total, 2);
                                    $tax_total = round($item->tax_total, 2);

                                    $shop_tin = $item->shop_tin;

                                    $cust_state = DB::table('state_masters')->where('state_code', @$order->cust_address->state)->first();
                                    $cust_tin = @$cust_state->tin_no ?? null;

                                    $taxableamount = 0;
                                    $cgst = 0;
                                    $sgst = 0;
                                    $igst = 0;
                                    $totalaftertax = 0;

                                    if ($shop_tin && $cust_tin) {
                                        $taxableamount = round($grossamount - $tax_total, 2);
                                        if ($shop_tin == $cust_tin) {
                                            $cgst = round($tax_total / 2, 2);
                                            $sgst = round($tax_total / 2, 2);
                                        } else{
                                            $igst = round($tax_total, 2);
                                        }
                                    }

                                    $totalaftertax = ($taxableamount + $cgst + $sgst + $igst);
                                @endphp

                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($grossamount), 2, '.', '') }}</td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($taxableamount), 2, '.', '') }}</td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($cgst), 2, '.', '') }}</td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($sgst), 2, '.', '') }}</td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($igst), 2, '.', '') }}</td>
                                <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)($totalaftertax), 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-6">
                <table class="table table-bordered">
                    <tr class="">
                        <td>Current Status</td>
                        <td>
                            <b>{{ @config('orderstatus.options')[$order->status] ?? $order->status }}</b>
                        </td>
                    </tr>

                    @if (Myhelper::hasRole(['superadmin', 'admin']) && $order->status == 'cancelled' && $order->user_cancel_reason != null)
                        <tr class="no-print">
                            <td>Cancellation Reason</td>
                            <td>
                                {{ $order->user_cancel_reason }}
                            </td>
                        </tr>
                    @endif

                    @if($order->deliveryboy != null)
                        <tr class="no-print">
                            <td>Delivery Boy</td>
                            <td>
                                <b>{{ $order->deliveryboy->name }}</b> ({{ $order->deliveryboy->mobile }})
                            </td>
                        </tr>
                    @endif

                    @if($order->rating != null && Myhelper::hasRole(['superadmin', 'admin']))
                        <tr class="no-print">
                            <td>Order Review</td>
                            <td>
                                <span class="badge bg-yellow"><i class="fa fa-star"></i> {{ $order->rating }}</span>
                                <i>&nbsp;&nbsp;{{ $order->review }}</i>
                                {{-- <b>{{ $order->deliveryboy->name }}</b> ({{ $order->deliveryboy->mobile }}) --}}
                            </td>
                        </tr>
                    @endif

                    @if($order->payment_txnid != null && Myhelper::hasRole(['superadmin', 'admin']))
                        <tr class="">
                            <td>Payment TxnID</td>
                            <td><span>{{$order->payment_txnid}}</span></td>
                        </tr>
                    @endif

                    @if(Myhelper::hasRole(['superadmin', 'admin']))
                        <tr class="">
                            <td>Order Type</td>
                            <th><span class="text-uppercase">{{$order->type}}</span></th>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="col-xs-6 pull-right">
                {{-- <p class="lead">Amount Due 2/22/2014</p> --}}

                <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Subtotal:</th>
                        <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->item_total, 2, '.', '') }}</td>
                    </tr>
                    <tr>
                        <th>Delivery Charge:</th>
                        <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->delivery_charge, 2, '.', '') }}</td>
                    </tr>
                    @if ($order->coupon_discount > 0)
                        <tr>
                            <th>Coupon Discount:</th>
                            <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->coupon_discount, 2, '.', '') }} (-)</td>
                        </tr>
                    @endif
                    @if ($order->wallet_deducted > 0)
                        <tr>
                            <th>Wallet Deducted:</th>
                            <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->wallet_deducted, 2, '.', '') }} (-)</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Total:</th>
                        <td>{!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->payable_amount, 2, '.', '') }} (Inc. Tax)</td>
                    </tr>
                    @if ($order->wallet_cashback > 0)
                        <tr>
                            <th>Wallet Cashback:</th>
                            <td>
                                {!! config('app.currency.htmlcode') !!} {{ number_format((float)$order->wallet_cashback, 2, '.', '') }}
                                &nbsp;<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="The cashback will be credited to user wallet when the order will be marked as delivered"></i>
                            </td>
                        </tr>
                    @endif
                </table>
                </div>
            </div>
        </div>

        @foreach ($order->return_replacements as $returnreplacement)
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header bg-info">
                            <h3 class="box-title text-uppercase">{{$returnreplacement->type}} Request</h3>

                            <div class="box-tools pull-right">
                                <b>{{$returnreplacement->code}}</b>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12 table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Product</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($returnreplacement->returnreplacement_items as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        {{ $item->orderproduct->product->details->name }}

                                                        @if($item->orderproduct->variant_selected->color_name)
                                                            &nbsp;| Color: {{$item->orderproduct->variant_selected->color_name}}
                                                        @endif

                                                        @if($item->orderproduct->variant_selected->variant_name)
                                                            &nbsp;| {{$item->orderproduct->variant_selected->variant_name}}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-6">
                                    <table class="table table-bordered">
                                        <tr class="">
                                            <td>Current Status</td>
                                            <td class="">
                                                <b>{{ @config('returnreplacestatus.options')[$returnreplacement->status] ?? $returnreplacement->status }}</b>
                                            </td>
                                        </tr>
                                        <tr class="no-print">
                                            <td>Initiation Date</td>
                                            <td class="">
                                                <b>{{ $returnreplacement->created_at }}</b>
                                            </td>
                                        </tr>
                                        <tr class="no-print">
                                            <td>Last Updated</td>
                                            <td class="">
                                                <b>{{ $returnreplacement->updated_at }}</b>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-xs-6">
                                    <table class="table table-bordered">
                                        @if(Myhelper::hasRole(['admin', 'superadmin']))
                                        <tr class="">
                                            <td>Delivery Partner Charge</td>
                                            <td class="">
                                                <b>{!! config('app.currency.htmlcode') !!}{{ $returnreplacement->delivery_charge }}</b>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-xs-12 table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-info">
                                <th colspan="100%" class="text-center text-uppercase">{{$returnreplacement->type}} Request</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                            </tr>
                        </tbody>
                    </table>
                </div> --}}
            </div>
        @endforeach

        <div class="row no-print">
            <div class="col-xs-12">
                <button class="btn btn-default pull-right" id="print"><i class="fa fa-print"></i> Print</button>
                {{-- <a href="{{$order->invoice}}" class="btn btn-default pull-right" id="print"><i class="fa fa-doc-pdf"></i> Invoice</a> --}}
            </div>
        </div>
    </section>

    <div class="clearfix"></div>

    @if (Myhelper::hasRole(['superadmin', 'admin']))
        <section class="content">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Wallet Usages</h3>
                    <div class="box-tools pull-right">

                    </div>
                </div>
                <div class="box-body">
                    <div class="">
                        <table id="wallet-usage-logs" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>User</th>
                                <th>Wallet Type</th>
                                <th>Amount</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Delivery Boy Logs <small class="text-primary">Regular Order</small></h3>
                    <div class="box-tools pull-right">

                    </div>
                </div>
                <div class="box-body">
                    <div class="">
                        <table id="deliveryboy-logs-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if (count($order->return_replacements) > 0)
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Delivery Boy Logs <small class="text-primary">Return/Replace Order</small></h3>
                        <div class="box-tools pull-right">

                        </div>
                    </div>
                    <div class="box-body">
                        <div class="">
                            <table id="deliveryboy-returnreplace-logs-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @endif
@endsection

@push('style')
    <style>
        th, td{ white-space: nowrap }
    </style>
@endpush

@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery.print/1.6.0/jQuery.print.min.js"></script>

    <script>
        $('#print').click(function(){
            $('.invoice').print();
        });

        /* @if (Myhelper::hasRole(['superadmin', 'admin'])) */
            $('#wallet-usage-logs').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('dashboard.fetchdata', ['type' => 'walletstatement'])}}",
                    type: "POST",
                    data:function( d )
                    {
                        d._token = '{{csrf_token()}}';
                        d.startendtime_filter = false;
                        d.ref_id = '{{$order->id}}';
                        d.service = ['order', 'firstorder', 'referral', 'ordercashback', 'ordercancellation']
                    },
                },
                columns:[
                    {
                        data:'id',
                        name: 'id',
                        render: function(data, type, full, meta){
                            return `<b>` + data + `</b>`;
                        },
                    },
                    {
                        data:'created_at',
                        name: 'created_at',
                        render: function(data, type, full, meta){
                            return data;
                        },
                    },
                    {
                        data:'service',
                        name: 'service',
                        render: function(data, type, full, meta){
                            return `<span class="badge bg-blue text-uppercase">${data}</span>`;
                        },
                    },
                    {
                        data:'user',
                        name: 'user.name',
                        render: function(data, type, full, meta){
                            return `${data?.name} <b>(#${data?.id})</b><br>
                                <small class="text-muted">[${data?.role?.name}]</small>`;
                        },
                    },
                    {
                        data:'wallet_type',
                        name: 'wallet_type',
                        render: function(data, type, full, meta){
                            let wallet_type = full.wallet_type;
                            if (full.user.role.slug == 'user') {
                                if (full.wallet_type == 'userwallet')
                                    wallet_type = "Main Wallet";
                            } else if (full.user.role.slug == 'branch') {
                                if (full.wallet_type == 'branchwallet')
                                    wallet_type = "Main Wallet";
                            } else if (full.user.role.slug == 'deliveryboy') {
                                if (full.wallet_type == 'riderwallet')
                                    wallet_type = "Earning Wallet";
                                else if (full.wallet_type == 'creditwallet')
                                    wallet_type = "Collection Wallet";
                            }

                            return wallet_type;
                        },
                    },
                    {
                        data:'amount',
                        name: 'amount',
                        render: function(data, type, full, meta){
                            return `<i class="fa fa-inr"></i> ${data}`;
                        },
                    },
                    {
                        data:'remarks',
                        name: 'remarks',
                        render: function(data, type, full, meta){
                            return data;
                        },
                    },
                ],
                "order": [
                    [0, 'desc']
                ]
            });

            $('#deliveryboy-logs-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('dashboard.fetchdata', ['type' => 'orderdeliveryboylogs'])}}",
                    type: "POST",
                    data:function( d )
                    {
                        d._token = '{{csrf_token()}}';
                        d.order_id = '{{ $order->id }}';
                    },
                },
                columns:[
                    {
                        data:'id',
                        name: 'id',
                        render: function(data, type, full, meta){
                            return `<b>` + data + `</b>`;
                        },
                    },
                    {
                        data:'deliveryboy',
                        name: 'deliveryboy.name',
                        render: function(data, type, full, meta){
                            return data.name;
                        },
                    },
                    {
                        data:'deliveryboy',
                        name: 'deliveryboy.mobile',
                        render: function(data, type, full, meta){
                            return data.mobile;
                        },
                    },
                    {
                        data:'deliveryboy',
                        name: 'deliveryboy.email',
                        render: function(data, type, full, meta){
                            return data.email;
                        },
                    },
                    {
                        data:'status',
                        name: 'status',
                        render: function(data, type, full, meta){
                            if(data == 'accepted'){
                                html = `<span class="badge bg-green text-uppercase">Accepted</span>`
                            } else if(data == 'rejected'){
                                html = `<span class="badge bg-red text-uppercase">Rejected</span>`
                            } else{
                                html = `<span class="badge bg-yellow text-uppercase">Pending</span>`
                            }

                            return html;
                        },
                        className: "text-center",
                    },
                    {
                        data:'description',
                        name: 'description',
                        render: function(data, type, full, meta){
                            return data ?? 'N/A';
                        },
                    },
                ],
                "order": [
                    [0, 'desc']
                ]
            });

            /* @if (count($order->return_replacements) > 0) */
                $('#deliveryboy-returnreplace-logs-datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{route('dashboard.fetchdata', ['type' => 'orderreturnreplacedeliveryboylogs'])}}",
                        type: "POST",
                        data:function( d )
                        {
                            d._token = '{{csrf_token()}}';
                            d.order_id = '{{ $order->id }}';
                        },
                    },
                    columns:[
                        {
                            data:'id',
                            name: 'id',
                            render: function(data, type, full, meta){
                                return `<b>` + data + `</b>`;
                            },
                        },
                        {
                            data:'orderreturnreplace',
                            name: 'orderreturnreplace.code',
                            render: function(data, type, full, meta){
                                return `<b>` + data?.code + `</b>`;
                            },
                        },
                        {
                            data:'deliveryboy',
                            name: 'deliveryboy.name',
                            render: function(data, type, full, meta){
                                return data.name;
                            },
                        },
                        {
                            data:'deliveryboy',
                            name: 'deliveryboy.mobile',
                            render: function(data, type, full, meta){
                                return data.mobile;
                            },
                        },
                        {
                            data:'deliveryboy',
                            name: 'deliveryboy.email',
                            render: function(data, type, full, meta){
                                return data.email;
                            },
                        },
                        {
                            data:'status',
                            name: 'status',
                            render: function(data, type, full, meta){
                                if(data == 'accepted'){
                                    html = `<span class="badge bg-green text-uppercase">Accepted</span>`
                                } else if(data == 'rejected'){
                                    html = `<span class="badge bg-red text-uppercase">Rejected</span>`
                                } else{
                                    html = `<span class="badge bg-yellow text-uppercase">Pending</span>`
                                }

                                return html;
                            },
                            className: "text-center",
                        },
                        {
                            data:'description',
                            name: 'description',
                            render: function(data, type, full, meta){
                                return data ?? 'N/A';
                            },
                        },
                    ],
                    "order": [
                        [0, 'desc']
                    ]
                });
            /* @endif */
        /* @endif */
    </script>
@endpush
