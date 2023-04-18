@section('pageheader', $heading)
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            {{$heading}}
            <small>{{$user->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Statement</li>
            <li class="active">{{$heading}}</li>
        </ol>
    </section>
    <section class="content">
        @php
            $filteroptions = [
                'daterange' => true,
            ];
        @endphp
        @include('inc.inhouse.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Records</h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="my-datatable" class="table table-bordered table-striped display nowrap" style="width: 100%">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Date</th>
                            <th>Opening Bal</th>
                            <th>Txn Type</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th>Service</th>
                            <th>Closing Bal</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        th, td{ white-space: nowrap }
    </style>
@endpush

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'walletstatement'])}}",
                type: "POST",
                data:function( d )
                {
                    d.daterange = $('#searchform').find('[name="daterange"]').val();
                    d.user_id = '{{$user->id}}';
                    d.wallet_type = '{{$wallet_type}}';
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        return `<b class="text-primary">` + data + `</b>`;
                    },
                    className: "text-center",
                },
                {
                    data:'created_at',
                    name: 'created_at',
                    render: function(data, type, full, meta){
                        return data;
                    },
                },
                {
                    data:'balance',
                    name: 'balance',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data;
                    },
                },
                {
                    data:'trans_type',
                    name: 'trans_type',
                    render: function(data, type, full, meta){
                        switch (data) {
                            case 'credit':
                                return `<b class="text-danger">Credit</b>`;
                                break;

                            case 'debit':
                                return `<b class="text-success">Debit</b>`;
                                break;

                            default:
                                return `<b class="text-capitalize">` + data + `</b>`
                                break;
                        }

                        return data;
                    },
                },
                {
                    data:'amount',
                    name: 'amount',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data;
                    },
                },
                {
                    data:'remarks',
                    name: 'remarks',
                    render: function(data, type, full, meta){
                        return data;
                    },
                },
                {
                    data:'service',
                    name: 'service',
                    render: function(data, type, full, meta){
                        return `<span class="text-capitalize">` + data + `</span>`;
                    },
                },
                {
                    data:'closing_bal',
                    name: 'closing_bal',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data;
                    },
                    orderable: false,
                    searchable: false
                },
            ],
            "order": [
                [0, 'desc']
            ],
            "drawCallback": function( settings ) {
                $('[data-toggle="tooltip"]').tooltip()
            }
        });
    </script>
@endpush
