@section('pageheader', 'Shop Settings')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Shop Settings <small>{{$shop->shop_name}}</small>
        </h1>
         
        @if (session('admin'))
            <div class="mt-5">

                <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
            </div>

        @endif
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Shop Settings</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Update Settings</h3>

                <div class="box-tools pull-right">
                    @if(in_array($shop->user->business_category, ['restaurant', 'mart']))
                        @if($shop->online == 1)
                            <button onclick="revertStoreOnlineStatus()" class="btn btn-success btn-sm"><i class="fa fa-store"></i> Store Open</button>
                        @else
                            <button onclick="revertStoreOnlineStatus()" class="btn btn-warning btn-sm"><i class="fa fa-store"></i> Store Closed</button>
                        @endif
                    @endif
                </div>
            </div>
            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs">
                        <li class="bg-gray active"><a href="#shopdetails" data-toggle="tab"><i class="fa fa-store-alt"></i> Shop Details</a></li>
                        <li class="bg-gray "><a href="#addressdetails" data-toggle="tab"><i class="fa fa-map-marker-alt"></i> Shop Address</a></li>
                    </ul>
                    <form action="{{route('dashboard.shopsettings.submit')}}" method="POST" id="settingsform">
                        @csrf
                        <input type="hidden" name="shop_id" value="{{$shop->id}}">
                        <div class="tab-content">
                            <div class="tab-pane active" id="shopdetails">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Shop Name <span class="text-danger">*</span></label>
                                            <input name="shop_name" value="{{$shop->shop_name}}" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Shop Tagline</label>
                                            <input name="shop_tagline" value="{{$shop->shop_tagline}}" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            @if(isset($shop) && $shop->shop_logo != null)
                                            <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                <a href="{{ $shop->shop_logo_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">Current Image</a>
                                            @endif
                                            <label>Shop Image &nbsp;&nbsp;<code>Recommended Dimension - 350 X 200</code></label>
                                            <input type="file" name="shop_image" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Shop Contact Number <span class="text-danger">*</span></label>
                                            <input name="shop_mobile" value="{{$shop->shop_mobile}}" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Shop Email Address <span class="text-danger">*</span></label>
                                            <input name="shop_email" value="{{$shop->shop_email}}" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Shop Whatsapp Number <span class="text-danger">*</span></label>
                                            <input name="shop_whatsapp" value="{{$shop->shop_whatsapp}}" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Assign Admin <span class="text-danger">*</span></label>
                                            <select class="form-control" name="admin_id" id="">
                                                @foreach ($admins as $item)
                                                    <option {{$item->id == $shop->admin_id ? 'selected' : ''}} value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                                               
                                            </select>
                                        </div>
                                    </div>
                                    <footer class="text-left">
                                        <button type="button" onclick="anchor('addressdetails')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                    </footer>
                                </form>
                            </div>

                            <div class="tab-pane" id="addressdetails">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div id="address-picker" style="width: 100%; height: 300px;"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <hr class="short">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Location<span class="text-danger">*</span></label>
                                        <input name="shop_location" value="{{$shop->shop_location}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Delivery Radius (In Meters) <span class="text-danger">*</span></label>
                                        <input name="shop_delivery_radius" value="{{$shop->shop_delivery_radius}}" class="form-control">

                                        @if(config('app.order_storemindeliveryrange'))
                                            <code>Min Delivery Range: <b>{{config('app.order_storemindeliveryrange')}}</b></code>&nbsp;&nbsp;
                                        @endif

                                        @if(config('app.order_storemaxdeliveryrange'))
                                            <code>Max Delivery Range: <b>{{config('app.order_storemaxdeliveryrange')}}</b></code>&nbsp;&nbsp;
                                        @endif
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Latitude <span class="text-danger">*</span></label>
                                        <input name="shop_latitude" readonly value="{{$shop->shop_latitude}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Longitude <span class="text-danger">*</span></label>
                                        <input name="shop_longitude" readonly value="{{$shop->shop_longitude}}" class="form-control">
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label>Address (Line 1)</label>
                                        <input name="shop_address_line1" value="{{@$shop->shop_address->address_line1}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Address (Line 2)</label>
                                        <input name="shop_address_line2" value="{{@$shop->shop_address->address_line2}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Postal Code</label>
                                        <input name="shop_postal_code" value="{{@$shop->shop_address->postal_code}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>City</label>
                                        <input name="shop_city" value="{{@$shop->shop_address->city}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>State</label>
                                        <input name="shop_state" value="{{@$shop->shop_address->state}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Country</label>
                                        <input name="shop_country" value="{{@$shop->shop_address->country}}" class="form-control">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('shopdetails')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
                                    <button type="submit" class="btn btn-primary btn-md">Submit</button>
                                </footer>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .tab-pane{
            padding: 10px
        }
    </style>
