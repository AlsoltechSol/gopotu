@section('pageheader', 'Profile')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Profile
            <small>{{$user->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Profile</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        @if(in_array($user->role->slug, ['user']))
                            <img class="profile-user-img img-responsive img-circle" src="{{$user->avatar}}">
                        @else
                            <a href="javascript:;" data-toggle="modal" data-target="#imagemodal">
                                <img class="profile-user-img img-responsive img-circle" src="{{$user->avatar}}">
                            </a>
                        @endif

                        <h3 class="profile-username text-center">{{$user->name}}</h3>

                        <p class="text-muted text-center">{{$user->role->name}}</p>

                        <ul class="list-group list-group-unbordered">

                        </ul>

                        @if(Auth::id() == $user->id)
                            <a href="{{route('logout')}}" class="btn btn-primary btn-block"><b><i class="fa fa-power-off"></i></b> Logout</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="bg-gray active"><a href="#basicdetails" data-toggle="tab"><i class="fa fa-gear"></i> Basic Details</a></li>

                        @if(in_array($user->role->slug, ['deliveryboy', 'branch']))
                            <li class="bg-gray"><a href="#documentdetails" data-toggle="tab"><i class="fa fa-file"></i> Document Details</a></li>
                        @endif

                        @if(in_array($user->role->slug, ['deliveryboy', 'branch']))
                            <li class="bg-gray"><a href="#bankdetails" data-toggle="tab"><i class="fa fa-bank"></i> Bank Account Details</a></li>
                        @endif

                        @if(in_array($user->role->slug, ['branch']))
                            @if(Myhelper::hasRole(['superadmin', 'admin']) && Myhelper::can('edit_store'))
                                <li class="bg-gray"><a target="_blank" href="{{route('dashboard.shopsettings.index', ['shop_id' => Myhelper::getShop($user->id)])}}"><i class="fa fa-store"></i> Edit Store</a></li>
                            @endif
                        @endif

                        <li class="bg-gray "><a href="#password" data-toggle="tab"><i class="fa fa-lock"></i> Change Password</a></li>
                        <li class="bg-gray "><a href="#verify" data-toggle="tab"><i class="fa fa-lock"></i> Verify OTP</a></li>
                    </ul>
                    <div class="tab-content">
                        {{-- Basic Details Tab --}}
                        <div class="tab-pane active" id="basicdetails">
                            <form id="profileform" method="POST" action="{{route('dashboard.profile')}}">
                                @csrf
                                <input type="hidden" name="type" value="basicdetails">
                                <input type="hidden" name="id" value="{{$user->id}}">

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>{{ $user->role->slug == 'branch' ? "Owner Name" : "Name"}} <span class="text-danger">*</span></label>
                                        <input name="name" value="{{$user->name}}" class="form-control" placeholder="Enter full name">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input name="email" value="{{$user->email}}" class="form-control" placeholder="Enter email address" readonly>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Mobile <span class="text-danger">*</span> </label>
                                        <input name="mobile" value="{{$user->mobile}}" class="form-control" placeholder="Enter mobile number" readonly>
                                    </div>

                                    @if($user->role->slug == 'branch')
                                        @if(Myhelper::hasrole(['superadmin', 'admin']))
                                            {{-- @php
                                                $schemes = App\Model\Scheme::all();
                                            @endphp

                                            <div class="form-group col-md-4">
                                                <label>Scheme <span class="text-danger">*</span></label>
                                                <select name="scheme_id" class="form-control select2" style="width: 100%">
                                                    <option value="">Select Scheme</option>
                                                    @foreach ($schemes as $item)
                                                        <option value="{{$item->id}}" {{$user->scheme_id == $item->id ? 'selected' : '  '}}>{{$item->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div> --}}

                                            <div class="form-group col-md-4">
                                                <label>Business Category <span class="text-danger">*</span></label>
                                                <select name="business_category" class="form-control select2" style="width: 100%">
                                                    <option value="">Select from the dropdown</option>
                                                    <option value="mart" {{$user->business_category == 'mart' ? 'selected' : ''}}>Mart</option>
                                                    <option value="restaurant" {{$user->business_category == 'restaurant' ? 'selected' : ''}}>Restaurant</option>
                                                    {{-- <option value="service" {{$user->business_category == 'service' ? 'selected' : ''}}>Service</option> --}}
                                                </select>
                                            </div>
                                        @endif
                                    @elseif($user->role->slug == 'deliveryboy')
                                        @if(Myhelper::hasrole(['superadmin', 'admin']))
                                            <div class="form-group col-md-4">
                                                <label>Vaccination</label>
                                                <select name="vaccination" class="form-control select2" style="width: 100%">
                                                    <option value="">Select from the dropdown</option>
                                                    <option {{$user->vaccination == 'partially' ? 'selected' : ''}} value="partially">Partially Vaccinated</option>
                                                    <option {{$user->vaccination == 'fully' ? 'selected' : ''}} value="fully">Fully Vaccinated</option>
                                                </select>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <footer class="text-left">
                                    <button type="submit" class="btn btn-primary btn-md">Submit</button>
                                </footer>
                            </form>
                        </div>

                        {{-- Docuement Details Tab --}}
                        @if(in_array($user->role->slug, ['deliveryboy', 'branch']))
                            <div class="tab-pane" id="documentdetails">
                                <form id="documentform" method="POST" action="{{route('dashboard.profile')}}">
                                    @csrf
                                    <input type="hidden" name="type" value="documentdetails">
                                    <input type="hidden" name="id" value="{{$user->id}}">

                                    @if(in_array($user->role->slug, ['deliveryboy']))
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>Driving License Number <span class="text-danger">*</span></label>
                                                <input type="text" name="drivinglicense_number" class="form-control" value="{{@$user->documents->drivinglicense_number}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Driving License Expiry <span class="text-danger">*</span></label>
                                                <input type="date" name="drivinglicense_expiry" class="form-control" value="{{@$user->documents->drivinglicense_expiry}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Driving License Front Side @if(!@$user->documents->drivinglicense_front)<span class="text-danger">*</span>@endif</label>
                                                @if(@$user->documents->drivinglicense_front)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->drivinglicense_front_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="drivinglicense_front_file" class="form-control" accept="image/*">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Driving License Back Side</label>
                                                @if(@$user->documents->drivinglicense_back)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->drivinglicense_back_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="drivinglicense_back_file" class="form-control" accept="image/*">
                                            </div>

                                            <div class="col-md-12"><hr class="short"></div>
                                        </div>
                                    @endif

                                    @if(in_array($user->role->slug, ['deliveryboy', 'branch']))
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>Government ID Type <span class="text-danger">*</span></label>
                                                <select class="form-control select2" name="govtid_type" style="width: 100%">
                                                    <option value="">Select from the dropdown</option>
                                                    <option {{@$user->documents->govtid_type == 'aadhaar' ? 'selected' : ''}} value="aadhaar">Aadhaar Card</option>
                                                    <option {{@$user->documents->govtid_type == 'pancard' ? 'selected' : ''}} value="pancard">Pancard</option>
                                                    {{-- <option {{@$user->documents->govtid_type == 'voterid' ? 'selected' : ''}} value="voterid">Voter ID Card</option> --}}
                                                </select>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Government ID Number <span class="text-danger">*</span></label>
                                                <input type="text" name="govtid_number" class="form-control" value="{{@$user->documents->govtid_number}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Government ID Front Side @if(!@$user->documents->govtid_front)<span class="text-danger">*</span>@endif</label>
                                                @if(@$user->documents->govtid_front)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->govtid_front_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="govtid_front_file" class="form-control" accept="image/*">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Government ID Back Side</label>
                                                @if(@$user->documents->govtid_back)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->govtid_back_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="govtid_back_file" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                    @endif

                                    @if(in_array($user->role->slug, ['branch']))
                                        <div class="row">
                                            {{-- <div class="col-md-12"><hr class="short"></div> --}}

                                            <div class="form-group col-md-6">
                                                <label>Trade License Number</label>
                                                <input type="text" name="tradelicense_number" class="form-control" value="{{@$user->documents->tradelicense_number}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Trade License Document</label>
                                                @if(@$user->documents->tradelicense_doc)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->tradelicense_doc_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="tradelicense_doc" class="form-control" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- <div class="col-md-12"><hr class="short"></div> --}}

                                            <div class="form-group col-md-6">
                                                <label>FSSAI Registration Number</label>
                                                <input type="text" name="fssaireg_number" class="form-control" value="{{@$user->documents->fssaireg_number}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>FSSAI Registration Certificate</label>
                                                @if(@$user->documents->fssaireg_doc)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->fssaireg_doc_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="fssaireg_doc" class="form-control" accept="image/*">
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- <div class="col-md-12"><hr class="short"></div> --}}

                                            <div class="form-group col-md-6">
                                                <label>GSTIN Number</label>
                                                <input type="text" name="gstin_number" class="form-control" value="{{@$user->documents->gstin_number}}">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>GSTIN Certificate</label>
                                                @if(@$user->documents->gstin_doc)
                                                <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                    <a href="{{ $user->documents->gstin_doc_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                                @endif

                                                <input type="file" name="gstin_doc" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                    @endif

                                    <footer class="text-left">
                                        @if(Myhelper::hasrole(['superadmin', 'admin']))
                                            <button type="submit" class="btn btn-primary btn-md">Submit</button>
                                        @endif
                                    </footer>
                                </form>
                            </div>
                        @endif

                        {{-- Bank Details Tab --}}
                        @if(in_array($user->role->slug, ['deliveryboy', 'branch']))
                            <div class="tab-pane" id="bankdetails">
                                <form id="bankdetailsform" method="POST" action="{{route('dashboard.profile')}}">
                                    @csrf
                                    <input type="hidden" name="type" value="bankdetails">
                                    <input type="hidden" name="id" value="{{$user->id}}">

                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Account Number <span class="text-danger">*</span></label>
                                            <input type="text" name="accno" class="form-control" value="{{@$user->bankdetails->accno}}" placeholder="Enter Account Number">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>IFSC Code <span class="text-danger">*</span></label>
                                            <input type="text" name="ifsccode" class="form-control" value="{{@$user->bankdetails->ifsccode}}" placeholder="Enter IFSC Code">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Account Holder Name <span class="text-danger">*</span></label>
                                            <input type="text" name="accholder" class="form-control" value="{{@$user->bankdetails->accholder}}" placeholder="Enter Account Holder Name">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Bank Name <span class="text-danger">*</span></label>
                                            <input type="text" name="bankname" class="form-control" value="{{@$user->bankdetails->bankname}}" placeholder="Enter Bank Name">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Pancard Number <span class="text-danger">*</span></label>
                                            <input type="text" name="pancard_no" class="form-control" value="{{@$user->bankdetails->pancard_no}}" placeholder="Enter Pancard Number">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Pancard Image @if(!@$user->bankdetails->pancard_file)<span class="text-danger">*</span>@endif</label>
                                            @if(@$user->bankdetails->pancard_file)
                                            <a class="text-success"><i class="fa fa-check-circle"></i>&nbsp;Uploaded</a>
                                                <a href="{{ $user->bankdetails->pancard_file_path }}" data-toggle="lightbox" class="btn btn-danger btn-xs pull-right mb-1">View Uploaded Document</a>
                                            @endif

                                            <input type="file" name="pancard_file" class="form-control" accept="image/*">
                                        </div>
                                    </div>

                                    <footer class="text-left">
                                        @if(Myhelper::hasrole(['superadmin', 'admin']))
                                            <button type="submit" class="btn btn-primary btn-md">Submit</button>
                                        @endif
                                    </footer>
                                </form>
                            </div>
                        @endif

                        {{-- Password Update Tab --}}
                        <div class="tab-pane" id="password">
                            <form id="passwordform" method="POST" action="{{route('dashboard.profile')}}">
                                @csrf
                                <input type="hidden" name="type" value="changepassword">
                                <input type="hidden" name="id" value="{{$user->id}}">

                                <div class="row">
                                    @if($user->id == \Auth::id())
                                        <div class="form-group col-md-4">
                                            <label>Current Password <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-md">
                                                <input type="password" name="current_password" value="" class="form-control" placeholder="Enter your current password">
                                                <div class="input-group-btn">
                                                    <button type="button" class="btn btn-info btn-flat eye-password"><i class="fa fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-group col-md-4">
                                        <label>New Password <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-md">
                                            <input type="password" name="new_password" value="" class="form-control" placeholder="Enter your new password">
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-info btn-flat eye-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>New Password Confirmation<span class="text-danger">*</span></label>
                                        <div class="input-group input-group-md">
                                            <input type="password" name="new_password_confirmation" value="" class="form-control" placeholder="Re-enter your new password">
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-info btn-flat eye-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <footer class="text-left">
                                    <button type="submit" class="btn btn-primary btn-md">Submit</button>
                                </footer>
                            </form>
                        </div>

                        <div class="tab-pane" id="verify">
                            
                                @csrf
                                <input type="hidden" name="type" value="verify-otp">
                                <input type="hidden" name="id" value="{{$user->id}}">

                                @if ($user->otp_verified_status == 0)
                                    <div class="row">
                                
                                        <div class="col-md-9">
                                            <input margin: 3px type="number" id="otp" class="form-control" name="otp">
                                        </div>
                                        <div class="col-md-3">
                                            <button margin: 3px id="verify-button" type="button" onclick="verifyOtp(this)" value={{$user->mobile}} class="btn btn-md btn-primary">Verify OTP</button>
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <p class="text-success">Already Verified</p>
                                    </div>
                                @endif
                               

                                
                                <p id="otp-message"></p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="imagemodal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Profile Picture</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.profile')}}" method="POST" id="imageform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="profileimage">
                    <input type="hidden" name="id" value="{{$user->id}}">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Picture <span class="text-danger">*</span></label>
                            <input name="profile_image" accept="image/*" class="form-control" type="file">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
    </style>
