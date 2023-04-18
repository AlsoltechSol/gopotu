@section('pageheader', 'Site Settings')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Site Settings
            <small>Manage Portal Settings</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Site Settings</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Update Settings</h3>

                <div class="box-tools pull-right"></div>
            </div>
            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="tabs">
                        <li class="bg-gray active"><a href="#siteidentity" data-toggle="tab"><i class="fa fa-gear"></i> Site Identity</a></li>
                        <li class="bg-gray"><a href="#appsettings" data-toggle="tab"><i class="fa fa-mobile"></i> App Settings</a></li>
                        <li class="bg-gray"><a href="#ordersettings" data-toggle="tab"><i class="fa fa-shopping-cart"></i> Order Settings</a></li>
                        <li class="bg-gray"><a href="#walletbonussettings" data-toggle="tab"><i class="fa fa-wallet"></i> Wallet & Bonus Settings</a></li>
                        <li class="bg-gray"><a href="#smssettings" data-toggle="tab"><i class="fa fa-comment"></i> SMS Settings</a></li>
                        <li class="bg-gray"><a href="#mailsettings" data-toggle="tab"><i class="fa fa-envelope"></i> Mail Settings</a></li>
                    </ul>
                    <form action="{{route('dashboard.settings.submit')}}" method="POST" id="settingsform">
                        @csrf
                        <div class="tab-content">
                            <div class="tab-pane active" id="siteidentity">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Site Name <span class="text-danger">*</span></label>
                                        <input name="name" value="{{$settings->name}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Site Title <span class="text-danger">*</span></label>
                                        <input name="title" value="{{$settings->title}}" class="form-control">
                                    </div>

                                    <div class="col-md-12">
                                        <hr>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Contact Number <span class="text-danger">*</span></label>
                                        <input name="contactmobile" value="{{$settings->contactmobile}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Contact Whatsapp <span class="text-danger">*</span></label>
                                        <input name="contactwhatsapp" value="{{$settings->contactwhatsapp}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Contact Email <span class="text-danger">*</span></label>
                                        <input name="contactemail" value="{{$settings->contactemail}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Contact Address <span class="text-danger">*</span></label>
                                        <input name="contactaddress" value="{{$settings->contactaddress}}" class="form-control">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('appsettings')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                </footer>
                            </div>

                            <div class="tab-pane" id="appsettings">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Version <small class="text-muted">(User APP)</small> <span class="text-danger">*</span></label>
                                        <input name="userapp_version" value="{{$settings->userapp_version}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Maintenance Message <small class="text-muted">(User APP)</small></label>
                                        <textarea name="userapp_maintenancemsg" class="form-control">{{$settings->userapp_maintenancemsg}}</textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Version <small class="text-muted">(Merchant APP)</small> <span class="text-danger">*</span></label>
                                        <input name="branchapp_version" value="{{$settings->branchapp_version}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Maintenance Message <small class="text-muted">(Merchant APP)</small></label>
                                        <textarea name="branchapp_maintenancemsg" class="form-control">{{$settings->branchapp_maintenancemsg}}</textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Version <small class="text-muted">(Driver APP)</small> <span class="text-danger">*</span></label>
                                        <input name="deliveryboyapp_version" value="{{$settings->deliveryboyapp_version}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Maintenance Message <small class="text-muted">(Driver APP)</small></label>
                                        <textarea name="deliveryboyapp_maintenancemsg" class="form-control">{{$settings->deliveryboyapp_maintenancemsg}}</textarea>
                                    </div>
                                </div>

                                <footer class="text-left">
                                    <button type="button" onclick="anchor('siteidentity')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
                                    <button type="button" onclick="anchor('ordersettings')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                </footer>
                            </div>

                            <div class="tab-pane" id="ordersettings">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Delivery Charge <span class="text-danger">*</span></label>
                                        <select name="deliverycharge_status" class="form-control select2" style="width: 100%">
                                            <option {{($settings->deliverycharge_status == "enable") ? 'selected' : ''}} value="enable">Enabled</option>
                                            <option {{($settings->deliverycharge_status == "disable") ? 'selected' : ''}} value="disable">Disabled</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Delivery Charge (Per KM) <span class="text-danger">*</span></label>
                                        <input name="deliverycharge_perkm" value="{{$settings->deliverycharge_perkm}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Minimum Delivery Charge</label>
                                        <input name="deliverycharge_min" value="{{$settings->deliverycharge_min}}" class="form-control" placeholder="Optional">
                                    </div>

                                    {{-- <div class="form-group col-md-6">
                                        <label>Free for Order</label>
                                        <input name="deliverycharge_freeordervalue" value="{{$settings->deliverycharge_freeordervalue}}" class="form-control" placeholder="Optional - Greater than equals">
                                    </div> --}}

                                    <div class="col-md-12">
                                        <hr>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Minimum Order Value</label>
                                        <input name="order_minval" value="{{$settings->order_minval}}" class="form-control" placeholder="Optional">
                                    </div>

                                    <div class="col-md-12">
                                        <hr>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Store Minimum Delivery Range (In Meters)</label>
                                        <input name="order_storemindeliveryrange" value="{{$settings->order_storemindeliveryrange}}" class="form-control" placeholder="Optional">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Store Maximum Delivery Range (In Meters)</label>
                                        <input name="order_storemaxdeliveryrange" value="{{$settings->order_storemaxdeliveryrange}}" class="form-control" placeholder="Optional">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('appsettings')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
                                    <button type="button" onclick="anchor('walletbonussettings')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                </footer>
                            </div>

                            <div class="tab-pane" id="walletbonussettings">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="text-primary text-left text-uppercase">First Order Bonus</h4>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>User Wallet (Type) <span class="text-danger">*</span></label>
                                        <select name="firstorder_userwallet_type" class="form-control select2" style="width: 100%">
                                            <option {{($settings->firstorder_userwallet_type == "flat") ? 'selected' : ''}} value="flat">Flat</option>
                                            <option {{($settings->firstorder_userwallet_type == "percentage") ? 'selected' : ''}} value="percentage">Percentage (On Item Total)</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>User Wallet (Value) <span class="text-danger">*</span></label>
                                        <input name="firstorder_userwallet_value" value="{{$settings->firstorder_userwallet_value}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Parent Wallet (Type)<span class="text-danger">*</span></label>
                                        <select name="firstorder_parentwallet_type" class="form-control select2" style="width: 100%">
                                            <option {{($settings->firstorder_parentwallet_type == "flat") ? 'selected' : ''}} value="flat">Flat</option>
                                            <option {{($settings->firstorder_parentwallet_type == "percentage") ? 'selected' : ''}} value="percentage">Percentage (On Item Total)</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Parent Wallet (Value)<span class="text-danger">*</span></label>
                                        <input name="firstorder_parentwallet_value" value="{{$settings->firstorder_parentwallet_value}}" class="form-control">
                                    </div>

                                    <div class="col-md-12">
                                        <hr>
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="text-primary text-left text-uppercase">Max Wallet Usage <small>Percentage on Item Total</small></h4>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>On Mart Orders<span class="text-danger">*</span></label>
                                        <input name="maxwalletuse_mart" value="{{$settings->maxwalletuse_mart}}" class="form-control" placeholder="">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>On Restaurant Orders<span class="text-danger">*</span></label>
                                        <input name="maxwalletuse_restaurant" value="{{$settings->maxwalletuse_restaurant}}" class="form-control" placeholder="">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>On Service Orders<span class="text-danger">*</span></label>
                                        <input name="maxwalletuse_service" value="{{$settings->maxwalletuse_service}}" class="form-control" placeholder="">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('ordersettings')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
                                    <button type="button" onclick="anchor('smssettings')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                </footer>
                            </div>

                            <div class="tab-pane" id="smssettings">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>SMS Status <span class="text-danger">*</span></label>
                                        <select name="smsflag" class="form-control select2" style="width: 100%">
                                            <option {{($settings->smsflag == "1") ? 'selected' : ''}} value="1">Enable</option>
                                            <option {{($settings->smsflag == "0") ? 'selected' : ''}} value="0">Disable</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>SMS SenderID</label>
                                        <input name="smssender" value="{{$settings->smssender}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>SMS Username</label>
                                        <input name="smsuser" value="{{$settings->smsuser}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>SMS Password</label>
                                        <input name="smspwd" value="{{$settings->smspwd}}" class="form-control">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('walletbonussettings')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
                                    <button type="button" onclick="anchor('mailsettings')" class="btn btn-primary btn-md">Next&nbsp;<i class="fa fa-arrow-circle-right"></i></button>
                                </footer>
                            </div>

                            <div class="tab-pane" id="mailsettings">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Mail Host</label>
                                        <input name="mailhost" value="{{$settings->mailhost}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail Port</label>
                                        <input name="mailport" value="{{$settings->mailport}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail Encryption</label>
                                        <input name="mailenc" value="{{$settings->mailenc}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail Username</label>
                                        <input name="mailuser" value="{{$settings->mailuser}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail Password</label>
                                        <input name="mailpwd" value="{{$settings->mailpwd}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail-From Address</label>
                                        <input name="mailfrom" value="{{$settings->mailfrom}}" class="form-control">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mail-From Name</label>
                                        <input name="mailname" value="{{$settings->mailname}}" class="form-control">
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="button" onclick="anchor('smssettings')" class="btn btn-primary btn-md"><i class="fa fa-arrow-circle-left"></i>&nbsp;Prev</button>
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
    <script>
        $('#settingsform').validate({
            rules: {
                name: { required: true, },
                title: { required: true, },

                contactmobile: { required: true, number: true, minlength: 10, maxlength: 10, },
                contactwhatsapp: { required: true, number: true, minlength: 10, maxlength: 10, },
                contactemail: { required: true, email: true},
                contactaddress: { required: true },

                deliverycharge_status: { required: true },
                deliverycharge_perkm: { required: true, number: true },
                deliverycharge_min: { required: false, number: true },
                deliverycharge_freeordervalue: { required: false, number: true, },

                order_minval: { required: false, number: true, },
                order_storemindeliveryrange: { required: false, number: true, },
                order_storemaxdeliveryrange: { required: false, number: true, },

                firstorder_userwallet_type: { required: true, },
                firstorder_userwallet_value: { required: true, number: true, },
                firstorder_parentwallet_type: { required: true, },
                firstorder_parentwallet_value: { required: true, number: true, },

                maxwalletuse_mart: { required: true, number: true, },
                maxwalletuse_restaurant: { required: true, number: true, },
                maxwalletuse_service: { required: true, number: true, },

                userapp_version: { required: true },
                userapp_maintenancemsg: { required: false },
                branchapp_version: { required: true },
                branchapp_maintenancemsg: { required: false },
                deliveryboyapp_version: { required: true },
                deliveryboyapp_maintenancemsg: { required: false },
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
    </script>
@endpush
