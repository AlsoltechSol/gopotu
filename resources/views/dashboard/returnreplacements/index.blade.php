@section('pageheader', 'Return Replacements')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Return Replacements Management
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Return Replacements Management</li>
            <li class="active">View All</li>
        </ol>
    </section>
    <section class="content">
        @php
            $filteroptions = [
                'daterange' => true,
                // 'userfilter' => true,
                // 'cattypefilter' => true
            ];
        @endphp
        @include('inc.inhouse.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Return Replacements</h3>
                <div class="box-tools pull-right">
                    @if (Myhelper::can(['create_return_request', 'create_replacement_request']))
                        <a href="{{ route('dashboard.returnreplacements.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add New</a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <div class="">
                    <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Return/Replace Details</th>
                            <th>Order Details</th>
                            <th>Product Details</th>
                            <th>Ret/Rep Reason</th>
                            <th>User Details</th>
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

                <form action="{{route('dashboard.returnreplacements.submit')}}" method="POST" id="statusform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="statusupdate">

                    <div class="modal-body">
                        <div class="form-group status-master return-status" style="display: none">
                            <label>Select Status <span class="text-danger">*</span></label>
                            <select name="return_status" class="form-control select2" style="width: 100%">
                                @foreach ($return_status as $key => $item)
                                    <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group status-master replace-status" style="display: none">
                            <label>Select Status <span class="text-danger">*</span></label>
                            <select name="replace_status" class="form-control select2" style="width: 100%">
                                @foreach ($replace_status as $key => $item)
                                    <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="returnreplace-preperation-time" style="display: none">
                            <label>Items Preperation Time <span class="text-danger">*</span></label>
                            <select name="preperation_time" class="form-control select2" style="width: 100%">
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

                <form action="{{route('dashboard.returnreplacements.submit')}}" method="POST" id="assigndeliveryboyform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="assigndeliveryboy">

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

    <div class="modal fade" id="adminremarksmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Remarks <small id="code"></small></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.returnreplacements.submit')}}" method="POST" id="adminremarksform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="adminremarksupdate">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="adminremarks"></textarea>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'returnreplacements'])}}",
                type: "POST",
                data:function( d )
                {
                    d.daterange = $('#searchform').find('[name="daterange"]').val();
                    // d.order.user_id = $('#searchform').find('[name="user_id"]').val();
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
                        return `<b class="text-primary">`+full.code+`</b><br>\
                            Type: <b class="text-uppercase">`+full.type+`</b><br>\
                            <b class="text-danger">(`+full.created_at+`)</b>`;
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){

                        let order = full.order;
                        console.log(order);
                        if (order != null){
                            return `<b class="">`+order.type+`</b> <small class="text-uppercase text-info">(`+order.type+`)</small><br>\
                            Payable Amount: <b>{!! config("app.currency.faicon") !!}`+order.payable_amount+`</b><br>\
                            Payment Mode: <b class="text-uppercase">`+order.payment_mode+`</b>`;
                        }else{
                            return `N/A`
                        }

                       
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        
                        let order = full.order;
                        console.log(order);
                        if (order != null){
                            return `<b class="">`+order.pro+`</b> `
                        }else{
                            return `N/A`
                        }

                       
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){

                        let order = full.order;
                        console.log(order);
                        if (order != null){
                            return `<b class="">`+order.reason+`</b> `
                        }else{
                            return `N/A`
                        }

                       
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        if (full.order != null){
                            let user = full.order.user;
                            if (user != null){
                                return `Name: <b>`+user.name+`</b> <b class="text-danger">(#`+user.id+`)</b><br>\
                                Email: <b>`+user.email+`</b><br>\
                                Mobile: <b>`+user.mobile+`</b>`;
                            }else{
                                return `N/A` 
                            }
                          
                        }else{
                            return `N/A`
                        }
                      
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    render: function(data, type, full, meta){
                        if (full.order != null){
                            let order = full.order;
                            return `Name: <b>`+order.cust_name+`</b><br>\
                            Mobile: <b>`+order.cust_mobile+`</b><br>\
                            Location: <b><a target="_blank" href="https://maps.google.com/?q=` + order.cust_latitude + `,` + order.cust_longitude + `">View on Map</a></b>`;
                        }else{
                            return `N/A`
                        }
                     
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        if (full.order){
                            let shop = full.order.shop;

                            return `Name: <b>`+shop.shop_name+`</b><br>\
                            Mobile: <b>`+shop.shop_mobile+`</b><br>\
                            Location: <b><a target="_blank" href="https://maps.google.com/?q=` + shop.shop_latitude + `,` + shop.shop_longitude + `">View on Map</a></b>`;
                        }else{
                            return `N/A`
                        }
                       
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        if(data == 'initiated'){
                            html = `<span class="badge bg-yellow">{{config('returnreplacestatus.options')['initiated']}}</span>`
                        } else if(data == 'accepted'){
                            html = `<span class="badge bg-purple">{{config('returnreplacestatus.options')['accepted']}}</span>`
                        } else if(data == 'processed'){
                            html = `<span class="badge bg-purple">{{config('returnreplacestatus.options')['processed']}}</span>`
                        } else if(data == 'intransit'){
                            html = `<span class="badge bg-navy">{{config('returnreplacestatus.options')['intransit']}}</span>`
                        } else if(data == 'outfordelivery'){
                            html = `<span class="badge bg-green disabled">{{config('returnreplacestatus.options')['outfordelivery']}}</span>`
                        } else if(data == 'delivered'){
                            html = `<span class="badge bg-green">{{config('returnreplacestatus.options')['delivered']}}</span>`
                        } else if(data == 'outforpickup'){
                            html = `<span class="badge bg-green">{{config('returnreplacestatus.options')['outforpickup']}}</span>`
                        } else if(data == 'outforstore'){
                            html = `<span class="badge bg-green">{{config('returnreplacestatus.options')['outforstore']}}</span>`
                        } else if(data == 'deliveredtostore'){
                            html = `<span class="badge bg-green">{{config('returnreplacestatus.options')['deliveredtostore']}}</span>`
                        } else if(data == 'rejected'){
                            html = `<span class="badge bg-red text-capitalize">{{config('returnreplacestatus.options')['rejected']}}</span>`
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

                        if (full.order !=null){
                            html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.orders.view')}}/`+full.order.id+`"><i class="fa fa-eye"></i></a>`;
                        }

                     

                        /* @if(Myhelper::can('update_return_replacement_status')) */
                            /* @if(Myhelper::hasRole('branch')) */
                                if((full.type == 'return' && ['initiated'].includes(full.status)) || (full.type == 'replace' && ['initiated', 'accepted', 'processed', ].includes(full.status))){
                                    html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.type+`', '`+full.status+`')"><i class="fa fa-edit"></i></a>`;
                                }
                            /* @else */
                                html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.type+`', '`+full.status+`')"><i class="fa fa-edit"></i></a>`;
                            /* @endif */
                        /* @endif */

                        /* @if(Myhelper::can('assign_delivery_boy')) */
                            if(['accepted', 'processed'].includes(full.status) && !full.deliveryboy_id){
                                html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="assignDeliveryBoy('`+full.id+`')"><i class="fa fa-truck"></i></a>`;
                            }
                        /* @endif */

                        /* @if(Myhelper::hasRole(['superadmin', 'admin']) && Myhelper::can('update_return_replacement_status')) */
                            html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="addRemarks('`+full.id+`')"><i class="fa fa-sticky-note-o"></i></a>`;
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

        function editStatus(id, type, status){
            $('#statusform').find(`.form-group.status-master`).hide()
            $('#statusform').find(`.form-group.${type}-status`).show()
            $('#statusform').find(`[name="${type}_status"]`).find('option').attr('disabled', true)

            var available_status = ['initiated', 'accepted', 'processed', 'intransit', 'outfordelivery', 'delivered', 'outforpickup', 'outforstore', 'deliveredtostore', 'rejected', ];

            var enable_status = [];

            /* @if(Myhelper::hasrole(['superadmin', 'admin'])) */
                if(type == 'return'){
                    switch (status) {
                        case 'initiated':
                            enable_status = ['accepted', 'rejected']
                        break;

                        case 'accepted':
                            enable_status = ['outforpickup']
                        break;

                        case 'outforpickup':
                            enable_status = ['outforstore']
                        break;

                        case 'outforstore':
                            enable_status = ['deliveredtostore']
                        break;
                    }
                } else if(type == 'replace'){
                    switch (status) {
                        case 'initiated':
                            enable_status = ['accepted', 'rejected']
                        break;

                        case 'accepted':
                            enable_status = ['processed', 'intransit']
                        break;

                        case 'processed':
                            enable_status = ['intransit']
                        break;

                        case 'intransit':
                            enable_status = ['outfordelivery']
                        break;

                        case 'outfordelivery':
                            enable_status = ['outforstore']
                        break;

                        case 'outforstore':
                            enable_status = ['deliveredtostore']
                        break;
                    }
                }
            /* @elseif(Myhelper::hasrole(['branch'])) */
                if(type == 'return'){
                    switch (status) {
                        case 'initiated':
                            enable_status = ['accepted', 'rejected']
                        break;
                    }
                } else if(type == 'replace'){
                    switch (status) {
                        case 'initiated':
                            enable_status = ['accepted', 'rejected']
                        break;

                        case 'accepted':
                            enable_status = ['processed', 'intransit']
                        break;

                        case 'processed':
                            enable_status = ['intransit']
                        break;
                    }
                }
            /* @endif */

            enable_status.forEach(element => {
                $('#statusform').find(`[name="${type}_status"]`).find('[value=' + element + ']').removeAttr('disabled');
            });

            $('#statusform').find(`[name="${type}_status"]`).val(status).trigger("change");
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

        function assignDeliveryBoy(returnreplace_id, km_radius = 10){
            Pace.track(function(){
                $('button#deliveryboy-refresh').addClass('disabled');

                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.returnreplacements.ajax')}}",
                    method: "POST",
                    data: {'_token' : '{{csrf_token()}}', 'returnreplace_id' : returnreplace_id, 'type' : 'returnrefund-deliveryboy-assign', 'km_radius': km_radius },
                    success: function(result){
                        $('button#deliveryboy-refresh').removeClass('disabled');

                        var options = '<option value="">Select Delivery Boy</option>';

                        result.data.delivery_boys.forEach(deliveryboy => {
                            options += `<option value="` + deliveryboy.id + `">` + deliveryboy.name + ` (#` + deliveryboy.id + `)</option>`;
                        });

                        $('#assigndeliveryboyform').find('[name="deliveryboy_id"]').html(options);
                        $('#assigndeliveryboyform').find('[name="deliveryboy_id"]').val(result.data.returnreplace.deliveryboy_id ?? "").trigger("change");
                        $('#assigndeliveryboyform').find('[name="id"]').val(result.data.returnreplace.id);
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

            let returnreplace_id = $('#assigndeliveryboyform').find('[name="id"]').val();
            if(!returnreplace_id){
                notify("Order ID not found", 'error');
                return;
            }

            assignDeliveryBoy(returnreplace_id, km_radius);
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

        $('#statusform').find('[name="replace_status"]').on('change', function(e){
            e.preventDefault();
            statusChanged('replace', $(this).val());
        })

        function statusChanged(MODE, STATUS){
            let returnreplace_id = $('#statusform').find('[name="id"]').val();
            if(returnreplace_id && STATUS == 'accepted'){
                Pace.track(function(){
                    $.ajax({
                        dataType: "JSON",
                        url: "{{route('dashboard.fetchdata', ['type' => 'returnreplacements', 'fetch' => 'single'])}}" + "/" + returnreplace_id,
                        success: function(result){
                            let returnreplace = result.result;
                            // console.log(returnreplace.order.type);

                            if(["restaurant", "mart"].includes(returnreplace.order.type) && MODE == 'replace'){
                                $('#statusform').find('#returnreplace-preperation-time').show(100);
                            }
                        }, error: function(errors){
                            $('#statusform').find('#returnreplace-preperation-time').hide(100);
                            showErrors(errors);
                        }
                    });
                });
            } else{
                $('#statusform').find('#returnreplace-preperation-time').hide(100);
            }
        }

        function addRemarks(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'returnreplacements', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;
                        $('#adminremarksmodal').find('#code').text(result.code);
                        $('#adminremarksform').find('[name=id]').val(id);
                        $('#adminremarksform').find('[name=adminremarks]').val(result.adminremarks ?? null);
                        $('#adminremarksmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        $('#adminremarksform').validate({
            rules: {
                adminremarks: {
                    required: false,
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
                var form = $('#adminremarksform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#adminremarksmodal').modal('hide');
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
    </script>
@endpush
