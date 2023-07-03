@section('pageheader', 'Profits')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Sales Management
            <small></small>
        </h1>
         
        @if(Myhelper::hasRole(['branch', 'admin']))
            @if (session('admin'))
                <div class="mt-5">

                    <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
                </div>

            @endif
        @endif
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Orders Management</li>
            <li class="active">View All</li>
        </ol>
    </section>
    

    <section class="content">
        @php

            $filteroptions = [
                'daterange' => true,
               // 'cattypefilter' => true,
                'orderstatusfilter' => true,
                
                //'cityfilter' => true
            ];

            if(Myhelper::hasRole(['superadmin', 'admin'])) {
                $filteroptions['userfilter'] = true;
                $filteroptions['mobilenofilter'] = true;
                $filteroptions['orderidfilter'] = true;
            }
        @endphp
        @include('inc.inhouse.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Orders</h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <div class="box-body">
                <div class="">
                    <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Order Details</th>
                            @if(Myhelper::hasRole(['superadmin', 'admin']))
                                <th>User Details</th>
                            @endif
                            <th>Customer Details</th>
                            <th>Shop Details</th>
                            <th>Profit</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

   
@endsection

@push('style')
    <style>
        th, td{ white-space: nowrap }

        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            top: 39px;
        }

        span.dtr-title {
            display: block !important;
        }
    </style>
@endpush

