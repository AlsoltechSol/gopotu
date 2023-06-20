@section('pageheader', 'Mart Products')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Mart Products Management
            <small></small>
        </h1>
         
    @if (session('admin') && !Myhelper::hasRole('superadmin'))
    <div class="mt-5">

        <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
    </div>

    @endif
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Mart Products</li>
            <li class="active">View All</li>
        </ol>
    </section>

    
    <section class="content">
        {{-- {{dd(Myhelper::hasRole(['superadmin']))}} --}}
        <div id="role-data" data-role="{{ Myhelper::hasRole(['superadmin']) ? 'superadmin' : 'other' }}"></div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Products</h3>
                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_product') && Myhelper::hasRole(['branch']))
                        <a href="{{route('dashboard.products.library')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add New</a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Shop</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Variants</th>
                        <th>Image</th>
                        <th>Product Price</th>
                        <th>Offered Price</th>
                        
                        @if( Myhelper::hasRole(['superadmin']))
                            <th>Listing Price</th>
                            <th>Admin Charge</th>                         
                        @endif
                        
                        {{-- @if( Myhelper::hasRole(['superadmin', 'branch'])) --}}
                            <th>Top Offer</th>
                        {{-- @endif --}}
                        <th>Last Updated</th>
                        @if( Myhelper::hasRole(['superadmin']))
                            <th>Master Status</th>
                            <th>Verification Status</th>
                            <th>Status</th>
                        @endif
                       
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            top: 33px;
        }
    </style>
@endpush