@endpush

@push('script')
<script>
    /* @if (Myhelper::hasrole(['branch', 'deliveryboy'])) */
        $('.tab-content').find('#bankdetails input').attr("disabled", 'true')
        $('.tab-content').find('#documentdetails input').attr("disabled", 'true')
        $('.tab-content').find('#documentdetails select').attr("disabled", 'true')
    /* @endif */


    $('#profileform').validate({
        rules: {
            name: {
                required: true,
            },
            email: {
                required: true,
                email: true
            },
            mobile: {
                number: true,
                required: true,
                maxlength: 10,
                minlength: 10,
            },
            /** @if($user->role->slug == 'branch') **/
                /* @if(Myhelper::hasrole(['superadmin', 'admin'])) **/
                    // scheme_id: {
                    //     required: true,
                    // },
                    business_category: {
                        required: true,
                    },
                /* @endif **/
            /** @elseif($user->role->slug == 'deliveryboy') **/
                /* @if(Myhelper::hasrole(['superadmin', 'admin'])) **/
                    vaccination: {
                        required: false,
                    },
                /* @endif **/
            /** @endif **/
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
            var form = $('#profileform');

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

    $('#passwordform').validate({
        rules: {
            /** @if($user->id == \Auth::id()) **/
            current_password: {
                required: true,
            },
            /* @endif */
            new_password: {
                required: true,
            },
            new_password_confirmation: {
                required: true,
            },
        },
        errorElement: "p",
        errorPlacement: function ( error, element ) {
            if ( element.prop("tagName").toLowerCase() === "select" ) {
                error.insertAfter( element.closest( ".form-group" ).find("span.select2") );
            } else {
                error.insertAfter( element.closest( ".input-group" ) );
            }
        },
        submitHandler: function() {
            var form = $('#passwordform');

            Pace.track(function(){
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form[0].reset();
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

    $('#imageform').validate({
        rules: {
            profile_image: {
                required: true,
            },
        },
        profile_image: {
            name: {
                required: "Please select an image",
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
            var form = $('#imageform');

            Pace.track(function(){
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
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

    /* @if (in_array($user->role->slug, ['deliveryboy', 'branch']) && Myhelper::hasrole(['superadmin', 'admin'])) */
    $('#documentform').validate({
        rules: {
            /* @if(in_array($user->role->slug, ['deliveryboy'])) */
            drivinglicense_number : {
                required: true
            },
            drivinglicense_expiry : {
                required: true
            },
            drivinglicense_front_file : {
                /* @if(!@$user->documents->drivinglicense_front) **/
                required: true
                /* @endif */
            },
            drivinglicense_back_file : {
                required: false
            },
            /* @endif */

            /* @if(in_array($user->role->slug, ['deliveryboy', 'branch'])) */
            govtid_type : {
                required: true
            },
            govtid_number : {
                required: true
            },
            govtid_front_file : {
                /* @if(!@$user->documents->govtid_front) **/
                required: true
                /* @endif */
            },
            govtid_back_file : {
                required: false
            },
            /* @endif */

            /* @if(in_array($user->role->slug, ['branch'])) */
            tradelicense_number : {
                required: function(element) {
                    /* @if(@$user->documents->tradelicense_doc) **/
                        return true;
                    /* @else */
                        return ($("#documentform").find('[name=tradelicense_doc]').val()) ? true: false;
                    /*@endif*/
                }
            },
            tradelicense_doc : {
                /* @if(!@$user->documents->tradelicense_doc) **/
                required: function(element) {
                    return ($("#documentform").find('[name=tradelicense_number]').val()) ? true: false;
                }
                /* @endif */
            },
            fssaireg_number : {
                required: function(element) {
                    /* @if(@$user->documents->fssaireg_doc) **/
                        return true;
                    /* @else */
                        return ($("#documentform").find('[name=fssaireg_doc]').val()) ? true: false;
                    /*@endif*/

                }
            },
            fssaireg_doc : {
                /* @if(!@$user->documents->fssaireg_doc) **/
                required: function(element) {
                    return ($("#documentform").find('[name=fssaireg_number]').val()) ? true: false;
                }
                /* @endif */
            },
            gstin_number : {
                required: function(element) {
                    /* @if(@$user->documents->gstin_doc) **/
                        return true;
                    /* @else */
                        return ($("#documentform").find('[name=gstin_doc]').val()) ? true: false;
                    /*@endif*/
                }
            },
            gstin_doc : {
                /* @if(!@$user->documents->gstin_doc) **/
                required: function(element) {
                    return ($("#documentform").find('[name=gstin_number]').val()) ? true: false;
                }
                /* @endif */
            },
            /* @endif */
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
            var form = $('#documentform');

            Pace.track(function(){
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        location.reload();
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
    /* @endif */

    /* @if(in_array($user->role->slug, ['deliveryboy', 'branch']) && Myhelper::hasrole(['superadmin', 'admin'])) */
    $('#bankdetailsform').validate({
        rules: {
            accno: {
                required: true,
                number: true,
            },
            ifsccode: {
                required: true,
            },
            accholder: {
                required: true,
            },
            bankname: {
                required: true,
            },
            pancard_no: {
                required: true,
            },
            pancard_file: {
                /* @if(!@$user->bankdetails->pancard_file) **/
                required: true
                /* @endif */
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
            var form = $('#bankdetailsform');

            Pace.track(function(){
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        notify(data.status, 'success');
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
    /* @endif */

    function verifyOtp(src){

         var otp = $('#otp').val();
            var otpMessage = $('#otp-message');
            var csrfToken = '{{ csrf_token() }}';
            $.ajax({
            url: '/verify-otp',
            method: 'POST',
            data: {
                // Add any data you want to send with the request
                _token: csrfToken, 
                mobile: src.value,
                otp:otp
            },
            success: function(response) {
                // Handle the successful response
                
                if (response.status == 200){
                    otpMessage.removeClass('text-danger');
                    otpMessage.addClass('text-success');
                    $('#verify-button').addClass('disabled');
                    setTimeout(function() {
                        swal.close();
                    }, 2000);
                
                }else{
                    otpMessage.removeClass('text-danger');
                    otpMessage.addClass('text-danger');
                }
                otpMessage.html(response.message)
            },
            error: function(xhr, status, error) {
                // Handle the error response
                console.log(error);
            }
        });
    }

    

    $('#dob-picker').datepicker({
        endDate: "{{Carbon\Carbon::now()->subYears(18)->format('m/d/Y')}}",
        autoclose: true,
    })
</script>
@endpush
