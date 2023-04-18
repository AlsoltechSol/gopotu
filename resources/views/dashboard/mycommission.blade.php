@section('pageheader', 'My Commission')
@extends('layouts.app')

@section('content')

    <section class="content-header">
        <h1>
            My Commission
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">My Commission</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        @foreach ($types as $key => $value)
                            <li class="bg-gray {{ $loop->iteration == 1 ? 'active' : '' }}">
                                <a href="#{{$key}}" data-toggle="tab">
                                    {{$value}}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach ($types as $key => $value)
                            <div class="tab-pane {{ $loop->iteration == 1 ? 'active' : '' }}" id="{{$key}}">
                                <table class="table table-bordered table-hovered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @if(@$providers[$key] && count($providers[$key]) > 0)
                                            @foreach ($providers[$key] as $item)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$item->name}}</td>
                                                    <td class="text-capitalize"><b>{{$item->commission_type}}</b></td>
                                                    <td><b>{{$item->commission_value}}</b></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">No records found</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection


@push('script')

@endpush
