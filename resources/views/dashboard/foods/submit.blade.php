@section('pageheader', 'Restaurant Foods')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Restaurant Foods Management
            <small>{{isset($product) ? 'Edit' : 'Add New'}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class=""><a href="{{route('dashboard.foods.index')}}">Restaurant Foods</a></li>
            <li class="active"><small>{{isset($product) ? 'Edit' : 'Add New'}}</small></li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{!! isset($product) ? 'Edit Food' : 'Add New Food' !!}</h3>

                <div class="box-tools pull-right">
                    {{-- Tools --}}
                </div>
            </div>
            <form action="{{route('dashboard.foods.submit')}}" method="POST" id="productform">
                <div class="box-body">
                    {{-- {{dd($product->food_type)}} --}}
                    @csrf
                    <input type="hidden" name="operation" value="{{isset($product) ? 'edit' : 'new'}}">
                    <input type="hidden" name="id" value="{{isset($product) ? $product->id : ''}}">
                    <input type="hidden" name="master_id" value="{{ $product_master->id }}">

                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{$product_master->image_path}}" class="img-thumbnail masterproduct-image">
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Name</th>
                                            <td>{{$product_master->name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Category</th>
                                            <td>{{$product_master->category->name ?? "N/A"}}</td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>{{$product_master->brand->name ?? "N/A"}}</td>
                                        </tr>
                                        <input type="hidden" id="category_id" value="{{$product_master->category->id}}">
                                        <input type="hidden" id="product_id" value="{{$product_master->id}}">
                                        <tr>
                                            <th>Description</th>
                                            <td>{!! $product_master->description ?? "N/A" !!}</td>
                                        </tr>
                                        <tr>
                                            <th>Scheme</th>
                                           
                                            @if ($product_master->scheme_id)
                                                <td>{!! ucwords($product_master->scheme->name) ?? "N/A" !!}</td>
                                            @else
                                                <td>{!! ucwords($product_master->category->scheme->name) ?? "N/A" !!}</td>
                                            @endif
                                          
                                            <td></td>
                                           
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"><hr></div>

                              
                        @if(!isset($product) || (isset($product) ))
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label" style="margin: 8px 0;">Scheme <span class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <select onchange="changeScheme(this)" id="scheme_change" name="scheme_id" class="form-control">
                                        @foreach ($schemes as $item)
                                            <option {{$product_master->scheme_id == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{ucwords($item->name)}}</option>
                                        @endforeach
                                       
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                     @if((isset($product) || !isset($product)))
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" style="margin: 8px 0;">Food Type <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select name="food_type" class="form-control">
                                            <option {{isset($product) &&  $product->food_type == 'veg' ? 'selected' : ''}} value="veg">Veg</option>
                                            <option {{isset($product) &&  $product->food_type == 'nonveg' ? 'selected' : ''}} value="nonveg">Non Veg</option>
                                            <option {{isset($product) &&  $product->food_type == 'veg/nonveg' ? 'selected' : ''}} value="veg/nonveg">Veg/Non Veg</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                    

                        {{-- @if((isset($product) || !isset($product)))
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label" style="margin: 8px 0;">Product Availability <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select name="availability" class="form-control">
                                            <option {{isset($product) &&  $product->availability == 'instock' ? 'selected' : ''}} value="instock">LIVE PRODUCT</option>
                                            <option {{isset($product) &&  $product->availability == 'comingsoon' ? 'selected' : ''}} value="comingsoon">COMING SOON PRODUCT</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif --}}
                    </div>

                    {{-- <div class="box-header bg-primary with-border heading-label text-center product-av-blocks">
                        <h3 class="box-title text-uppercase text-bold">Product Variant</h3>
                    </div> --}}

                    {{-- <div class="row product-av-blocks">
                       

                        <div class="form-group col-md-12">
                            <label>Product Variants</label>
                            <select name="available_variant" class="form-control select2" {{ ( isset($product) && $product->variant ) ? 'disabled' : '' }}  style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                @foreach ($attributes as $item)
                                    <option {{ ( isset($product) && $product->variant == $item->slug ) ? 'selected' : '' }} value="{{$item->slug}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="variant-options">
                            @if (isset($product) && $product->variant)
                                <div class="form-group col-md-6">
                                    <label class="text-capitalize">Available {{$product->variant}}</label>
                                    <select name="variants[]" class="form-control select-tags" multiple data-placeholder="Select available {{$product->variant}}" onchange="variant_color_changed(this)">
                                        @if ($product->variant_options)
                                            @php
                                                $attr_variants = \App\Model\ProductAttributeVariant::with('attribute')->whereHas('attribute', function ($q) use ($product) {
                                                    $q->where('slug', $product->variant);
                                                })->orderBy('created_at', 'ASC')->get();
                                            @endphp
                                            @foreach ($attr_variants as $item)
                                                <option value="{{$item->name}}" {{in_array($item->name, $product->variant_options) ? 'selected' : ''}}>{{$item->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div> --}}


                    <div class="row product-av-blocks">
                        <div class="col-md-12" id="product-variants-section">
                            <table class="table table-bordered">
                                <thead class="bg-primary">
                                    <th class="text-center">MRP</th>
                                    <th class="text-center">Merchant Selling Price</th>
                                </thead>

                                <tbody>
                                    @if (isset($product))
                                        @foreach ($product->product_variants as $item)
                                            <tr id="row-{{ $item->color ? str_replace('#', '', $item->color) : 'null' }}-{{ $item->variant ? str_replace(':', '-', $item->variant) : 'null' }}">
                                                <td>
                                                    <input type="hidden" name="variant_id[]" value="{{$item->id}}">

                                                    <div class="input-group">
                                                        <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>
                                                            <input type="number" name="price[]" class="form-control" placeholder="" value="{{$item->price}}">
                                                        <span class="input-group-addon">.00</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>
                                                            <input type="number" name="offeredprice[]" class="form-control" placeholder="" value="{{$item->offeredprice}}">
                                                        <span class="input-group-addon">.00</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>
                                                        <input type="number" name="price[]" class="form-control" placeholder="" value="">
                                                    <span class="input-group-addon">.00</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>
                                                        <input type="number" name="offeredprice[]" class="form-control" placeholder="" value="">
                                                    <span class="input-group-addon">.00</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .form-group {
            min-height: 62px;
        }

        .masterproduct-image{
            max-width: 250px;
        }
    </style>
@endpush

@push('script')
    <script>
        $(function () {
            // CKEDITOR.replace('ck-editor');
        });

        $( document ).ready(function() {
            $('.select-tags').select2({
                // tags: true
            });
        })

        $('#productform').validate({
            rules: {

            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                error.insertAfter( element.closest( ".dont-show" ) );
            },
            submitHandler: function() {
                for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                var form = $('#productform');

                if($('#product-variants-section tbody tr').length < 1){
                    notify('You need to add a single variant for any product.', 'warning')
                    form.find('button[type="submit"]').button('reset');
                    return;
                }

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            // form.find('button[type="submit"]').button('reset');
                            // location.reload();

                            location.replace("{{ route('dashboard.foods.index') }}");
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function variantRowDelete(ele){
            var del_row = $(ele).closest('tr');
            if(del_row && $('#product-variants-section tbody tr').length > 1){
                del_row.remove();
            } else{
                notify('You need to add a single variant for any product.', 'warning')
            }
        }

        function changeScheme(src) {

            var product_id = $('#product_id').val();
            // let pilot = $('#pilot').find(":selected").val();
            let scheme_id = src.value;

            $.ajax({
                url: `/update-product/${product_id}`,
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    scheme_id: scheme_id,
                    
                },
                success: function(response) {
                    
                    console.log(response);

                },
                error: function(response) {
                    
                },
            });
        }
    </script>
@endpush
