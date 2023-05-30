@section('pageheader', 'Master Products Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Master Products Management
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Master</li>
            <li class="active">Master Products</li>
        </ol>
    </section>

    <section class="content">
        @php
            $customfilter = true;
            $filteroptions = [
              
               // 'shopfilter' => true
            ];
            $customfilter_array = [
                (object) [
                    'label' => "Type",
                    'name' => "type",
                    'type' => "select2",
                    'options' => [
                        '' => "Select Type",
                        'mart' => "Mart",
                        'restaurant' => "Restaurant",
                    ]
                ],

               
            ];
        @endphp
        @include('inc.inhouse.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Master Products</h3>

                <div class="box-tools pull-right">
                    @if (Myhelper::can('add_product_master'))
                        {{-- <a class="btn btn-primary btn-sm" href="{{ route('dashboard.master.add', ['type' => 'product']) }}"><i class="fa fa-plus"></i> Add New</a> --}}

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-plus"></i> Add New
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('dashboard.master.add', ['type' => 'product-mart']) }}"> Mart Product</a></li>
                                <li><a href="{{ route('dashboard.master.add', ['type' => 'product-restaurant']) }}"> Restaurant Product</a></li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Image</th>
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
    </section>

    <div class="modal fade" id="brandmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.master.submit')}}" method="POST" id="brandform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" value="" name="name" class="form-control" placeholder="Enter Brand Name" required>
                        </div>

                        {{-- <div class="form-group">
                            <label>Icon</label>
                            <input type="file" value="" name="icon" class="form-control" accept="image/*" required>
                        </div> --}}
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

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'productmaster'])}}",
                type: "POST",
                data:function( d )
                {
                    d.type = $('#searchform').find('[name="type"]').val();
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
                    data:'type',
                    name: 'type',
                    render: function(data, type, full, meta){
                        return `<span class="text-uppercase">` + data + `</span>`
                    },
                },
                {
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'category',
                    name: 'category.name',
                    render: function(data, type, full, meta){
                        return data.name
                    },
                },
                {
                    data:'brand',
                    name: 'brand.name',
                    render: function(data, type, full, meta){
                        if (data){
                            return data.name
                        }else{
                            return 'N/A'
                        }
                       
                    },
                },
                {
                    data:'image',
                    name: 'image',
                    render: function(data, type, full, meta){
                        return `<a href="` + full.image_path + `" data-toggle="lightbox">\
                                    <img class="datatable-icon" src="` + full.image_path + `">\
                                </a>`
                    },
                    searchable: false,
                    orderable: false,
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
                                    <input type="checkbox" ` + checked + ` onChange="changeStatus(` + full.id + `)">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        // html += `<a class="btn btn-xs btn-info mg" href="{{route('dashboard.products.view')}}/`+full.id+`"><i class="fa fa-eye"></i></a>`;

                        /* @if(Myhelper::can('edit_product_master')) */
                            html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.master.edit', ['type'=>'product'])}}/`+full.id+`"><i class="fa fa-edit"></i></a>`;
                        /* @endif */

                        /* @if(Myhelper::can('delete_product_master')) */
                            html += `<a class="btn btn-xs btn-danger mg" href="javascript:;" onclick="deleteitem('`+full.id+`')"><i class="fa fa-trash"></i></a>`;
                        /* @endif */

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            "order": [
                [0, 'asc']
            ]
        });

        function changeStatus(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.master.submit')}}",
                    data: {"_token" : "{{csrf_token()}}", "operation" : "product-changestatus", "id" : id},
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
                            url: "{{ route('dashboard.master.submit') }}",
                            method: "POST",
                            data: {
                                '_token':'{{csrf_token()}}',
                                'operation':'product-delete',
                                'id':id
                            },
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
