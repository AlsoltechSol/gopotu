@php
    $listing = false;
@endphp

@section('pageheader', 'Funds Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Funds Management
            <small>Collection Submitted</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Funds</li>
            <li class="active">Collection Submitted</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            @if(Myhelper::hasrole(['branch']))
                @php $listing = true; @endphp

                <div class="col-md-4">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Create New Request</h3>
                        </div>
                        <form action="{{route('dashboard.funds.submit')}}" method="POST" id="payoutform">
                            @csrf
                            <input type="hidden" name="type" value="submitcollectionsubmitted">

                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-sm-12">
                                        <label>Wallet <span class="text-danger">*</span></label>
                                        <select name="wallet_type" class="form-control select2" style="width: 100%">
                                            <option value="">Select Wallet</option>
                                            @if (Myhelper::hasrole('branch'))
                                                <option value="branchwallet">Main Wallet</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-12">
                                        <label>Amount <span class="text-danger">*</span></label>
                                        <input name="amount" type="number" class="form-control" placeholder="Enter Amount">
                                    </div>

                                    <div class="form-group col-sm-12">
                                        <label>Remarks <span class="text-danger">*</span></label>
                                        <textarea name="remarks" class="form-control" placeholder="Enter Remarks"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="col-md-{{ $listing == true ? '8' : '12' }}">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Requests</h3>
                    </div>
                    <div class="box-body">
                        <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Request #</th>
                                    <th>Request Date</th>
                                    <th>Wallet Type</th>
                                    <th>User Name</th>
                                    <th>Amount</th>
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
        </div>
    </section>

    @if(Myhelper::can('edit_collectionsubmitted_status'))
        <div class="modal fade" id="editstatusmodal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Update Status <small id="request-code"></small></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <form action="{{route('dashboard.funds.submit')}}" method="POST" id="editstatusform" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="">
                        <input type="hidden" name="request_id" value="">

                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control select2" style="width: 100%">
                                        <option value="pending" disabled>Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-12">
                                    <label>Admin's Remark</label>
                                    <textarea name="adminremarks" class="form-control" placeholder="Enter Admin's Remarks"></textarea>
                                </div>
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
    @endif

    <div class="modal fade" id="payoutdetailsmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">View Request</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Request #</th>
                                        <td id="code"></td>
                                    </tr>
                                    <tr>
                                        <th>User Name</th>
                                        <td id="user-name"></td>
                                    </tr>
                                    <tr>
                                        <th>User ID</th>
                                        <td id="user-id"></td>
                                    </tr>
                                    <tr>
                                        <th>Wallet Type</th>
                                        <td id="wallet_type"></td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td id="amount"></td>
                                    </tr>
                                    <tr>
                                        <th>Remarks</th>
                                        <td id="remarks"></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-uppercase" id="status"></th>
                                    </tr>
                                    <tr>
                                        <th>Admin Remarks</th>
                                        <td id="adminremarks"></td>
                                    </tr>
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

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'collectionsubmitted'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                {
                    data:'id',
                    name: 'id',
                    render: function(data, type, full, meta){
                        return '<b class="text-primary">' + data + '</b>';
                    },
                },
                {
                    data:'code',
                    name: 'code',
                    render: function(data, type, full, meta){
                        return '<b class="">' + data + '</b>';
                    },
                },
                {
                    data:'created_at',
                    name: 'created_at',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'wallet_type',
                    name: 'wallet_type',
                    render: function(data, type, full, meta){
                        switch (data) {
                            case 'creditwallet':
                                return 'Collection Wallet'
                            break;

                            default:
                                return data;
                                break;
                        }
                    },
                },
                {
                    data:'user',
                    name: 'user.name',
                    render: function(data, type, full, meta){
                        return data.name
                    },
                },
                {
                    data:'amount',
                    name: 'amount',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },

                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        switch (data) {
                            case 'pending':
                                return `<span class="badge bg-yellow">Pending</span>`;
                            break;

                            case 'approved':
                                return `<span class="badge bg-green">Approved</span>`;
                            break;

                            case 'rejected':
                                return `<span class="badge bg-red">Rejected</span>`;
                            break;

                            default:
                                return `<span class="badge text-capitalize">` + data + `</span>`;
                            break;
                        }
                    },
                    className: 'text-center'
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        html += `<button onClick="view(` + full.id + `)" class="btn btn-xs btn-primary mg"><i class="fa fa-eye"></i></button>`;

                        if(full.transaction_copy){
                            html += `<a href="` + full.transaction_copy_path + `" data-title="Transaction Copy" data-toggle="lightbox" class="btn btn-xs btn-primary mg"><i class="fa fa-file-invoice"></i></a>`;
                        }

                        /* @if(Myhelper::can('edit_collectionsubmitted_status')) */
                            if(['pending'].includes(full.status)){
                                html += `<button onClick="editStatus(` + full.id + `)" class="btn btn-xs btn-info mg"><i class="fa fa-edit"></i></button>`;
                            }
                        /* @endif */

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            "order": [
                [0, 'desc']
            ]
        });

        $('#editstatusform').validate({
            rules: {
                status: {
                    required: true,
                },
                adminremarks: {
                    required: false,
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
                var form = $('#editstatusform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            form.find('.select2').val('').trigger('change');
                            form.find('button[type="submit"]').button('reset');
                            $('#editstatusmodal').modal('hide');
                            $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        $('#payoutform').validate({
            rules: {
                wallet_type: {
                    required: true,
                },
                amount: {
                    required: true,
                    number: true,
                },
                remarks: {
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
                var form = $('#payoutform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            form.find('.select2').val('').trigger('change');
                            form.find('button[type="submit"]').button('reset');
                            $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        function view(id){
            Pace.track(function(){
                $.ajax({
                dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'collectionsubmitted', 'fetch' => 'single'])}}" + "/" + id,
                    method: "GET",
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        let requestdetails = data.result;

                        let wallet_type = "";
                        switch (requestdetails.wallet_type) {
                            case 'creditwallet':
                                wallet_type = 'Collection Wallet'
                            break;

                            default:
                                wallet_type = requestdetails.wallet_type;
                                break;
                        }

                        $('#payoutdetailsmodal').find('#code').text(requestdetails.code);
                        $('#payoutdetailsmodal').find('#user-name').text(requestdetails.user.name);
                        $('#payoutdetailsmodal').find('#user-id').text(requestdetails.user.id);
                        $('#payoutdetailsmodal').find('#wallet_type').text(wallet_type);
                        $('#payoutdetailsmodal').find('#amount').text(requestdetails.amount);
                        $('#payoutdetailsmodal').find('#remarks').text(requestdetails.remarks);
                        $('#payoutdetailsmodal').find('#status').text(requestdetails.status);
                        $('#payoutdetailsmodal').find('#adminremarks').text(requestdetails.adminremarks ?? "N/A");

                        $('#payoutdetailsmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

        function editStatus(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'collectionsubmitted', 'fetch' => 'single'])}}" + "/" + id,
                    method: "GET",
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        let requestdetails = data.result;

                        $('#editstatusmodal').find('#request-code').text(requestdetails.code);

                        $('#editstatusform').find('[name="request_id"]').val(requestdetails.id);
                        $('#editstatusform').find('[name="status"]').val(requestdetails.status).trigger("change");
                        $('#editstatusform').find('[name="adminremarks"]').val(requestdetails.adminremarks);
                        $('#editstatusform').find('[name="type"]').val("editcollectionsubmittedstatus");
                        $('#editstatusmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }
    </script>
@endpush