@push('script')
    <script>

        function reasonChange(src){
            if (src.value == 'others'){
                document.getElementById("other").disabled = false;
            }else{
                document.getElementById("other").disabled = true;
            }
        }
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'profits'])}}",
                type: "POST",
                data:function( d )
                {
                    console.log(d);
                    d.daterange = $('#searchform').find('[name="daterange"]').val();
                    d.user_id = $('#searchform').find('[name="user_id"]').val();
                    d.code = $('#searchform').find('[name="order_id"]').val();
                    // d.type = $('#searchform').find('[name="type"]').val();
                    d.cust_mobile = $('#searchform').find('[name="cust_mobile"]').val();
                    // d.cust_location = $('#searchform').find('[name="city"]').val();
                    d.status = $('#searchform').find('[name="orderstatus"]').val();
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        return `<b class="text-primary" style="font-size: x-large;">` + data + `</b>`;
                    },
                    className: "text-center",
                    orderable: true,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        return `<b class="text-primary">`+full.code+`</b> <small class="text-uppercase text-info">(`+full.type+`)</small><br>\
                            Payable Amount: <b>{!! config("app.currency.faicon") !!}`+full.payable_amount+`</b><br>\
                            Payment Mode: <b class="text-uppercase">`+full.payment_mode+`</b><br>\
                            <b class="text-danger">(`+full.created_at+`)</b>`;
                    },
                    orderable: false,
                    searchable: false,
                },
                /* @if(Myhelper::hasRole(['superadmin', 'admin'])) */
                {
                    data:'user',
                    name: 'user',
                    render: function(data, type, full, meta){
                        return `Name: <b>`+data.name+`</b><br>\
                            Email: <b>`+data.email.substring(0,10)+ "..." +`</b><br>\
                            Mobile: <b>`+data.mobile+`</b><br>\
                            Unique ID: <b class="text-danger">(#`+data.id+`)</b>`;
                    },
                    searchable: false,
                    orderable: false,
                },
               
                /* @endif */
                {
                    render: function(data, type, full, meta){
                        return `Name: <b>`+full.cust_name+`</b><br>\
                            Mobile: <b>`+full.cust_mobile+`</b><br>\
                            Location: <b><a target="_blank" href="https://maps.google.com/?q=` + full.cust_latitude + `,` + full.cust_longitude + `">View on Map</a></b> <br>\
                            Postal Code: <b>`+full.cust_address?.postal_code+`</b>`;
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        return `Name: <b>`+full.shop.shop_name.substring(0,10)+ "..." +` </b><br>\
                            Mobile: <b>`+full.shop.shop_mobile+`</b><br>\
                            Location: <b><a target="_blank" href="https://maps.google.com/?q=` + full.shop.shop_latitude + `,` + full.shop.shop_longitude + `">View on Map</a></b> <br>\
                            Postal Code: <b>`+full.shop.shop_address?.postal_code+`</b>`;
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    data:'user',
                    name: 'user',
                    
                    render: function(data, type, full, meta){
                       
                            return `Profits: <br> <b>`+full.admin_charge +`</b><br>\
                            `;
                       
                        
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        if(data == 'received'){
                            html = `<span class="badge bg-yellow">{{config('orderstatus.options')['received']}}</span>`
                        } else if(data == 'accepted'){
                            html = `<span class="badge bg-purple">{{config('orderstatus.options')['accepted']}}</span>`
                        } else if(data == 'processed'){
                            html = `<span class="badge bg-purple">{{config('orderstatus.options')['processed']}}</span>`
                        } else if(data == 'intransit'){
                            html = `<span class="badge bg-navy">{{config('orderstatus.options')['intransit']}}</span>`
                        } else if(data == 'outfordelivery'){
                            html = `<span class="badge bg-green disabled">{{config('orderstatus.options')['outfordelivery']}}</span>`
                        } else if(data == 'delivered'){
                            html = `<span class="badge bg-green">{{config('orderstatus.options')['delivered']}}</span>`
                        } else if(data == 'cancelled'){
                            html = `<span class="badge bg-red text-capitalize">{{config('orderstatus.options')['cancelled']}}</span>`
                        } else if(data == 'returned'){
                            html = `<span class="badge bg-red text-capitalize">{{config('orderstatus.options')['returned']}}</span>`
                        } else{
                            html = `<span class="badge text-capitalize">`+data+`</span>`
                        }

                        if(full.deliveryboy != null){
                            html += `<br><span class="badge bg-navy disabled"
                                            style="padding: 5px 10px; margin: 3px;"
                                            data-toggle="tooltip"
                                            data-placement="right"
                                            title="` + full.deliveryboy?.name + ` (#` + full.deliveryboy?.id + `)">\
                                        <i class="fa fa-truck"></i>\
                                    </span>`
                        }

                        return html;
                    },
                    className: "text-center",
                    orderable: true,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.orders.view')}}/`+full.id+`" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>`;

                        /* @if(Myhelper::can('update_order_status')) */
                            /* @if(Myhelper::hasRole('branch')) */
                              
                            /* @else */
                                html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.status+`', '`+full.expected_delivery+`')" data-toggle="tooltip" data-placement="top" title="Update Status"><i class="fa fa-edit"></i></a>`;
                            /* @endif */
                        /* @endif */
                        if(['accepted','processed', 'intransit', 'received'].includes(full.status)){
                            html += `<a class="btn btn-xs btn-danger mg" href="javascript:;" onclick="cancelOrder('`+full.id+`')" data-toggle="tooltip" data-placement="top" title="Cancel"><i class="fa fa-ban"></i></a>`;
                        }
                      
                        /* @if(Myhelper::can('assign_delivery_boy')) */
                            if(['accepted','processed'].includes(full.status) && !full.deliveryboy_id){
                                html += `<a data-toggle="modal" data-target="#exampleModal" class="btn btn-xs btn-info mg" href="javascript:;" onclick="assignDeliveryBoy('`+full.id+`')" data-toggle="tooltip" data-placement="top" title="Assign Delivery Boy"><i class="fa fa-truck"></i></a>`;
                            }
                        /* @endif */

                        return html;

                        var menu = `<div class="btn-group">\
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">\
                                    <i class="fa fa-bars"></i>&nbsp;&nbsp;<span class="fa fa-caret-down"></span>\
                                </button>\
                                <ul class="dropdown-menu">\
                                    `+html+`
                                </ul>\
                            </div>`;

                        return menu;
                    },
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                }
            ],
            "order": [
                [0, 'desc']
            ],
            "drawCallback": function( settings ) {
                $('[data-toggle="tooltip"]').tooltip()
            }
        });


      
      
    

    </script>
@endpush
