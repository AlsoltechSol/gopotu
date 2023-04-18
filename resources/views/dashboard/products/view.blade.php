@section('pageheader', 'Products')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Products Management
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="{{route('dashboard.products.index')}}">Products Management</li>
            <li class="active">View Details</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <img class="img-responsive img-thumbnail" src="{{$product->image_path}}" alt="">

                        <h3 class="product-name text-center text-primary">{{$product->name}}</h3>

                        <p class="text-muted text-center">{{$product->short_description}}</p>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Total Sales</b> <a class="pull-right"><b>{!! config('app.currency.faicon') !!} {{ $totalsales }}</b></a>
                            </li>
                            <li class="list-group-item">
                                <b>This Month Sales</b> <a class="pull-right"><b>{!! config('app.currency.faicon') !!} {{ $monthsales }}</b></a>
                            </li>
                            <li class="list-group-item">
                                <b>Left in Cart</b> <a class="pull-right">{{$leftincart}}</a>
                            </li>
                        </ul>

                        @if(Myhelper::can('edit_product'))
                            <a href="{{route('dashboard.products.edit', ['id' => $product->id])}}" class="btn btn-primary btn-block"><b><i class="fa fa-edit"></i> Edit</b></a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#productdetails" data-toggle="tab" aria-expanded="false"><i class="fa fa-book"></i> Product Details</a></li>
                        <li class=""><a href="#productimages" data-toggle="tab" aria-expanded="false"><i class="fa fa-image"></i> Product Images</a></li>

                        @if(Myhelper::can('view_product_stock'))
                            <li class=""><a href="#stockdetails" data-toggle="tab" aria-expanded="false"><i class="fa fa-cubes"></i> Stock Details</a></li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="productdetails">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <th>Description</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{!! $product->description !!}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <hr>

                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>Category</th>
                                            <td>{{ $product->category->name }}</td>
                                        </tr>

                                        <tr>
                                            <th>Brand</th>
                                            <td>{{ $product->brand->name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="productimages">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <th>
                                                Gallery Images

                                                @if(Myhelper::can('edit_product'))
                                                    <button class="pull-right btn btn-sm btn-primary" data-toggle="modal" data-target="#dropzone-modal"><i class="fa fa-plus"></i> Add New</button>
                                                @endif
                                            </th>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="row" id="gallery-images">

                            </div>
                        </div>

                        @if(Myhelper::can('view_product_stock'))
                            <div class="tab-pane" id="stockdetails">
                                <div class="table-responsive">
                                    <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Color</th>
                                                <th>Variant</th>
                                                <th>Price</th>
                                                <th>Offered Price</th>
                                                <th>Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="dropzone-modal">
        <div class="modal-dialog">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h4 class="modal-title">Default Modal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('dashboard.products.submit') }}" method="POST" class="dropzone" id="dropzone">
                        @csrf
                        <input type="hidden" name="operation" value="product-image-upload">
                        <input type="hidden" name="id" value="{{ $product->id }}">
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .box-profile .product-name{
            margin-top: 15px;
            font-weight: 500;
        }
    </style>
@endpush

@push('script')
    <script>
        $( document ).ready(function() {
            loadGalleryImages()
        })

        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'product_variants'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{ csrf_token() }}';
                    d.product_id = '{{ $product->id }}';
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
                        return `<b style="font-size: 16px" class="text-primary">{!!config('app.currency.faicon')!!}` + data + `</b>`;
                    },
                    className: 'text-center'
                },
                {
                    data:'offeredprice',
                    name: 'offeredprice',
                    render: function(data, type, full, meta){
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
            ],
            "order": [
                [0, 'asc']
            ]
        });

        Dropzone.options.dropzone = {
            acceptedFiles: `image/*`,
            method: "post",
            init: function() {
                this.on("success", function(file, response) {
                    loadGalleryImages()
                })
            },
            error: function(file, errors) {
                showErrors(errors, file, 'dropzone');
            }
        };

        function loadGalleryImages(){
            Pace.track(function(){
                $.ajax({
                dataType: "JSON",
                    url: "{{route('dashboard.products.ajax')}}",
                    method: "POST",
                    data: {'_token' : '{{csrf_token()}}', 'product_id' : '{{ $product->id }}', 'type' : 'fetch-gallery' },
                    success: function(result){
                        var html = "";

                        if(result.images.length > 0) {
                            result.images.forEach(element => {
                                html += `<div class="col-sm-4" style="margin-bottom: 2rem">
                                    <a href="` + element.image_path + `" data-toggle="lightbox" data-gallery="gallery">
                                        <img src="` + element.image_path + `" class="img-fluid mb-2" style="width: 100%;"/>
                                    </a>`

                                /* @if(Myhelper::can('edit_product')) */
                                    html += `<button class="btn btn-block btn-danger" onclick="deleteProductImage(` + element.id + `)"><i class="fa fa-trash"></i> Delete</button>`;
                                /* @endif */

                                html += `</div>`;
                            });
                        } else{
                            html += `<div class="col-md-12">
                                        <div style="background: aliceblue; padding: 20px 0; border: 1px solid #3c8dbc;">
                                            <h5 class="text-center">No Gallery Image Uploaded</h5>
                                        </div>
                                    </div>`
                        }

                        $('#gallery-images').html(html);
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

        function deleteProductImage(productimage_id){
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
                            data: {'_token':'{{csrf_token()}}','operation':'product-image-delete','id':productimage_id},
                            success: function(data){
                                loadGalleryImages()
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

        $('[data-dismiss="modal"]').on('click', function(e){
            var dropzone = Dropzone.forElement("#dropzone");
            dropzone.removeAllFiles(true);
        })
    </script>
@endpush
