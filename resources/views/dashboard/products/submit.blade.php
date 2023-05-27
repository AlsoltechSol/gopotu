@section('pageheader', 'Mart Products')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Mart Products Management
            <small>{{isset($product) ? 'Edit' : 'Add New'}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class=""><a href="{{route('dashboard.products.index')}}">Mart Products</a></li>
            <li class="active"><small>{{isset($product) ? 'Edit' : 'Add New'}}</small></li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{!! isset($product) ? 'Edit Product' : 'Add New Product' !!}</h3>

                <div class="box-tools pull-right">
                    {{-- Tools --}}
                </div>
            </div>
            <form action="{{route('dashboard.products.submit')}}" method="POST" id="productform">
                <div class="box-body">
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
                                    <label class="col-sm-2 col-form-label" style="margin: 8px 0;">Product Availability <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select name="availability" class="form-control">
                                            <option {{isset($product) &&  $product->availability == 'instock' ? 'selected' : ''}} value="instock">LIVE PRODUCT</option>
                                            <option {{isset($product) &&  $product->availability == 'comingsoon' ? 'selected' : ''}} value="comingsoon">COMING SOON PRODUCT</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif


                        {{-- <div class="col-md-12 product-av-blocks"><hr></div> --}}
                    </div>

                    <div class="box-header bg-primary with-border heading-label text-center product-av-blocks">
                        <h3 class="box-title text-uppercase text-bold">Product Variant</h3>
                    </div>

                    <div class="row product-av-blocks">
                        <div class="form-group col-md-6">
                            <label>Available Colors</label>
                            <select name="available_colors[]" class="form-control select2" multiple data-placeholder="Select Available Colors" style="width: 100%">
                                @foreach ($colors as $item)
                                    <option {{ (isset($product) && $product->colors != null && in_array($item->code, $product->colors)) ? 'selected' : '' }} value="{{$item->code}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
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
                    </div>

                    <div class="box-header bg-primary with-border heading-label text-center product-av-blocks">
                        <h3 class="box-title text-uppercase text-bold">Product Price + Stock</h3>
                    </div>

                    <div class="row product-av-blocks">
                        <div class="col-md-12" id="product-variants-section">
                            <table class="table table-bordered">
                                <thead>
                                    <th class="text-left">Variant</th>
                                    <th class="text-left">Color</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Offered Price</th>
                                    @if (Auth::user()->role->name != 'Merchant')
                                        <th class="text-center">Listing Price</th>                                    
                                    @endif
                                    
                                    <th class="text-center">Quantity (Stock Available)</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">Action</th>
                                </thead>

                                <tbody>
                                    @if (isset($product))
                                    
                                        @foreach ($product->product_variants as $item)
                                            <tr id="row-{{ $item->color ? str_replace('#', '', $item->color) : 'null' }}-{{ $item->variant ? str_replace(':', '-', $item->variant) : 'null' }}">
                                                <td>
                                                    <input type="hidden" name="variant_id[]" value="{{$item->id}}">

                                                    <input type="hidden" name="variant[]" value="{{$item->variant}}">
                                                    @if ($item->variant)
                                                        @php $var = explode(':', $item->variant); @endphp
                                                        <span class="text-capitalize">{{$var[0]}}</span> : <b>{{$var[1]}}</b>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="hidden" name="color[]" value="{{$item->color}}">
                                                    @if ($item->color)
                                                        <span class="color-code-disp" style="background: {{$item->color}}"></span>{{$item->color_name}}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
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
                                                @if (Auth::user()->role->name != 'Merchant')
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>
                                                            <input type="number" name="listingprice[]" class="form-control" placeholder="" value="{{$item->listingprice}}">
                                                        <span class="input-group-addon">.00</span>
                                                    </div>
                                                </td>
                                                @endif
                                                <td>
                                                    <input type="number" name="quantity[]" class="form-control" placeholder="" value="{{$item->quantity}}">
                                                </td>
                                                <td>
                                                    <input type="text" name="sku[]" class="form-control" placeholder="" value="{{$item->sku}}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" onClick="variantRowDelete(this)" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
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
            /* @if( !isset($product) || (isset($product) && count($product->product_variants) == 0) ) */
                $('[name="available_colors[]"]').trigger("change");
                $('[name="available_variant"]').trigger("change");

                // fetchCategories();
            /* @endif */

            $('.select-tags').select2({
                // tags: true
            });

            $('.brands-select2').select2({
                tags: true
            });

            $('[name="availability"]').trigger("change");
        })

        $('[name="availability"]').on('change', async function(e){
            if($(this).val() == 'comingsoon'){
                $('.product-av-blocks').hide(100);
            } else{
                $('.product-av-blocks').show(100);
            }
        })

        $('[name="available_colors[]"]').on('change', function(e){
            variant_color_changed();
        })

        $('[name="available_variant"]').on('change', async function(e){
            var html = "";
            var variant = $(this).val();

            if(variant){
                var attr_variants = await get_attribute_variants(variant);
                var options = "";

                attr_variants.forEach(element => {
                    options += `<option value="` + element.name + `">` + element.name + `</option>`
                });

                html += `<div class="form-group col-md-6">\
                        <label class="text-capitalize">Available ` + variant + `</label>\
                        <select name="variants[]" class="form-control select-tags" multiple data-placeholder="Select available ` + variant + `" onchange="variant_color_changed(this)">` + options + `</select>\
                    </div>`
            }

            $('#variant-options').html(html);
            $('.select-tags').select2({
                // tags: true
            });

            variant_color_changed();
        })

        function variant_color_changed(ele){
            var available_variant = $('[name="available_variant"]').val()
            var variants = [];

            if(available_variant){
                variants = $('[name="variants[]"]').val()
            }

            variant_section_changed(
                $('[name="available_colors[]"]').val(),
                variants
            );
        }

        async function variant_section_changed($_colors, $_variants){
            var html = "";
            var dom_exists = [];

            if($_colors.length > 0 && $_variants.length > 0){
                var available_variant = $('[name="available_variant"]').val()
                var variants = $_variants;
                var colors = await get_colors($_colors);

                colors.forEach(color => {
                    variants.forEach(variant => {
                        let rowid = `row-` + color.code.replace('#', '') + `-`+ available_variant + `-` + variant;

                        if($('#' + rowid).length == 0){
                            html += `<tr id="` + rowid + `">\
                                <td>\
                                    <input type="hidden" name="variant_id[]" value="">\

                                    <input type="hidden" name="variant[]" value="` + available_variant + `:` + variant + `">\
                                    <span class="text-capitalize">` +  available_variant + `<span>: <b>` + variant + `</b> \
                                </td>\
                                <td>\
                                    <input type="hidden" name="color[]" value="` + color.code + `">\
                                    <span class="color-code-disp" style="background: ` + color.code + `"></span>` + color.name + `\
                                </td>\
                                <td>\
                                    <div class="input-group">\
                                        <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                            <input type="number" name="price[]" class="form-control" placeholder="">\
                                        <span class="input-group-addon">.00</span>\
                                    </div>\
                                </td>\
                                <td>\
                                    <div class="input-group">\
                                        <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                            <input type="number" name="offeredprice[]" class="form-control" placeholder="">\
                                        <span class="input-group-addon">.00</span>\
                                    </div>\
                                </td>\
                                @if (Auth::user()->role->name != 'Merchant')
                                <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input type="number" name="listingprice[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    @endif
                                <td>\
                                    <input type="number" name="quantity[]" class="form-control" placeholder="">\
                                </td>\
                                <td>\
                                    <input type="text" name="sku[]" class="form-control" placeholder="" value="">\
                                </td>\
                                <td class="text-center">\
                                    <button type="button" onClick="variantRowDelete(this)" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>\
                                </td>\
                            </tr>`;
                        } else{
                            dom_exists.push( $('#' + rowid).clone() );
                        }
                    });
                });
            } else if($_colors.length > 0){
                var colors = await get_colors($_colors);
                colors.forEach(element => {
                    let rowid = `row-` + element.code.replace('#', '') + `-null`;

                    if($('#' + rowid).length == 0){
                        html += `<tr id="` + rowid + `">\
                            <td>\
                                <input type="hidden" name="variant_id[]" value="">\

                                <input type="hidden" name="variant[]" value=""> N/A\
                            </td>\
                            <td>\
                                <input type="hidden" name="color[]" value="` + element.code + `">\
                                <span class="color-code-disp" style="background: ` + element.code + `"></span>` + element.name + `\
                            </td>\
                            <td>\
                                <div class="input-group">\
                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                        <input type="number" name="price[]" class="form-control" placeholder="">\
                                    <span class="input-group-addon">.00</span>\
                                </div>\
                            </td>\
                            <td>\
                                <div class="input-group">\
                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                        <input type="number" name="offeredprice[]" class="form-control" placeholder="">\
                                    <span class="input-group-addon">.00</span>\
                                </div>\
                            </td>\
                            @if (Auth::user()->role->name != 'Merchant')
                            <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input type="number" name="listingprice[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    @endif
                            <td>\
                                <input type="number" name="quantity[]" class="form-control" placeholder="">\
                            </td>\
                            <td>\
                                <input type="text" name="sku[]" class="form-control" placeholder="" value="">\
                            </td>\
                            <td class="text-center">\
                                <button type="button" onClick="variantRowDelete(this)" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>\
                            </td>\
                        </tr>`;
                    } else{
                        dom_exists.push( $('#' + rowid).clone() );
                    }
                });
            } else if($_variants.length > 0){
                var available_variant = $('[name="available_variant"]').val()
                var variants = $_variants;

                variants.forEach(element => {
                    let rowid = `row-null-`+ available_variant + `-` + element;

                    if($('#' + rowid).length == 0){
                        html += `<tr id="` + rowid + `">\
                            <td>\
                                <input type="hidden" name="variant_id[]" value="">\

                                <input type="hidden" name="variant[]" value="` + available_variant + `:` + element + `">\
                                <span class="text-capitalize">` +  available_variant + `<span>: <b>` + element + `</b> \
                            </td>\
                            <td>\
                                <input type="hidden" name="color[]" value=""> N/A\
                            </td>\
                            <td>\
                                <div class="input-group">\
                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                        <input type="number" name="price[]" class="form-control" placeholder="">\
                                    <span class="input-group-addon">.00</span>\
                                </div>\
                            </td>\
                            <td>\
                                <div class="input-group">\
                                    <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                        <input type="number" name="offeredprice[]" class="form-control" placeholder="">\
                                    <span class="input-group-addon">.00</span>\
                                </div>\
                            </td>\
                            @if (Auth::user()->role->name != 'Merchant')
                            <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input type="number" name="listingprice[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    @endif
                            <td>\
                                <input type="number" name="quantity[]" class="form-control" placeholder="">\
                            </td>\
                            <td>\
                                <input type="text" name="sku[]" class="form-control" placeholder="" value="">\
                            </td>\
                            <td class="text-center">\
                                <button type="button" onClick="variantRowDelete(this)" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>\
                            </td>\
                        </tr>`;
                    } else{
                        dom_exists.push( $('#' + rowid).clone() );
                    }
                });
            } else{
                $('[name="variant[]"]').val('');
                $('[name="color[]"]').val('');
                $('[name="price[]"]').val('');
                $('[name="offeredprice[]"]').val('');
                $('[name="listingprice[]"]').val('');
                $('[name="quantity[]"]').val('');

                html += `<tr>\
                    <td>\
                        <input type="hidden" name="variant_id[]" value="">\

                        <input type="hidden" name="variant[]" value=""> N/A\
                    </td>\
                    <td>\
                        <input type="hidden" name="color[]" value=""> N/A\
                    </td>\
                    <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input type="number" name="price[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input type="number" name="offeredprice[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    @if (Auth::user()->role->name != 'Merchant')
                    <td>\
                        <div class="input-group">\
                            <span class="input-group-addon">{!!config('app.currency.faicon')!!}</span>\
                                <input value="" type="number" name="listingprice[]" class="form-control" placeholder="">\
                            <span class="input-group-addon">.00</span>\
                        </div>\
                    </td>\
                    @endif
                    <td>\
                        <input type="number" name="quantity[]" class="form-control" placeholder="">\
                    </td>\
                    <td>\
                        <input type="text" name="sku[]" class="form-control" placeholder="" value="">\
                    </td>\
                    <td class="text-center">\
                        <button type="button" onClick="variantRowDelete(this)" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>\
                    </td>\
                </tr>`;
            }

            $('#product-variants-section').find('tbody').html('')

            dom_exists.forEach(element => {
                element.appendTo("#product-variants-section tbody");
            });

            $('#product-variants-section').find('tbody').append(html)
        }

        function get_colors(colors){
            return new Promise(function(resolve, reject) {
                Pace.track(function(){
                    $.ajax({
                    dataType: "JSON",
                        url: "{{route('dashboard.products.ajax')}}",
                        method: "POST",
                        data: {'_token' : '{{csrf_token()}}', 'colors' : colors, 'type' : 'color-details' },
                        success: function(data){
                            resolve(data.colors);
                        }, error: function(errors){
                            showErrors(errors);
                            reject(errors);
                        }
                    });
                });
            })
        }

        function get_attribute_variants(slug){
            return new Promise(function(resolve, reject) {
                Pace.track(function(){
                    $.ajax({
                    dataType: "JSON",
                        url: "{{route('dashboard.products.ajax')}}",
                        method: "POST",
                        data: {'_token' : '{{csrf_token()}}', 'slug' : slug, 'type' : 'attribute-variants' },
                        success: function(data){
                            resolve(data.variants);
                        }, error: function(errors){
                            showErrors(errors);
                            reject(errors);
                        }
                    });
                });
            })
        }

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

                            location.replace("{{ route('dashboard.products.index') }}");
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
