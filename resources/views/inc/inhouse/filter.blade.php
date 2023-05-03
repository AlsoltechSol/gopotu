<div class="box">
    <form id="searchform">
        <div class="box-body">
            <div class="row">
                @if(isset($filteroptions['daterange']) && $filteroptions['daterange'] == true)
                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <input type="text" class="form-control daterange" name="daterange" placeholder="Filter by Date Range">
                        </div>
                    </div>

                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <select id="datefilter" class="form-control select2">
                                <option value="">Select from the dropdown <span style="color: red !important" class="text-danger">*</span></option>
                                <option value="{{ Carbon\Carbon::now()->format('m/d/Y') }} - {{ Carbon\Carbon::now()->format('m/d/Y') }}">Daily</option>
                                <option value="{{ Carbon\Carbon::now()->firstOfMonth()->format('m/d/Y') }} - {{ Carbon\Carbon::now()->format('m/d/Y') }}">Monthly</option>
                                <option value="{{ Carbon\Carbon::now()->startOfYear()->format('m/d/Y') }} - {{ Carbon\Carbon::now()->format('m/d/Y') }}">Annually</option>
                            </select>
                        </div>
                    </div>
                   
                @endif

                @if(isset($filteroptions['userfilter']) && $filteroptions['userfilter'] == true)
                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </div>
                            <select name="user_id" class="form-control select2" style="width: 100%">
                                <option value="">Select User</option>
                            </select>
                        </div>
                    </div>
                @endif

                @if(isset($filteroptions['cattypefilter']) && $filteroptions['cattypefilter'] == true)
                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <select name="type" class="form-control select2" style="width: 100%">
                                <option value="">Select Type</option>
                                <option value="restaurant">Restaurant Orders</option>
                                <option value="mart">GoPotu Mart Orders</option>
                            </select>
                        </div>
                    </div>
                @endif

                @if(isset($filteroptions['orderstatusfilter']) && $filteroptions['orderstatusfilter'] == true)
                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <select name="orderstatus" class="form-control select2" style="width: 100%">
                                <option value="">Select Status</option>

                                @php $orderstatus = config('orderstatus.options') @endphp
                                @foreach ($orderstatus as $key => $value)
                                    @if (!in_array($key, ['paymentinitiated', 'paymentfailed', 'returned']))
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                
                @if(isset($filteroptions['mobilenofilter']) && $filteroptions['mobilenofilter'] == true)
                    <div class="form-group col-lg-4">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </div>
                            <select name="cust_mobile" class="form-control select2" style="width: 100%">
                                <option value="">Select Mobile</option>
                            </select>
                        </div>
                    </div>
                @endif

                @if(isset($filteroptions['orderidfilter']) && $filteroptions['orderidfilter'] == true)
                <div class="form-group col-lg-4">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-list"></i>
                        </div>
                        <select name="order_id" class="form-control select2" style="width: 100%">
                            <option value="">Select OrderId</option>
                        </select>
                    </div>
                </div>
            @endif

            @if(isset($filteroptions['cityfilter']) && $filteroptions['cityfilter'] == true)
            <div class="form-group col-lg-4">
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-city"></i>
                    </div>
                    <select name="city" class="form-control select2" style="width: 100%">
                        <option value="">Select City</option>
                    </select>
                </div>
            </div>
        @endif

