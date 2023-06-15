@section('pageheader', $role->name)
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Member Management
            <small>Create {{$role->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Member Management</li>
            <li class="active"><a href="{{route('dashboard.members.index', ['type' => $role->slug])}}">{{$role->name}}</a></li>
            <li class="active">Add New</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Add New {{$role->name}}</h3>

                <div class="box-tools pull-right">
                    {{-- Tools --}}
                </div>
            </div>
            <form action="{{route('dashboard.members.create', ['type' => $role->slug])}}" method="POST" id="memberform">
                <div class="box-body">
                    @csrf

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile">
                        </div>

                        {{-- <div class="form-group col-md-4">
                            <label>Scheme <span class="text-danger">*</span></label>
                            <select name="scheme_id" class="form-control select2" style="width: 100%">
                                <option value="">Select Scheme</option>
                                @foreach ($schemes as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="form-group col-md-4">
                            <label>Business Category <span class="text-danger">*</span></label>
                            <select name="business_category" class="form-control select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                <option value="mart">Mart</option>
                                <option value="restaurant">Restaurant</option>
                            </select>
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

@push('script')
    <script>
        $('#memberform').validate({
            rules: {
                name: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true,
                },
                mobile: {
                    required: true,
                    number: true,
                    maxlength: 10,
                    minlength: 10,
                },
                // scheme_id: {
                //     required: true,
                // },
                business_category: {
                    required: true,
                },
            },
            messages: {
                mobile: {
                    number: "Mobile number must be numeric with a maximum length of 10 digits",
                    maxlength: "Mobile number must be numeric with a maximum length of 10 digits",
                    minlength: "Mobile number must be numeric with a maximum length of 10 digits",
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
                var form = $('#memberform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            // notify(data.status, 'success');
                            form.find('button[type="submit"]').button('reset');
                            form[0].reset();

                            if(data.data.credentials){
                                showCredentials(data.data.credentials);
                            }
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function showCredentials(credentials){
            var div = document.createElement("div");
            div.innerHTML = `<h4 class="text-primary" style="margin: 3px; text-transform: uppercase; font-weight: bold;">Login Credentials</h4>
                            <p style="margin: 3px">Email: <b>` + credentials.email + `</b></p>
                            <p style="margin: 3px">Mobile: <b>` + credentials.mobile + `</b></p>
                            <p style="margin: 3px">Password: <b>` + credentials.password + `</b></p>
                            <div class="row">
                              
                                <div class="col-md-9">
                                    <input margin: 3px type="number" id="otp" class="form-control" name="otp">
                                </div>
                                <div class="col-md-3">
                                    <button margin: 3px id="verify-button" type="button" onclick="verifyOtp(this)" value=`+ credentials.mobile +` class="btn btn-md btn-primary">Verify OTP</button>
                                </div>
                            </div>
                            <p id="otp-message"></p>`;                       

            swal({
                icon: "success",
                title: "{{ucfirst($type)}} Created Successfully",
                content: div,
                onClose: () => {
                    window.location.href = "{{route('dashboard.members.index', ['type' => $role->slug ])}}";
                },
              
            }).then((confirm) => {
                window.location.href = "{{route('dashboard.members.index', ['type' => $role->slug ])}}";
            });
        }


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
    </script>
@endpush
