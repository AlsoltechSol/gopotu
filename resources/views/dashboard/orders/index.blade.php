@section('pageheader', 'Orders')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Orders Management
            <small></small>
        </h1>
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
                'cattypefilter' => true,
                'orderstatusfilter' => true,
            ];

            if(Myhelper::hasRole(['superadmin', 'admin'])) {
                $filteroptions['userfilter'] = true;
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

    <div class="modal fade" id="statusmodal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Order</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.orders.update')}}" method="POST" id="statusform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="type" value="orderstatus">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control select2" style="width: 100%">
                                @foreach ($order_status as $key => $item)
                                    <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="order-time-slots" style="display: none">
                            <label>Expected Ready Time <span class="text-danger">*</span></label>
                            <select name="order_ready_time" class="form-control select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                @foreach ($martTimeslots as $item)
                                    <option value="{{$item}}">{{ Carbon\Carbon::parse($item)->format('d M y - h:i A') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="order-preperation-time" style="display: none">
                            <label>Order Preperation Time <span class="text-danger">*</span></label>
                            <select name="order_preperation_time" class="form-control select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                @foreach ($restaurantPreperationTtimes as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assigndeliveryboymodal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Delivery Partner</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.orders.update')}}" method="POST" id="assigndeliveryboyform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="type" value="assigndeliveryboy">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Search Within (In KM)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="km_radius">

                                <span class="input-group-btn">
                                    <button onclick="refreshDeliveryBoyList()" type="button" class="btn btn-info btn-flat" id="deliveryboy-refresh"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Select Delivery Boy <span class="text-danger">*</span></label>
                            <select name="deliveryboy_id" class="form-control select2" style="width: 100%">

                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'orders'])}}",
                type: "POST",
                data:function( d )
                {
                    d.daterange = $('#searchform').find('[name="daterange"]').val();
                    d.user_id = $('#searchform').find('[name="user_id"]').val();
                    d.type = $('#searchform').find('[name="type"]').val();
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
                            Email: <b>`+data.email+`</b><br>\
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
                        return `Name: <b>`+full.shop.shop_name+`</b><br>\
                            Mobile: <b>`+full.shop.shop_mobile+`</b><br>\
                            Location: <b><a target="_blank" href="https://maps.google.com/?q=` + full.shop.shop_latitude + `,` + full.shop.shop_longitude + `">View on Map</a></b> <br>\
                            Postal Code: <b>`+full.shop.shop_address?.postal_code+`</b>`;
                    },
                    orderable: false,
                    searchable: false,
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
                                if(['received', 'processed', 'accepted'].includes(full.status)){
                                    html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.status+`', '`+full.expected_delivery+`')" data-toggle="tooltip" data-placement="top" title="Update Status"><i class="fa fa-edit"></i></a>`;
                                }
                            /* @else */
                                html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.status+`', '`+full.expected_delivery+`')" data-toggle="tooltip" data-placement="top" title="Update Status"><i class="fa fa-edit"></i></a>`;
                            /* @endif */
                        /* @endif */
                        html += `<a class="btn btn-xs btn-danger mg" href="{{route('dashboard.orders.cancel')}}/`+full.id+`" data-toggle="tooltip" data-placement="top" title="Cancel"><i class="fa fa-ban"></i></a>`;
                        /* @if(Myhelper::can('assign_delivery_boy')) */
                            if(['accepted','processed'].includes(full.status) && !full.deliveryboy_id){
                                html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="assignDeliveryBoy('`+full.id+`')" data-toggle="tooltip" data-placement="top" title="Assign Delivery Boy"><i class="fa fa-truck"></i></a>`;
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

        function editStatus(id, status, expected_delivery){
            $('#statusform').find('[name="status"]').find('option').attr('disabled', true)
            var available_status = ['received','accepted','intransit','outfordelivery','delivered','cancelled','returned'];

            var enable_status = [];

            /* @if(Myhelper::hasrole(['superadmin', 'admin'])) */
                switch (status) {
                    case 'received':
                        enable_status = ['accepted', 'cancelled', 'returned']
                    break;

                    case 'accepted':
                        enable_status = ['processed', 'intransit', 'cancelled', 'returned']
                    break;

                    case 'processed':
                        enable_status = ['intransit', 'cancelled', 'returned']
                    break;

                    case 'intransit':
                        enable_status = ['outfordelivery', 'cancelled', 'returned']
                    break;

                    case 'outfordelivery':
                        enable_status = ['delivered', 'cancelled', 'returned']
                    break;

                    case 'delivered':
                        enable_status = ['cancelled', 'returned']
                    break;

                    case 'paymentfailed':
                    case 'cancelled':
                    case 'returned':
                        enable_status = [status];
                    break;
                }
            /* @elseif(Myhelper::hasrole(['branch'])) */
                switch (status) {
                    case 'received':
                        enable_status = ['accepted']
                    break;

                    case 'accepted':
                        enable_status = ['processed', 'intransit']
                    break;

                    case 'processed':
                        enable_status = ['intransit']
                    break;
                }
            /* @endif */

            enable_status.forEach(element => {
                $('#statusform').find('[name="status"]').find('[value=' + element + ']').removeAttr('disabled');
            });

            $('#statusform').find('[name="status"]').val(status).trigger("change");
            $('#statusform').find('[name="expected_delivery"]').val(!['null', null].includes(expected_delivery) ? expected_delivery : null);
            $('#statusform').find('[name="id"]').val(id);
            $('#statusmodal').modal();
        }

        $('#statusform').validate({
            rules: {
                status: {
                    required: true,
                },
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find("span.select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function() {
                var form = $('#statusform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            $('#statusmodal').modal('hide');
                            form.find('button[type="submit"]').button('reset');
                            $('#my-datatable').dataTable().api().ajax.reload();
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function assignDeliveryBoy(order_id, km_radius = 10){
            Pace.track(function(){
                $('button#deliveryboy-refresh').addClass('disabled');

                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.orders.ajax')}}",
                    method: "POST",
                    data: {'_token' : '{{csrf_token()}}', 'order_id' : order_id, 'type' : 'order-deliveryboy-assign', 'km_radius': km_radius },
                    success: function(result){
                        $('button#deliveryboy-refresh').removeClass('disabled');

                        var options = '<option value="">Select Delivery Boy</option>';

                        result.data.delivery_boys.forEach(deliveryboy => {
                            options += `<option value="` + deliveryboy.id + `">` + deliveryboy.name + ` (#` + deliveryboy.id + `)</option>`;
                        });

                        $('#assigndeliveryboyform').find('[name="deliveryboy_id"]').html(options);
                        $('#assigndeliveryboyform').find('[name="deliveryboy_id"]').val(result.data.order.deliveryboy_id ?? "").trigger("change");
                        $('#assigndeliveryboyform').find('[name="id"]').val(result.data.order.id);
                        $('#assigndeliveryboyform').find('[name="km_radius"]').val(km_radius)
                        $('#assigndeliveryboymodal').modal();
                    }, error: function(errors){
                        $('button#deliveryboy-refresh').removeClass('disabled');
                        showErrors(errors);
                    }
                });
            });
        }

        function refreshDeliveryBoyList(){
            let km_radius = $('#assigndeliveryboyform').find('[name="km_radius"]').val();
            if(!km_radius){
                notify("Please enter radius for searching delivery partners", 'error');
                return;
            }

            let order_id = $('#assigndeliveryboyform').find('[name="id"]').val();
            if(!order_id){
                notify("Order ID not found", 'error');
                return;
            }

            assignDeliveryBoy(order_id, km_radius);
        }

        $('#assigndeliveryboyform').validate({
            rules: {
                deliveryboy_id: {
                    required: true,
                },
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find("span.select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function() {
                var form = $('#assigndeliveryboyform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            $('#assigndeliveryboymodal').modal('hide');
                            form.find('button[type="submit"]').button('reset');
                            $('#my-datatable').dataTable().api().ajax.reload();
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        $('#statusform').find('[name="status"]').on('change', function(e){
            e.preventDefault();

            let order_id = $('#statusform').find('[name="id"]').val();
            if(order_id && $(this).val() == 'accepted'){
                Pace.track(function(){
                    $.ajax({
                        dataType: "JSON",
                        url: "{{route('dashboard.fetchdata', ['type' => 'orders', 'fetch' => 'single'])}}" + "/" + order_id,
                        success: function(result){
                            let order = result.result;
                            // if(order.type == "restaurant"){
                            //     $('#statusform').find('#order-preperation-time').show(100);
                            // } else if(order.type == "mart"){
                            //     $('#statusform').find('#order-time-slots').show(100);
                            // }

                            if(["restaurant", "mart"].includes(order.type)){
                                $('#statusform').find('#order-preperation-time').show(100);
                            }
                        }, error: function(errors){
                            showErrors(errors);
                        }
                    });
                });
            }else{
                $('#statusform').find('#order-time-slots').hide(100);
                $('#statusform').find('#order-preperation-time').hide(100);
            }
        })
    </script>
@endpush
