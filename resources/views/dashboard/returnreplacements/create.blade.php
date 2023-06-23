@section('pageheader', 'Return Replacements')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Return Replacements Management
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class=""><a href="{{ route('dashboard.returnreplacements.index') }}">Return Replacements Management</a></li>
            <li class="active">Create</li>
        </ol>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Add New Request</h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <form action="{{ route('dashboard.returnreplacements.submit') }}" method="POST" id="submitForm">
                <div class="box-body">
                    @csrf
                    <input type="hidden" name="operation" value="createreturnreplacementrequest">

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Order <span class="text-danger">*</span></label>
                            <select name="order_id" class="form-control selec2-order-select"></select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control select2">
                                <option value="">Select from the dropdown</option>

                                @if (Myhelper::can(['create_return_request']))
                                    <option value="return">Return</option>
                                @endif

                                @if (Myhelper::can(['create_replacement_request']))
                                    <option value="replace">Replace</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped" id="order-items-table">
                                <thead>
                                    <tr class="bg-primary">
                                        <th colspan="100%" class="text-center">Select Return/Replacement Item(s)</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">
                                            {{-- <input type="checkbox"> --}}
                                        </th>
                                        <th>Product</th>
                                        <th>Item Price</th>
                                        <th>Quantity</th>
                                        <th>Total Price</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center">No records found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>

                    <h5 class="pull-right">Delivery Partner Fees : <b>{!! config('app.currency.htmlcode') !!}<span id="delivery-charge">0</span></b></h5>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .selec2-order-select__ordercode {
            font-weight: bold;
            font-size: 15px;
        }

        .selec2-order-select__ordercode small {
            font-weight: normal;
        }
    </style>
@endpush

@push('script')
    <script>
        $('[name="order_id').select2({
            ajax: {
                url: "{{route('dashboard.returnreplacements.ajax')}}",
                dataType: 'JSON',
                method: "POST",
                delay: 10,
                data: function (params) {
                    return {
                        '_token': '{{ csrf_token() }}',
                        'type': "ordersearch",
                        'keyword': params.term, // search term
                        // 'keyword': '9132457358', // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.orders ?? [],
                    };
                },
                cache: true
            },
            placeholder: 'Search for completed orders by Order Code',
            minimumInputLength: 1,
            templateResult: formatSelectTwoOrder,
            templateSelection: formatOrderSelectTwoSelection
        });

        function formatSelectTwoOrder (order) {
            if (order.loading) {
                return order.text;
                3+
            }

            var $container = $(
                `<div class='selec2-order-select clearfix'>
                    <div class='selec2-order-select__meta'>
                        <div class="selec2-order-select__ordercode">${order.code} <small class="text-uppercase">(${order.type})</small></div>
                        <div class="selec2-order-select__payableamount">Amount: <b>{!! config('app.currency.htmlcode') !!} ${order.payable_amount}</b></div>
                    </div>
                </div>`
            );

            return $container;
        }

        function formatOrderSelectTwoSelection (order) {
            let html = ''
            if(order.id){
                order_products = order.order_products ?? [];

                if(order_products.length > 0){
                    let cntr = 1
                    order_products.forEach(element => {
                        html += `<tr>
                                    <td class="text-center"><input type="checkbox" name="order_products[]" value="${element.id}"></td>
                                    <td>
                                        {{-- ${ (element.product?.details?.image_path) ? '<img class="datatable-icon" src="' + element.product.details.image_path + '">' : '' } --}}

                                        ${element.product.details.name}
                                        ${ (element?.variant_selected?.color_name) ? '&nbsp;| Color: ' + element.variant_selected.color_name : '' }
                                        ${ (element?.variant_selected?.variant_name) ? '&nbsp;| ' + element.variant_selected.variant_name : '' }
                                    </td>
                                    <td>{!! config('app.currency.htmlcode') !!} ${element.price?.toFixed(2)}</td>
                                    <td>${element.quantity}</td>
                                    <td>{!! config('app.currency.htmlcode') !!} ${element.sub_total?.toFixed(2)}</td>
                                </tr>`;

                        cntr++;
                    });
                } else{
                    html += `<tr><td colspan="100%" class="text-center">No records found</td></tr>`;
                }
            } else{
                html += `<tr><td colspan="100%" class="text-center">No records found</td></tr>`;
            }

            $('#order-items-table').find('tbody').html(html)

            return order.code || order.text;
        }

        $('#submitForm').validate({
            rules: {
                order_id : { required: true, },
                type : { required: true, },
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
                var form = $('#submitForm');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            form[0].reset();
                            form.find('[name="order_id"]').select2("val", " ");
                            form.find('.select2').val("").trigger("change");

                            notify(data.status, 'success');
                            form.find('button[type="submit"]').button('reset');
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });


        $('[name="order_id').on('change', function(e){
            calculateDeliveryCharge();
        })

        $('[name="type').on('change', function(e){
            calculateDeliveryCharge();
        })

        function calculateDeliveryCharge(){
            const order_id = $('[name="order_id').val();
            const type = $('[name="type').val();

            if(order_id && type){
                Pace.track(function(){
                    $.ajax({
                        dataType: "JSON",
                        url: "{{route('dashboard.returnreplacements.ajax')}}",
                        method: "POST",
                        data: {'_token' : '{{csrf_token()}}', 'type' : 'calculate-delivery-charge', 'order_id' : order_id, 'mode' : type },
                        success: function(result){
                            const data = result.data
                            $('#delivery-charge').text(data.delivery_charge);
                        }, error: function(errors){
                            showErrors(errors);
                            $('#delivery-charge').text(0);
                        }
                    });
                });
            } else{
                $('#delivery-charge').text(0);
            }
        }
    </script>
@endpush
