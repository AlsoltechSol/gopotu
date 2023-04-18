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
                                        <tr>
                                            <th>Description</th>
                                            <td>{!! $product_master->description ?? "N/A" !!}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"><hr></div>
                    </div>

                    <div class="row product-av-blocks">
                        <div class="col-md-12" id="product-variants-section">
                            <table class="table table-bordered">
                                <thead class="bg-primary">
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Offered Price</th>
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
    </script>
@endpush
