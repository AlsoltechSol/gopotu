
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>@yield('pageheader', 'Dashboard') - Dashboard | {{config('app.name', 'Laravel')}} - {{config('app.title', 'Another Laravel Website')}}</title>

        {{-- <link rel="icon" href="{{asset('favicon.ico')}}" type="image/x-icon" /> --}}
        <link rel="icon" href="{{asset('favicon.png')}}" />

        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.7 -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
        <!-- Font Awesome -->
        {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> --}}
        <!-- Ionicons -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/Ionicons/css/ionicons.min.css')}}">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.7/css/responsive.dataTables.min.css">
        <!-- Select2 -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/select2/dist/css/select2.min.css')}}">
        <!-- bootstrap datepicker -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">
        <!-- daterange picker -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
        <!-- Bootstrap Color Picker -->
        <link rel="stylesheet" href="https://itsjavi.com/bootstrap-colorpicker/v2/dist/css/bootstrap-colorpicker.min.css">
        {{-- <link rel="stylesheet" href="{{asset('inhouse/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css')}}"> --}}
        <!-- Bootstrap Tags Input -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.css')}}">
        <!-- Ekko Lightbox -->
        <link rel="stylesheet" href="{{asset('inhouse/plugins/ekko-lightbox/ekko-lightbox.css')}}">
        <!-- Dropzone.JS -->
        <link rel="stylesheet" href="{{asset('inhouse/plugins/multi-file-Upload-dropzone/dist/dropzone.css')}}">

        <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="{{asset('inhouse/dist/css/skins/_all-skins.min.css')}}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('inhouse/dist/css/AdminLTE.min.css')}}">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        {{-- myPlugin CSS --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.css" integrity="sha512-8D+M+7Y6jVsEa7RD6Kv/Z7EImSpNpQllgaEIQAtqHcI0H6F4iZknRj0Nx1DCdB+TwBaS+702BGWYC0Ze2hpExQ==" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pace/0.7.8/themes/black/pace-theme-flash.min.css" integrity="sha512-0c1cb0LYXVvb9L459008ryNuWW7NuZEFY0ns6fAOfpJhHnTX7Db2vbSrjaLgvUpcl+atb3hkawh2s+eEE3KaLQ==" crossorigin="anonymous" />

        <link rel="stylesheet" href="{{asset('inhouse/dist/css/custom.css')}}"/>

        @stack('style')
    </head>

    <body class="hold-transition skin-green sidebar-mini fixed">
        <div class="wrapper">
            @include('inc.inhouse.header')

            @include('inc.inhouse.sidebar')

            <div class="content-wrapper">
                @yield('content')
            </div>

            @include('inc.inhouse.footer')

            <div class="control-sidebar-bg"></div>

            <div class="modal fade" id="mobileverifymodal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Verify mobile number</h4>
                        </div>

                        <form action="{{route('dashboard.profile')}}" method="POST" id="mobileverifyform" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" value="verifymobile">

                            <div class="modal-body">
                                <div class="form-group has-feedback">
                                    <label>OTP</label>
                                    <input type="password" value="" name="otp" class="form-control" placeholder="Enter the OTP sent to you" required>
                                    <button type="button" class="btn btn-primary form-control-feedback resendotp" id="resendmverifyotp"><i class="fa fa-lock"></i>&nbsp;&nbsp;Send OTP</button>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="resetpwdmodal">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Change Default Password</h4>
                        </div>

                        <form action="{{route('dashboard.profile')}}" method="POST" id="defaultpasswordform" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" value="defaultpassword">

                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-lg-6">
                                        <label>New Password <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-md">
                                            <input type="password" name="new_password" value="" class="form-control" placeholder="Enter your new password">
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-info btn-flat eye-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-lg-6">
                                        <label>New Password Confirmation<span class="text-danger">*</span></label>
                                        <div class="input-group input-group-md">
                                            <input type="password" name="new_password_confirmation" value="" class="form-control" placeholder="Re-enter your new password">
                                            <div class="input-group-btn">
                                                <button type="button" class="btn btn-info btn-flat eye-password"><i class="fa fa-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery 3 -->
        <script src="{{asset('inhouse/bower_components/jquery/dist/jquery.min.js')}}"></script>
        <!-- Bootstrap 3.3.7 -->
        <script src="{{asset('inhouse/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
        <!-- SlimScroll -->
        <script src="{{asset('inhouse/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
        <!-- FastClick -->
        <script src="{{asset('inhouse/bower_components/fastclick/lib/fastclick.js')}}"></script>
        <!-- DataTables -->
        <script src="{{asset('inhouse/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('inhouse/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
        <script src="https://cdn.datatables.net/responsive/1.0.7/js/dataTables.responsive.min.js"></script>
        <!-- CK Editor -->
        <script src="https://adminlte.io/themes/AdminLTE/bower_components/ckeditor/ckeditor.js"></script>
        <!-- Select2 -->
        <script src="{{asset('inhouse/bower_components/select2/dist/js/select2.full.min.js')}}"></script>
        <!-- bootstrap datepicker -->
        <script src="{{asset('inhouse/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
        <!-- date-range-picker -->
        <script src="{{asset('inhouse/bower_components/moment/min/moment.min.js')}}"></script>
        <script src="{{asset('inhouse/bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
        <!-- bootstrap color picker -->
        <script src="https://itsjavi.com/bootstrap-colorpicker/v2/dist/js/bootstrap-colorpicker.js"></script>
        {{-- <script src="{{asset('inhouse/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script> --}}
        <!-- Bootstrap Tags Input -->
        <link rel="stylesheet" href="{{asset('inhouse/bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.js')}}">
        <!-- Ekko Lightbox -->
        <script src="{{asset('inhouse/plugins/ekko-lightbox/ekko-lightbox.min.js')}}"></script>
        <!-- moment-js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
        <!-- Dropzone.JS -->
        <script src="{{asset('inhouse/plugins/multi-file-Upload-dropzone/dist/dropzone.js')}}"></script>

        <!-- Font Awesome -->
        <script src="https://kit.fontawesome.com/a66d115c29.js" crossorigin="anonymous"></script>

        <!-- AdminLTE App -->
        <script src="{{asset('inhouse/dist/js/adminlte.min.js')}}"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="{{asset('inhouse/dist/js/demo.js')}}"></script>

        {{-- myPlugins JS --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js" integrity="sha512-YUkaLm+KJ5lQXDBdqBqk7EVhJAdxRnVdT2vtCzwPHSweCzyMgYV/tgGF4/dCyqtCC2eCphz0lRQgatGVdfR0ww==" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pace/0.7.8/pace.min.js" integrity="sha512-t3TewtT7K7yfZo5EbAuiM01BMqlU2+JFbKirm0qCZMhywEbHZWWcPiOq+srWn8PdJ+afwX9am5iqnHmfV9+ITA==" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js" integrity="sha512-zlWWyZq71UMApAjih4WkaRpikgY9Bz1oXIW5G0fED4vk14JjGlQ1UmkGM392jEULP8jbNMiwLWdM8Z87Hu88Fw==" crossorigin="anonymous"></script>

        <script src="{{asset('inhouse/dist/js/custom.js')}}"></script>

        @stack('script')

        <script>
            $(window).on('pageshow', function(){
                $('.dataTables_wrapper').find('[type="search"]').val('');
            })

            $(document).ready(function () {
                $('.sidebar-menu').tree();

                // $('#datepicker').datepicker({
                //     autoclose: true
                // })
            })

            /** @if(Auth::user()->resetpwd == 'default') **/
                $('#resetpwdmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#defaultpasswordform').validate({
                    rules: {
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
                        var form = $('#defaultpasswordform');

                        Pace.track(function(){
                            form.ajaxSubmit({
                                dataType:'json',
                                beforeSubmit:function(){
                                    form.find('button[type="submit"]').button('loading');
                                },
                                success:function(data){
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

            /** @elseif(Auth::user()->mobile != null && Auth::user()->mobile_verified_at == null) **/
                $('#mobileverifymodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#mobileverifyform').validate({
                    rules: {
                        otp: {
                            required: true,
                            number: true,
                            minlength: 6,
                            maxlength: 6,
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
                        var form = $('#mobileverifyform');

                        Pace.track(function(){
                            form.ajaxSubmit({
                                dataType:'json',
                                beforeSubmit:function(){
                                    form.find('button[type="submit"]').button('loading');
                                },
                                success:function(data){
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

                $('#resendmverifyotp').on('click', function(){
                    Pace.track(function(){
                        $.ajax({
                    dataType: "JSON",
                            url: "{{route('dashboard.profile')}}",
                            method: "POST",
                            data: {'_token':'{{csrf_token()}}','type':'verifymobile','otp':'send'},
                            success: function(data){
                                resendotptimer(120, 'resendmverifyotp');
                            }, error: function(errors){
                                showErrors(errors);
                            }
                        });
                    });
                });

                function resendotptimer(remaining, buttonid) {
                    $('#'+buttonid).attr('disabled', 'true');

                    var m = Math.floor(remaining / 60);
                    var s = remaining % 60;

                    m = m < 10 ? '0' + m : m;
                    s = s < 10 ? '0' + s : s;

                    document.getElementById(buttonid).innerHTML = '<i class="fa fa-clock-o"></i>&nbsp;&nbsp;' + m + ':' + s;
                    remaining -= 1;

                    if(remaining >= 0) {
                        setTimeout(function() {
                            resendotptimer(remaining, buttonid);
                        }, 1000);
                        return;
                    }

                    document.getElementById(buttonid).innerHTML = '<i class="fa fa-repeat"></i>&nbsp;&nbsp;Resend OTP';
                    $('#'+buttonid).removeAttr('disabled');
                }
            /** @endif **/

            $('#searchform').on('submit', function(){
                $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
            });
        </script>

        @include('inc.inhouse.messages')
    </body>
</html>