@endpush

@push('script')
    <script type="text/javascript" src='https://maps.google.com/maps/api/js?key={{config('google.apikey')}}&sensor=false&libraries=places'></script>
    <script src="{{ asset('inhouse/plugins/jquery-locationpicker-plugin-master/dist/locationpicker.jquery.min.js') }}"></script>

    <script>
        $('#settingsform').validate({
            rules: {
                name: {
                    required: true,
                },
                title: {
                    required: true,
                },
                shop_mobile: {
                    required: false,
                    number: true,
                    maxlength: 10,
                    minlength: 10,
                },
                shop_email: {
                    required: false,
                    email: true,
                },
                shop_whatsapp: {
                    required: false,
                    number: true,
                    maxlength: 10,
                    minlength: 10,
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
                var form = $('#settingsform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            form.find('button[type="submit"]').button('reset');
                            notify(data.status, 'success');
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function anchor(id){
            const currentTabId = $('.tab-pane.active').attr('id');
            const form = $('#settingsform');
            let cont = true;

            $(`#${currentTabId} input, #${currentTabId} select, #${currentTabId} textarea`).each(
                function(index){
                    var input = $(this);
                    if(input.attr('name')){
                        // console.log('name: ' + input.attr('name'), form.validate().element("[name='" + input.attr('name') + "']"))

                        if(!form.validate().element("[name='" + input.attr('name') + "']")){ // validate the input field
                            form.validate().focusInvalid(); // focus it if it was invalid
                            cont = false;
                        }
                    }
                }
            )

            if(cont == true) {
                $('a[href="#'+id+'"]').tab('show');
            }
        }

        const init_radius = '{{ ($shop->shop_delivery_radius) ? $shop->shop_delivery_radius : 1000 }}';
        const init_latitude = '{{ ($shop->shop_latitude) ? $shop->shop_latitude : 22.8765026 }}';
        const init_longitude = '{{ ($shop->shop_longitude) ? $shop->shop_longitude : 87.7909507 }}';

        $('#address-picker').locationpicker({
            location: {
                latitude: init_latitude,
                longitude: init_longitude,
            },
            radius: init_radius,
            inputBinding: {
                latitudeInput: $('[name="shop_latitude"]'),
                longitudeInput: $('[name="shop_longitude"]'),
                radiusInput: $('[name="shop_delivery_radius"]'),
                locationNameInput: $('[name="shop_location"]')
            },
            enableAutocomplete: true,
            onchanged: function(currentLocation, radius, isMarkerDropped) {
                var addressComponents = $(this).locationpicker('map').location.addressComponents;
                updateAddressControls(addressComponents);
            },
            oninitialized: function(component) {
                // @init
            }
        });

        function updateAddressControls(addressComponents) {
            $('[name="shop_address_line1"]').val(addressComponents.addressLine1);
            $('[name="shop_address_line2"]').val(addressComponents.addressLine2);
            $('[name="shop_postal_code"]').val(addressComponents.postalCode);
            $('[name="shop_city"]').val(addressComponents.city);
            $('[name="shop_state"]').val(addressComponents.stateOrProvince);
            $('[name="shop_country"]').val(addressComponents.country);
        }

        function revertStoreOnlineStatus(){
            let currentstatus = "{{$shop->online}}";

            if(currentstatus == '1'){
                var swaltitle = 'Closing the Store'
                var swaltext = 'Once the store is closed, you will not receive new orders'
            } else{
                var swaltitle = 'Opening the Store'
                var swaltext = 'Once the store is opened, you will start receiving new orders'
            }

            swal({
                title: swaltitle,
                text: swaltext,
                icon: "warning",
                buttons: true,
                dangerMode: true,
                })
                .then((willDelete) => {
                if (willDelete) {
                    Pace.track(function(){
                        $.ajax({
                            dataType: "JSON",
                            url: "{{route('dashboard.shopsettings.changeonlinestatus')}}",
                            method: "POST",
                            data: {"_token" : "{{csrf_token()}}", "operation" : "changestatus", "id" : "{{$shop->id}}"},
                            success: function(data){
                                location.reload();
                            }, error: function(errors){
                                showErrors(errors);
                            }
                        });
                    });
                }
            });
        }

        /* @if(Myhelper::hasnotrole(['superadmin', 'admin'])) */
            $('[name="shop_image"]').attr('disabled', true);
        /* @endif */
    </script>
@endpush