@push('script')
    <script>

    var role = document.getElementById('role-data').getAttribute('data-role');
    

        var col = [
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        let html = ''
                        html += '<b class="text-primary">' + data + '</b>';

                        if(full.availability == 'comingsoon'){
                            html += '&nbsp;<i data-toggle="tooltip" data-placement="right" title="Coming Soon" class="fa fa-circle text-warning" aria-hidden="true"></i>'
                        }

                        return html;
                    },
                },
                {
                    data:'shop',
                    name: 'shop.shop_name',
                    render: function(data, type, full, meta){
                        return data.shop_name
                    },
                },
                {
                    data:'details',
                    name: 'details.name',
                    render: function(data, type, full, meta){
                        return data.name
                    },
                },
                {
                    data:'details',
                    name: 'details.category.name',
                    render: function(data, type, full, meta){
                        return data.category.name
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'product_variants',
                    name: 'product_variants',
                    render: function(data, type, full, meta){
                        if(data[0]?.variant != null){
                            return data[0]?.variant
                        }else{
                            return 'N/A'
                        }
                      
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'details',
                    name: 'details.image',
                    render: function(data, type, full, meta){
                        return `<a href="` + full.details.image_path + `" data-toggle="lightbox">\
                                    <img class="datatable-icon" src="` + full.details.image_path + `">\
                                </a>`
                    },
                    searchable: false,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data:'product_variants',
                    name: 'product_variants',
                    render: function(data, type, full, meta){
                        let html = ''

                        if(data[0]?.price){
                            html += `<b class="text-primary">{!!config('app.currency.faicon')!!}`+data[0]?.price+`</b>`;
                        } else{
                            html += 'N/A';
                        }

                        return html
                    },
                    searchable: false,
                    orderable: false,
                },
                {
                    data:'product_variants',
                    name: 'product_variants',
                    render: function(data, type, full, meta){
                        let html = ''

                        if(data[0]?.offeredprice){
                            html += `<b class="text-primary">{!!config('app.currency.faicon')!!}`+data[0]?.offeredprice+`</b>`;
                        } else{
                            html += 'N/A';
                        }

                        return html;
                    },
                    searchable: false,
                    orderable: false,
                },
                
                {
                    data:'product_variants',
                        name: 'product_variants',
                        
                        render: function(data, type, full, meta){
                            let html = ''
                         
                            if(data[0]?.listingprice){
                                html += `<b class="text-primary">{!!config('app.currency.faicon')!!}`+data[0]?.listingprice+`</b>`;
                            } else{
                                html += 'N/A';
                            }
                          

                            return html;
                        },
                        searchable: false,
                        orderable: false,
                },

                {
                    data:'product_variants',
                        name: 'product_variants',
                        render: function(data, type, full, meta){
                            let html = ''
                          

                            if(data[0]?.listingprice){
                                html += `<b class="text-primary">{!!config('app.currency.faicon')!!}`+(parseInt(data[0]?.listingprice) - parseInt(data[0]?.offeredprice))+`</b>`;
                            } else{
                                html += 'N/A';
                            }
                          

                            return html;
                        },
                        searchable: false,
                        orderable: false,
                },
             
                
                {
                    data:'top_offer',
                    name: 'top_offer',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + `, 'changetopoffer')">
                                    <span class="slider round"></span>
                                </label>`;
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
                    data:'master_status',
                    name: 'master_status',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + ` , 'master')">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
                },
                {
                    data:'verification_status',
                    name: 'verification_status',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + ` , 'verify')">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
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

                        // html += `<a class="btn btn-xs btn-info mg" href="{{route('dashboard.products.view')}}/`+full.id+`"><i class="fa fa-eye"></i></a>`;

                        /* @if(Myhelper::can('edit_product')) */
                            html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.products.edit')}}/`+full.id+`"><i class="fa fa-edit"></i></a>`;
                        /* @endif */

                        /* @if(Myhelper::can('delete_product')) */
                            html += `<a class="btn btn-xs btn-danger mg" href="javascript:;" onclick="deleteitem('`+full.id+`')"><i class="fa fa-trash"></i></a>`;
                        /* @endif */

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ];

           
        // hide superadmin section for merchants
        if (role !== 'superadmin') {

          
            col.splice(8,2);
            col.splice(10,3);
          // col.splice(8,1);
        }

        // if (role == 'admin'){
        //     col.splice(9,1);
        // }
    
           
        var dataTable = $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'martproducts'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                   
                },
                
            },
           
            columns:col,
            "drawCallback": function( settings ) {
                
                $('[data-toggle="tooltip"]').tooltip();
              
            }
            
           
        });

        function changeAction(id, operation = "changestatus"){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.products.submit')}}",
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

        // function changeAction(id, operation = "master"){
        //     Pace.track(function(){
        //         $.ajax({
        //             dataType: "JSON",
        //             url: "{{route('dashboard.products.submit')}}",
        //             data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
        //             method: "POST",
        //             success: function(data){
        //                 notify(data.master_status, 'success');
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             },
        //             error: function(errors) {
        //                 showErrors(errors);
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             }
        //         });
        //     });
        // }

        // function changeAction(id, operation = "verify"){
        //     Pace.track(function(){
        //         $.ajax({
        //             dataType: "JSON",
        //             url: "{{route('dashboard.products.submit')}}",
        //             data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
        //             method: "POST",
        //             success: function(data){
        //                 notify(data.verification_status, 'success');
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             },
        //             error: function(errors) {
        //                 showErrors(errors);
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             }
        //         });
        //     });
        // }

        // function changeActionMaster(id){
        //     Pace.track(function(){
        //         $.ajax({
        //             dataType: "JSON",
        //             url: "{{route('dashboard.products.submit')}}",
        //             data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
        //             method: "POST",
        //             success: function(data){
        //                 notify(data.master_status, 'success');
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             },
        //             error: function(errors) {
        //                 showErrors(errors);
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             }
        //         });
        //     });
        // }

        // function changeActionVerify(id){
        //     Pace.track(function(){
        //         $.ajax({
        //             dataType: "JSON",
        //             url: "{{route('dashboard.products.submit')}}",
        //             data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
        //             method: "POST",
        //             success: function(data){
        //                 notify(data.verification_status, 'success');
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             },
        //             error: function(errors) {
        //                 showErrors(errors);
        //                 $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
        //             }
        //         });
        //     });
        // }

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
                            url: "{{route('dashboard.products.submit')}}",
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
    </script>
@endpush
