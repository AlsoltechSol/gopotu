@section('pageheader', 'Product Stocks')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Products Management
            <small>Stock</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{route('dashboard.products.index')}}">Products Management</a></li>
            <li class="active">Stocks</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Product Stocks</h3>
                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_product'))
                        <a href="{{route('dashboard.products.library')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add New</a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <div class="">
                    <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Color</th>
                            <th>Variant</th>
                            <th>Price</th>
                            <th>Offered Price</th>
                            <th>Stock</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="varantmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.products.stock.submit')}}" method="POST" id="variantform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Price <span class="text-danger">*</span></label>
                            <input type="text" value="" name="price" class="form-control" placeholder="Enter Value" required>
                        </div>

                        <div class="form-group">
                            <label>Offered Price <span class="text-danger">*</span></label>
                            <input type="text" value="" name="offeredprice" class="form-control" placeholder="Enter Value" required>
                        </div>

                        <div class="form-group">
                            <label>Quantity (Stock Available) <span class="text-danger">*</span></label>
                            <input type="text" value="" name="quantity" class="form-control" placeholder="Enter Value" required>
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
        .bg-red b.text-primary {
            color: white;
        }
    </style>
@endpush

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'martstock_variants'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        return '<b class="text-primary">' + data + '</b>';
                    },
                },
                {
                    data:'sku',
                    name: 'sku',
                    render: function(data, type, full, meta){
                        let sku = data ? data : 'N/A';

                        return `<b>` + sku + `</b>`
                    },
                },
                {
                    data:'product',
                    name: 'product.details.name',
                    render: function(data, type, full, meta){
                        return data.details.name
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'product',
                    name: 'product.details.category.name',
                    render: function(data, type, full, meta){
                        return data.details.category.name
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'product',
                    name: 'product.details.image',
                    render: function(data, type, full, meta){
                        return `<a href="` + data.image_path + `" data-toggle="lightbox">\
                                    <img class="datatable-icon" src="` + data.details.image_path + `">\
                                </a>`
                    },
                    searchable: false,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data:'color',
                    name: 'color',
                    render: function(data, type, full, meta){
                        if(!data){
                            return 'N/A'
                        }

                        return `<span class="color-code-disp" style="background: ` + data + `"></span><span>` + full.color_details?.name + `</span>`;
                    },
                },
                {
                    data:'variant',
                    name: 'variant',
                    render: function(data, type, full, meta){
                        if(!data){
                            return 'N/A'
                        }

                        let arr = data.split(":");
                        return `<span class="text-capitalize">` + arr[0] + `</span> : <b>` + arr[1] + `</b>`;
                    },
                },
                {
                    data:'price',
                    name: 'price',
                    render: function(data, type, full, meta){
                        if(!data){
                            return 'N/A'
                        }

                        return `<b style="font-size: 16px" class="text-primary">{!!config('app.currency.faicon')!!}` + data + `</b>`;
                    },
                    className: 'text-center'
                },
                {
                    data:'offeredprice',
                    name: 'offeredprice',
                    render: function(data, type, full, meta){
                        if(!data){
                            return 'N/A'
                        }

                        return `<b style="font-size: 16px" class="text-primary">{!!config('app.currency.faicon')!!}` + data + `</b>`;
                    },
                    className: 'text-center'
                },
                {
                    data:'quantity',
                    name: 'quantity',
                    render: function(data, type, full, meta){
                        return `<b style="font-size: 16px" class="">` + data + `</b>`;
                    },
                    className: 'text-center'
                },
                {
                    data:'updated_at',
                    name: 'updated_at',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + `)">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        @if(Myhelper::can('edit_product'))
                            html += `<li><a href="javascript:;" onclick="edit('`+full.id+`')"><i class="fa fa-edit"></i>Update</a></li>`;
                        @endif

                        @if(Myhelper::can('product_stock_delete'))
                            html += `<li><a href="javascript:;" onclick="deleteitem('`+full.id+`')"><i class="fa fa-trash"></i>Delete</a></li>`;
                        @endif

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
                }
            ],
            "order": [
                [0, 'asc']
            ],
            createdRow: function( row, data, dataIndex ) {
                // Set the data-status attribute, and add a class
                if(data.quantity <= 5){
                    $( row ).addClass('bg-red');
                }
            }
        });

        $('#variantform').validate({
            rules: {
                price: {
                    required: true,
                    number: true,
                    min: 1,
                },
                offeredprice: {
                    required: true,
                    number: true,
                    min: 1,
                },
                quantity: {
                    required: true,
                    number: true,
                    min: 0,
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
                var form = $('#variantform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#varantmodal').modal('hide');
                            form.find('button[type="submit"]').button('reset');
                            $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function changeAction(id, operation = "changestatus"){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.products.stock.submit')}}",
                    data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
                    method: "POST",
                    success: function(data){
                        notify(data.status, 'success');
                        $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                    },
                    error: function(errors) {
                        showErrors(errors);
                        $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                    }
                });
            });
        }

        function deleteitem(id){
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                })
                .then((willDelete) => {
                if (willDelete) {
                    Pace.track(function(){
                        $.ajax({
                            dataType: "JSON",
                            url: "{{route('dashboard.products.stock.submit')}}",
                            method: "POST",
                            data: {'_token':'{{csrf_token()}}','operation':'delete','id':id},
                            success: function(data){
                                $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                            }, error: function(errors){
                                showErrors(errors);
                            }
                        });
                    });
                } else {
                    swal({
                        title: "Cancelled Successfully",
                        text: "Your data is safe!",
                        icon: "warning",
                    });
                }
            });
        }

        function edit(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'product_variants', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;

                        $('#variantform')[0].reset();
                        $('#variantform').find('[name=id]').val(id);
                        $('#variantform').find('[name=operation]').val('edit');
                        $('#variantform').find('[name=price]').val(result.price);
                        $('#variantform').find('[name=offeredprice]').val(result.offeredprice);
                        $('#variantform').find('[name=quantity]').val(result.quantity);

                        $('#varantmodal').find('.modal-title').text('Update Stock');
                        $('#varantmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }
    </script>
@endpush
