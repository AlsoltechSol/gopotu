@section('pageheader', 'Reset Password')

@extends('layouts.auth')

@section('content')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ route('index') }}"><b>GOPOTU</b> APP</a>
        </div>

        <div class="login-box-body">
            <p class="login-box-msg">Reset Password</p>

            <form action="{{route('password.update')}}" method="post">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group has-feedback">
                    <input type="email" class="form-control" value="{{ $email ?? old('email') }}" name="email" placeholder="Email" required readonly>
                    <span class="fa fa-envelope form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                    <span class="fa fa-lock form-control-feedback"></span>
                </div>

                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
                    <span class="fa fa-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Reset Password</button>
                    </div>
                </div>

                <br>
            </form>
        </div>
    </div>
@endsection
