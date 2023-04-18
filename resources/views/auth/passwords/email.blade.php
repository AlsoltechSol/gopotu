@section('pageheader', 'Reset Password')

@extends('layouts.auth')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ route('index') }}"><b>GOPOTU</b> APP</a>
        </div>

        <div class="login-box-body">
            <p class="login-box-msg">Reset Password</p>

            <form action="{{ route('password.email') }}" method="post">
                @csrf

                <div class="form-group has-feedback">
                    <input type="email" class="form-control" value="{{ old('email') }}" name="email" placeholder="Email"
                        required>
                    <span class="fa fa-envelope form-control-feedback"></span>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Send Password Reset Link</button>
                    </div>
                </div>

                <br>
            </form>
        </div>
    </div>
@endsection
