@section('pageheader', 'Master Products')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Master Products Management
            <small>{{isset($product) ? 'Edit' : 'Add New'}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Master</li>
            <li class=""><a href="{{route('dashboard.master.index', ['type' => 'product'])}}">Master Products</a></li>
            <li class="active"><small>{{isset($product) ? 'Edit' : 'Add New'}}</small></li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{!! isset($product) ? 'Edit Product' : 'Add New Product' !!} <small class="text-capitalize">{{$type}}</small></h3>

                <div class="box-tools pull-right">
                    {{-- Tools --}}
                </div>
            </div>
            <form action="{{route('dashboard.master.submit')}}" method="POST" id="productform">
                <div class="box-body">
                    @csrf
                    <input type="hidden" name="operation" value="product-{{isset($product) ? 'edit' : 'new'}}">
                    <input type="hidden" name="id" value="{{isset($product) ? $product->id : ''}}">
                    <input type="hidden" name="type" value="{{$type}}">

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="" name="name" value="{{isset($product) ? $product->name : ''}}">
                        </div>

                        <div class="form-group col-md-6">
                            @if(isset($product) && $product->image != null)
                                <a href="{{ $product->image_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Image</a>
                            @endif
                            <label>Product Image {!! !isset($product) ? '<span class="text-danger">*</span>' : '' !!} &nbsp;&nbsp;<code>Dimension - 500 X 500</code></label>
                            <input type="file" class="form-control" name="product_image">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Select Category <span class="text-danger">*</span></label>
                            {{-- @if(isset($product)) --}}
                                <select onchange="selectCategory(this)" name="category_id" class="form-control select2" style="width: 100%">
                                    <option value="">Select from the dropdown</option>
                                    @foreach ($categories as $item)
                                        <option {{ (isset($product) && ($product->category_id == $item->id) ) ? 'selected' : '' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                        {{-- <optgroup label="{{ $item->name }}"> --}}
                                            @foreach ($item->sub_categories as $l1_cat)
                                                <option {{ ( isset($product) && ($product->category_id == $l1_cat->id) ) ? 'selected' : '' }} value="{{ $l1_cat->id }}">- {{ $l1_cat->name }}</option>
                                            @endforeach
                                        {{-- </optgroup> --}}
                                    @endforeach
                                </select>
                            {{-- @else --}}
                                {{-- <select name="category_id" class="form-control"></select> --}}
                            {{-- @endif --}}
                        </div>

                        <div class="form-group col-md-6">
                            <label>Select Brand <span class="text-danger">*</span></label>
                            <select name="brand_id" class="form-control brands-select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                @foreach ($brands as $item)
                                    <option {{ ( isset($product) && ($product->brand_id == $item->id) ) ? 'selected' : '' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="scheme" class="form-group col-md-12">
                            {{-- <label>Select Scheme <span class="text-danger">*</span></label> --}}
                                 
                       
                        </div>

                        <div class="form-group col-md-12">
                            <label>Product Descripion <span class="text-danger">*</span></label>
                            <textarea id="ck-editor" name="description">{!! isset($product) ? $product->description : '' !!}</textarea>
                        </div>

                        <div class="form-group col-md-6">
                            <label>TAX Rate (In Percentage)</label>
                            <input type="text" class="form-control" placeholder="Optional" name="tax_rate" value="{{isset($product) ? $product->tax_rate : ''}}">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>
                </div>
            </form>
        </div>

        @isset($product)
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Gallery Images
                    </h3>

                    <div class="box-tools pull-right">
                        <button class="pull-right btn btn-sm btn-primary" data-toggle="modal" data-target="#dropzone-modal"><i class="fa fa-plus"></i> Add New</button>
                    </div>
                </div>

                <div class="box-body" id="gallery-images">

                </div>
            </div>

            <div class="modal fade" id="dropzone-modal">
                <div class="modal-dialog">
                    <div class="modal-content modal-lg">
                        <div class="modal-header">
                            <h4 class="modal-title">Upload Product Images</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('dashboard.master.submit') }}" method="POST" class="dropzone" id="dropzone">
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
        @endisset
    </section>
@endsection

@push('style')
    <style>
        .form-group {
            min-height: 62px;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            color: initial;
        }

        /* .select2-results__group {
            display: none !important;
        } */
    </style>
@endpush

@push('script')
    <script>

        var scheme = document.getElementById('scheme');
        function selectCategory(src){

            $.ajax({
    type: "get",
    url: `/category-schemes/${src.value}`,
    success: function(res) {
        console.log(typeof res.scheme.id);
        var dynamicValue = 2; // or set dynamicValue to the value you want to use
        // res.scheme.id.toString();
        console.log(typeof dynamicValue);

        // console.log(res.scheme.id );
        // $('.scheme').remove();
        // $('#scheme').append(`
        //     <select name="scheme_id" class="form-control brands-select2 scheme" style="width: 100%">
        //         <option value="">Select scheme from the dropdown</option>
        //         @foreach ($schemes as $item)
        //             <option {{$item->id == @dynamicValue ? 'selected' : ''}} value="{{ $item->id }}">{{ $item->name }}</option>
        //         @endforeach
        //     </select>
        // `);


    // var schemes = '{{$schemes}}';
    var schemes =  {!! json_encode($schemes) !!}
    console.log(schemes);
    var dynamicValue = res.scheme.id; // Replace with the value you want to use
    var options = '<option value="">Select scheme from the dropdown</option>';
    schemes.forEach(function(scheme) {
        options += '<option ' + (scheme.id == dynamicValue ? 'selected' : '') + ' value="' + scheme.id + '">' + scheme.name + '</option>';
    });
    $('#scheme').html('<select name="scheme_id" class="form-control brands-select2 scheme" style="width: 100%">' + options + '</select>');


        
    },
});
             

                
        }
        
        $(function () {
            CKEDITOR.replace('ck-editor');
        });

        $( document ).ready(function() {
            /* @if( !isset($product) ) */
                // fetchCategories();
            /* @endif */

            $('.select-tags').select2({
                // tags: true
            });

            $('.brands-select2').select2({
                tags: true
            });

            /* @isset($product) */
                loadGalleryImages()
            /* @endif */
        })

        $('#productform').validate({
            rules: {
                name:{
                    required: true,
                },
                category_id:{
                    required: true,
                },
                brand_id:{
                    required: true,
                },
                description:{
                    required: true,
                },
                tax_rate:{
                    required: false,
                    number: true,
                    min: 0,
                    max: 100,
                },
                product_image:{
                    required: function(element) {
                        return $("#productform").find('[name=operation]').val() == 'new';
                    }
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
                for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                var form = $('#productform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form.find('button[type="submit"]').button('reset');
                            location.reload();
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function fetchCategories(){
            $('[name="category_id"]').select2({
                placeholder: "Select from the dropdown",
                ajax: {
                    dataType: "JSON",
                    url: "{{route('dashboard.products.ajax')}}",
                    method: "POST",
                    cache: true,
                    data: function(params) {
                        return {
                            '_token' : '{{csrf_token()}}',
                            'type' : 'fetch-categories',
                            "searchtext": params.term, // search term
                            "product_id": '{{ isset($product) ? $product->id : null }}',
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: response.categories
                        };
                    },
                }
            });
        }

        /* @isset($product) */
            function loadGalleryImages(){
                Pace.track(function(){
                    $.ajax({
                    dataType: "JSON",
                        url: "{{route('dashboard.master.ajax')}}",
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
                                html += `<div class="col-md-12" style="padding: 0">
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

            $('[data-dismiss="modal"]').on('click', function(e){
                var dropzone = Dropzone.forElement("#dropzone");
                dropzone.removeAllFiles(true);
            })

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
                                url: "{{route('dashboard.master.submit')}}",
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
        /* @endif */
    </script>
@endpush