{{--         
        @if(isset($filteroptions['shopfilter']) && $filteroptions['shopfilter'] == true)
        <div class="form-group col-lg-4">
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-city"></i>
                </div>
                <select name="city" class="form-control select2" style="width: 100%">
                    <option value="">Select Shop</option>
                </select>
            </div>
        </div>
    @endif --}}


                

            
                @if(isset($customfilter) && $customfilter == true)
                    @foreach ($customfilter_array as $settings)
                        <div class="form-group col-lg-4">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-info-circle"></i>
                                </div>
                                @switch($settings->type)
                                    @case('select2')
                                        <select name="{{ $settings->name }}" class="form-control select2" style="width: 100%">
                                            @foreach ($settings->options as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="box-footer text-right">
            <button class="btn btn-success" type="submit"><i class="fa fa-search"></i> Search</button>
            {{-- <button class="btn btn-primary export" data-url="{{route('dashboard.fetchdata', ['type' => 'orders'])}}/export" type="button"><i class="fa fa-download"></i> Export</button> --}}
            <button class="btn btn-danger reset" type="button"><i class="fa fa-times"></i> Reset</button>
        </div>
    </form>
</div>

@push('script')
    <script>
        $(document).ready(function() {
            /* @if(isset($filteroptions['userfilter']) && $filteroptions['userfilter'] == true) */
                bindUserIdFields();
                bindMobileFields();
                bindOrderIdFields();
                bindCityFields();
            /* @endif */
        });

        /* @if(isset($filteroptions['userfilter']) && $filteroptions['userfilter'] == true) */
            function bindUserIdFields() {
                Pace.track(function() {
                    $.ajax({
                        url: "{{ route('dashboard.fetchdata', ['type' => 'user', 'fetch' => 'select']) }}",
                        method: "GET",
                        data: {
                            'token': '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            console.log(data);
                            var result = data.result;
                            $('[name="user_id"]').html("");
                            $('[name="user_id"]').append('<option value="">Select User</option>');

                            for (var key in result) {
                                $('[name="user_id"]').append('<option value="' + key + '">' + result[key] + ' (#' + key + ')</option>');
                            }

                            $('[name="user_id"]').val("").trigger("change");
                        },
                        error: function(errors) {
                            showErrors(errors);
                        }
                    });
                });
            }

            function bindMobileFields() {
                Pace.track(function() {
                    $.ajax({
                        url: "{{ route('dashboard.fetchdata', ['type' => 'mobile', 'fetch' => 'select']) }}",
                        method: "GET",
                        data: {
                            'token': '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            console.log(data);
                            var result = data.result;
                            $('[name="cust_mobile"]').html("");
                            $('[name="cust_mobile"]').append('<option value="">Select Mobile</option>');

                            for (var key in result) {
                                $('[name="cust_mobile"]').append('<option value="' + result[key] + '">' + result[key] + ' (#' + key + ')</option>');
                            }

                            $('[name="cust_mobile"]').val("").trigger("change");
                        },
                        error: function(errors) {
                            showErrors(errors);
                        }
                    });
                });
            }

            function bindOrderIdFields() {
                Pace.track(function() {
                    $.ajax({
                        url: "{{ route('dashboard.fetchdata', ['type' => 'order_id', 'fetch' => 'select']) }}",
                        method: "GET",
                        data: {
                            'token': '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            console.log(data);
                            var result = data.result;
                            $('[name="order_id"]').html("");
                            $('[name="order_id"]').append('<option value="">Select OrderId</option>');

                            for (var key in result) {
                                $('[name="order_id"]').append('<option value="' + result[key] + '">' + result[key] + '</option>');
                            }

                            $('[name="order_id"]').val("").trigger("change");
                        },
                        error: function(errors) {
                            showErrors(errors);
                        }
                    });
                });
            }

            function bindCityFields() {
                Pace.track(function() {
                    $.ajax({
                        url: "{{ route('dashboard.fetchdata', ['type' => 'city', 'fetch' => 'select']) }}",
                        method: "GET",
                        data: {
                            'token': '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            console.log(data);
                            var result = data.result;
                            $('[name="city"]').html("");
                            $('[name="city"]').append('<option value="">Select City</option>');

                            for (var key in result) {
                                $('[name="city"]').append('<option value="' + result[key] + '">' + result[key] + '</option>');
                            }

                            $('[name="city"]').val("").trigger("change");
                        },
                        error: function(errors) {
                            showErrors(errors);
                        }
                    });
                });
            }
        /* @endif */

        /* @if(isset($filteroptions['daterange']) && $filteroptions['daterange'] == true) */
            $('.daterange').daterangepicker({
                autoApply: true,
                showDropdowns: true,
                maxDate: new Date(),
            })

            $('#datefilter').on('change', function(e) {
                $('[name="daterange"]').val($(this).val());
                $('[name="daterange"]').daterangepicker("refresh");
            })
        /* @endif */

        $('.reset').on('click', function() {
            var form = $('form#searchform');
            form[0].reset();
            form.find('.select2').val("").trigger("change");
            form.find('[name="daterange"]').daterangepicker("refresh");
            $('#my-datatable').dataTable().api().ajax.reload();
        });

        $('form#searchform').submit(function(e) {
            e.preventDefault();
            $('#my-datatable').dataTable().api().ajax.reload();
        });
    </script>
@endpush
