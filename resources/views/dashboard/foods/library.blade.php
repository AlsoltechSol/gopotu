@section('pageheader', 'Restaurant Foods')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Restaurant Foods Management
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
            <li class=""><a href="{{route('dashboard.foods.index')}}">Restaurant Foods</a></li>
            <li class="active">Library</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Available Foods</h3>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
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

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'restaurantproductlibrary'])}}",
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
                    render: function(data, type, full, meta){
                        var html = '';

                        /* @if(Myhelper::can('add_food') && Myhelper::hasRole(['branch'])) */
                            html += `<a class="btn btn-xs btn-primary mg" href="{{route('dashboard.foods.add')}}/`+full.id+`"><i class="fa fa-plus"></i> ADD</a>`;
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
    </script>
@endpush
