@section('pageheader', 'Funds Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Funds Management
            <small>Transfer & Return</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Funds</li>
            <li class="">Transfer & Return</li>
            <li class="active">{{$role->name}}</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Members</h3>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            @if($role->slug == 'user')
                            <th>Main Wallet</th>
                            @elseif($role->slug == 'branch')
                            <th>Main Wallet</th>
                            @elseif($role->slug == 'deliveryboy')
                            <th>Earning Wallet</th>
                            <th>Collection Wallet</th>
                            @endif
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade" id="fundtrmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Transfer/Return <small id="user-name"></small></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.funds.submit')}}" method="POST" id="fundtrform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-control select2" style="width: 100%">
                                    <option value="">Select Type</option>
                                    <option value="fundtransfer">Transfer</option>
                                    <option value="fundreturn">Return</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-6">
                                <label>Wallet <span class="text-danger">*</span></label>
                                <select name="wallet_type" class="form-control select2" style="width: 100%">
                                    <option value="">Select Wallet</option>
                                </select>
                            </div>

                            <div class="form-group col-sm-12">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" placeholder="Enter Amount">
                            </div>

                            <div class="form-group col-sm-12">
                                <label>Remarks <span class="text-danger">*</span></label>
                                <textarea name="remarks" class="form-control" placeholder="Enter Remarks"></textarea>
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
@endsection

@push('script')
    <script>
        $('#my-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('dashboard.fetchdata', ['type' => 'fundtrmembers'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                    d.role_id = '{{$role->id}}';
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
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        return data
                    },
                    searchable: true,
                },
                {
                    data:'email',
                    name: 'email',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'mobile',
                    name: 'mobile',
                    render: function(data, type, full, meta){
                        if(data != null){
                            return data;
                        } else{
                            return 'N/A';
                        }
                    },
                },
                {
                    data:'role',
                    name: 'role.name',
                    render: function(data, type, full, meta){
                        return `<b class="text-primary">` + data.name + `</b>`
                    },
                },
                /* @if($role->slug == 'user') */
                {
                    data:'userwallet',
                    name: 'userwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @elseif($role->slug == 'branch') */
                {
                    data:'branchwallet',
                    name: 'branchwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @elseif($role->slug == 'deliveryboy') */
                {
                    data:'riderwallet',
                    name: 'riderwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                {
                    data:'creditwallet',
                    name: 'creditwallet',
                    render: function(data, type, full, meta){
                        return `<i class="fa fa-inr"></i> ` + data
                    },
                },
                /* @endif */
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        if(data == 1){
                            html = `<span class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i>&nbsp;Active</span>`;
                        } else{
                            html = `<span class="btn btn-sm btn-warning"><i class="fa fa-remove"></i>&nbsp;Inactive</span>`;
                        }

                        return html;
                    },
                    className: 'text-center'
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        /* @if(Myhelper::can('fund_tr_action')) */
                            html += `<button onClick="initiate_tr(` + full.id + `)" class="btn btn-xs btn-primary mg"><i class="fa fa-refresh"></i></button>`;
                        /* @endif */

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            "order": [
                [0, 'asc']
            ]
        });

        function initiate_tr(user_id){
            Pace.track(function(){
                $.ajax({
                dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'fundtrmembers', 'fetch' => 'single'])}}" + "/" + user_id,
                    method: "GET",
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        let user_details = data.result;

                        var options = '<option value="">Select Wallet</option>';

                        if(user_details.role.slug == 'user'){
                            options += '<option value="userwallet">User Main Wallet</option>';
                        }

                        if(user_details.role.slug == 'branch'){
                            options += '<option value="branchwallet">Merchant Main Wallet</option>';
                        }

                        if(user_details.role.slug == 'deliveryboy'){
                            options += '<option value="riderwallet">Rider Earning Wallet</option>';
                            options += '<option value="creditwallet">Rider Collection Wallet</option>';
                        }


                        $('#fundtrform').find('[name="wallet_type"]').html(options);
                        $('#fundtrform').find('[name="user_id"]').val(user_details.id);
                        $('#fundtrmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

        $('#fundtrform').validate({
            rules: {
                type: {
                    required: true,
                },
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
                var form = $('#fundtrform');

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
                            $('#fundtrmodal').modal('hide');
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
    </script>
@endpush
