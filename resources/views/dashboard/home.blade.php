@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Dashboard
            <small>Control Panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('dashboard.home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>

        </ol>
       
        @if (session('admin'))
        <div class="mt-5">

            <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
        </div>
       
        @endif

       

    </section>

    <section class="content">

    </section>
@endsection


@push('script')
@endpush
