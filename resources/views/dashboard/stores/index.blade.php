@section('pageheader', 'Stores')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Stores Management
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
            <li class="">Stores Management</li>
            <li class="active">View All</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Stores</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Merchant</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Featured</th>
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
@endsection

@push('style')
    <style>

    </style>
@endpush

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'stores'])}}",
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
                    data:'user',
                    name: 'user.name',
                    render: function(data, type, full, meta){
                        return `${data?.name} <b>(#${data?.id})</b>`;
                    },
                },
                {
                    data:'shop_name',
                    name: 'shop_name',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'shop_location',
                    name: 'shop_location',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'is_featured',
                    name: 'is_featured',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + `, 'changefeatured')">
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

                     
                            html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.shopsettings.index')}}/`+full.id+`"><i class="fa fa-edit"></i></a>`;
                      

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

        function changeAction(id, operation = "changestatus"){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.stores.submit')}}",
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
