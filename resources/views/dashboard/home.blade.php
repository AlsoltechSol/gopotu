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

        @if( Myhelper::hasRole(['branch']))
            <section class="content">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['neworders'] }}</h3>
                                <p>New Orders</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-bag"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                            
                                <h3>{{ $count['todaysales'] }}</h3>
                                <p>Today Sales</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-inr"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['monthsales'] }}</h3>
                                <p>This Month Sales</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index') }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>


                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['today_cancel_order'] }}</h3>
                                <p>Today's Cancel Order</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-ban"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['monthly_cancel_order'] }}</h3>
                                <p>Monthly Cancel Order</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-ban"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $count['return_order'] }}</h3>
                                <p>Return Order</p>
                            </div>
                            <div class="icon">
                                <<i class="fa fa-arrow-rotate"></i>
                            </div>
                            <a href="{{ route('dashboard.orders.index', ['type' => 'user']) }}" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

            
            

            
            </section>
        @endif

    
    

       

    </section>

    <section class="content">

    </section>
@endsection


@push('script')
@endpush
