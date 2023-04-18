@section('pageheader', 'Reports & Statment')
@extends('layouts.app')
@section('content')
    <section class="content-header">
        <h1>
            Support Tickets
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Support Tickets</li>
            <li class="active">View All</li>
        </ol>
    </section>
    <section class="content">
        @php
            $daterange_filter = true;

            $status_filter = true;
            $status_array = [
                'pending' => 'Pending',
                'progress' => 'In-Progress',
                'resolved' => 'Resolved',
                'rejected' => 'Rejected',
            ];
        @endphp
        @include('inc.inhouse.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Tickets</h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Ticket ID</th>
                                <th>Subject</th>
                                <th>Order Code</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="statusmodal">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Ticket Status <small id="ticketcode"></small></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.report.update')}}" method="POST" id="statusform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="type" value="ticketstatus">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control select2" style="width: 100%">
                                @foreach ($status_array as $key => $item)
                                    <option value="{{ $key }}">{{ $item }}</option>
                                @endforeach
                            </select>
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

    <div class="modal fade" id="viewmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Support Ticket Details</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr><td>Code</td><th class="code"></th></tr>
                                    <tr><td>Date</td><th class="created_at"></th></tr>
                                    <tr><td>Order Code</td><th class="order_code"></th></tr>
                                    <tr><td>Name</td><th class="name"></th></tr>
                                    <tr><td>Email</td><th class="email"></th></tr>
                                    <tr><td>Mobile</td><th class="mobile"></th></tr>
                                    <tr><td>Alternate Mobile</td><th class="alternate_mobile"></th></tr>
                                    <tr><td>Subject</td><th class="subject"></th></tr>
                                    <tr><td>Message</td><th class="message"></th></tr>
                                    <tr><td>Status</td><th class="text-uppercase status"></th></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'supporttickets'])}}",
                type: "POST",
                data:function( d )
                {
                    d.daterange = $('#searchform').find('[name="daterange"]').val();
                    d.status = $('#searchform').find('[name="status"]').val();
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
                    name:'created_at',
                    render: function(data, type, full, meta){
                        return data;
                    },
                },
                {
                    data:'code',
                    name:'code',
                    render: function(data, type, full, meta){
                        return data;
                    },
                },
                {
                    data:'subject',
                    name:'subject',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'order_code',
                    name:'order_code',
                    render: function(data, type, full, meta){
                        return data ?? "N/A"
                    },
                },
                {
                    data:'name',
                    name:'name',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'mobile',
                    name:'mobile',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'email',
                    name:'email',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        if(data == 'pending'){
                            html = `<span class="badge bg-yellow">Pending</span>`
                        } else if(data == 'progress'){
                            html = `<span class="badge bg-blue">In-Progress</span>`
                        } else if(data == 'resolved'){
                            html = `<span class="badge bg-green">Resolved</span>`
                        } else if(data == 'rejected'){
                            html = `<span class="badge bg-green">Rejected</span>`
                        } else{
                            html = `<span class="badge text-capitalize">`+data+`</span>`
                        }

                        if(full.deliveryboy != null){
                            html += `<br><span class="badge bg-navy disabled"
                                            style="padding: 5px 10px; margin: 3px;"
                                            data-toggle="tooltip"
                                            data-placement="right"
                                            title="` + full.deliveryboy?.name + ` (#` + full.deliveryboy?.id + `)">\
                                        <i class="fa fa-truck"></i>\
                                    </span>`
                        }

                        return html;
                    },
                    className: "text-center",
                    orderable: true,
                    searchable: false,
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        html += `<a class="btn btn-xs btn-primary mg" href="javascript:;" onclick="viewDetails('`+full.id+`')"><i class="fa fa-eye"></i></a>`;

                        /* @if(Myhelper::can('update_support_ticket_status')) */
                            html += `<a class="btn btn-xs btn-info mg" href="javascript:;" onclick="editStatus('`+full.id+`', '`+full.status+`', '`+full.code+`')"><i class="fa fa-edit"></i></a>`;
                        /* @endif */

                        return html;

                        var menu = `<div class="btn-group">\
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">\
                                    <i class="fa fa-bars"></i>&nbsp;&nbsp;<span class="fa fa-caret-down"></span>\
                                </button>\
                                <ul class="dropdown-menu">\
                                    `+html+`
                                </ul>\
                            </div>`;

                        return menu;
                    },
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                }
            ],
            "order": [
                [0, 'desc']
            ],
            "drawCallback": function( settings ) {
                $('[data-toggle="tooltip"]').tooltip()
            }
        });

        function editStatus(id, status, code){
            $('#statusform').find('[name="status"]').find('option').attr('disabled', true)
            var available_status = ['pending','progress','resolved','rejected'];

            var enable_status = [];

            /* @if(Myhelper::hasrole(['superadmin', 'admin'])) */
                switch (status) {
                    case 'pending':
                        enable_status = available_status;
                    break;

                    case 'progress':
                        enable_status = ['progress','resolved','rejected']
                    break;

                    case 'resolved':
                    case 'rejected':
                        enable_status = [status];
                    break;
                }
            /* @endif */

            enable_status.forEach(element => {
                $('#statusform').find('[name="status"]').find('[value=' + element + ']').removeAttr('disabled');
            });

            $('#statusform').find('[name="status"]').val(status).trigger("change");
            $('#statusmodal').find('#ticketcode').text(code);
            $('#statusform').find('[name="id"]').val(id);
            $('#statusmodal').modal();
        }

        function viewDetails(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'supporttickets', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;

                        $('#viewmodal')?.find('.code')?.text(result?.code ?? "N/A");
                        $('#viewmodal')?.find('.created_at')?.text(result?.created_at ?? "N/A");
                        $('#viewmodal')?.find('.order_code')?.text(result?.order_code ?? "N/A");
                        $('#viewmodal')?.find('.name')?.text(result?.name ?? "N/A");
                        $('#viewmodal')?.find('.email')?.text(result?.email ?? "N/A");
                        $('#viewmodal')?.find('.mobile')?.text(result?.mobile ?? "N/A");
                        $('#viewmodal')?.find('.alternate_mobile')?.text(result?.alternate_mobile ?? "N/A");
                        $('#viewmodal')?.find('.subject')?.text(result?.subject ?? "N/A");
                        $('#viewmodal')?.find('.message')?.text(result?.message ?? "N/A");
                        $('#viewmodal')?.find('.status')?.text(result?.status ?? "N/A");

                        $('#viewmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        $('#statusform').validate({
            rules: {
                status: {
                    required: true,
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
                var form = $('#statusform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            $('#statusmodal').modal('hide');
                            form.find('button[type="submit"]').button('reset');
                            $('#my-datatable').dataTable().api().ajax.reload();
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });
    </script>
@endpush
