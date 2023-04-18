@section('pageheader', 'Dicount Coupons')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Coupon Management
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Discount Coupons</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Discount Coupons</h3>

                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_coupon'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped display responsive nowrap" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Coupon Code</th>
                            <th>Discount Type</th>
                            <th>Discount Value</th>
                            <th>Max Discount</th>
                            <th>Min Order</th>
                            <th>Max Usage</th>
                            <th>Coupon Used</th>
                            <th>Valid Till</th>
                            <th>Last Updated</th>
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

    <div class="modal fade" id="couponmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Profile Picture</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.coupon.submit')}}" method="POST" id="couponform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label>Code <span class="text-danger">*</span></label>
                                <input type="text" value="" name="code" class="form-control" placeholder="Enter Coupon Code">
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Rewarded <span class="text-danger">*</span></label>
                                <select name="rewarded" class="form-control" style="width: 100%">
                                    <option value="">Select from the dropdown</option>
                                    <option value="instant">Instant Discount</option>
                                    <option value="walletafterdelivery">Credited to wallet after delivery</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-12">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea value="" name="description" class="form-control" placeholder="Enter Coupon Description"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-control" style="width: 100%">
                                    <option value="">Select from the dropdown</option>
                                    <option value="flat">Flat (In {{config('app.currency.code')}})</option>
                                    <option value="percentage">In Percentage</option>
                                </select>
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Value <span class="text-danger">*</span></label>
                                <input type="text" value="" name="value" class="form-control" placeholder="Enter Discount Value">
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Maximum Discount</label>
                                <input type="text" value="" name="max_discount" class="form-control" placeholder="Enter Maximum Discount (Optional)">
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Minimum Order</label>
                                <input type="text" value="" name="min_order" class="form-control" placeholder="Enter Minimum Order Value (Optional)">
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Max Usage</label>
                                <input type="text" value="" name="max_usages" class="form-control" placeholder="Enter Maximum Usage (Optional)">
                            </div>

                            <div class="form-group col-lg-6">
                                <label>Valid Till</label>
                                <input type="date" value="" name="valid_till" class="form-control" placeholder="Coupon Expiration Date (Optional)">
                            </div>

                            <div class="form-group col-lg-12">
                                <label>Apply for Users</label>
                                <select value="" name="applied_for_users[]" class="form-control select2" multiple data-placeholder="Select from the Dropdown (Optional)" style="width: 100%">
                                    @foreach ($users as $key => $value)
                                        <option value="{{ $key }}">{{ $value }} (#{{ $key }})</option>
                                    @endforeach
                                </select>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'coupons'])}}",
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
                        html = '<b class="text-danger">' + data + '</b>';

                        if(full.applied_for_users.length > 0){
                            html += '&nbsp;<span class="" data-toggle="tooltip" data-placement="right" title="User Restricted"><i class="fa fa-user-circle"></i></span>'
                        }

                        return html;
                    },
                },
                {
                    data:'type',
                    name: 'type',
                    render: function(data, type, full, meta){
                        return `<b class="text-capitalize">` + data + `</b>`;
                    },
                },
                {
                    data:'value',
                    name: 'value',
                    render: function(data, type, full, meta){
                        return `<b>` + data + `</b>`;
                    },
                },
                {
                    data:'max_discount',
                    name: 'max_discount',
                    render: function(data, type, full, meta){
                        if(data){
                            return `{!! config("app.currency.faicon") !!}` + data;
                        } else{
                            return `N/A`;
                        }
                    },
                },
                {
                    data:'min_order',
                    name: 'min_order',
                    render: function(data, type, full, meta){
                        if(data){
                            return `{!! config("app.currency.faicon") !!}` + data;
                        } else{
                            return `N/A`;
                        }
                    },
                },
                {
                    data:'max_usages',
                    name: 'max_usages',
                    render: function(data, type, full, meta){
                        if(data){
                            return data;
                        } else{
                            return `N/A`;
                        }
                    },
                },
                {
                    data:'coupon_used_count',
                    name: 'coupon_used_count',
                    render: function(data, type, full, meta){
                        if(data){
                            return data;
                        } else{
                            return 0;
                        }
                    },
                },
                {
                    data:'valid_till',
                    name: 'valid_till',
                    render: function(data, type, full, meta){
                        if(data){
                            return moment(data).format('D MMM Y');
                        } else{
                            return 'N/A'
                        }
                    },
                },
                {
                    data:'updated_at',
                    name: 'updated_at',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'status',
                    name: 'status',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + `)">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        /* @if (Myhelper::can('edit_coupon')) */
                            html += `<a class="btn btn-xs mg btn-primary" href="javascript:;" onclick="edit('` + full.id + `')"><i class="fa fa-edit"></i></a>`;
                        /* @endif */

                        /* @if (Myhelper::can('delete_coupon')) */
                            html += `<a class="btn btn-xs mg btn-danger" href="javascript:;" onclick="deleteitem('` + full.id + `')"><i class="fa fa-trash"></i></a>`;
                        /* @endif */

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                }
            ],
            "order": [
                [0, 'asc']
            ],
            "drawCallback": function( settings ) {
                $('[data-toggle="tooltip"]').tooltip()
            }
        });

        $('#couponform').validate({
            rules: {
                code: {
                    required: true,
                },
                rewarded: {
                    required: true,
                },
                description: {
                    required: true,
                },
                type: {
                    required: true,
                },
                value: {
                    required: true,
                    number: true,
                    min: 1,
                },
                max_discount: {
                    required: false,
                    number: true,
                    min: 1,
                },
                min_order: {
                    required: false,
                    number: true,
                    min: 1,
                },
                max_usages: {
                    required: false,
                    number: true,
                    min: 1,
                },
                valid_till: {
                    required: false,
                }
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
                var form = $('#couponform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#couponmodal').modal('hide');
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

        function edit(id){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.fetchdata', ['type' => 'coupons', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;

                        $('#couponmodal').find('.modal-title').text('Edit Dicount Coupon');
                        $('#couponmodal').find('[name=id]').val(id);
                        $('#couponmodal').find('[name=operation]').val('edit');
                        $('#couponmodal').find('[name=code]').val(result.code);
                        $('#couponmodal').find('[name=rewarded]').val(result.rewarded);
                        $('#couponmodal').find('[name=description]').val(result.description);
                        $('#couponmodal').find('[name=type]').val(result.type);
                        $('#couponmodal').find('[name=value]').val(result.value);
                        $('#couponmodal').find('[name=max_discount]').val(result.max_discount);
                        $('#couponmodal').find('[name=min_order]').val(result.min_order);
                        $('#couponmodal').find('[name=max_usages]').val(result.max_usages);
                        $('#couponmodal').find('[name=valid_till]').val(moment(result.valid_till).format('MM/DD/YYYY'));

                        if(result.applied_for_users.length > 0){
                            $('#couponmodal').find('[name="applied_for_users[]"]').val(result.applied_for_users).trigger('change');
                        } else{
                            $('#couponmodal').find('[name="applied_for_users[]"]').val(null).trigger('change');
                        }

                        $('#couponmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        function add(){
            $('#couponmodal').find('.modal-title').text('Add New Dicount Coupon');
            $('#couponform')[0].reset();
            $('#couponform').find('[name=id]').val('');
            $('#couponform').find('[name=operation]').val('new');
            $('#couponmodal').find('[name="applied_for_users[]"]').val(null).trigger('change');
            $('#couponmodal').modal('show');
        }

        $('[name="code"]').on('input', function(e){
            $(this).val( $(this).val().toUpperCase().replace(' ', '') )
        })

        // $('[name="valid_till"]').datepicker({
        //     autoclose: true,
        //     todayHighlight: true,
        // })

        function deleteitem(id){
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
                })
                .then((willDelete) => {
                if (willDelete) {
                    Pace.track(function(){
                        $.ajax({
                    dataType: "JSON",
                            url: "{{ route('dashboard.coupon.submit') }}",
                            method: "POST",
                            data: {
                                '_token':'{{csrf_token()}}',
                                'operation':'delete',
                                'id':id
                            },
                            success: function(data){
                                $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                            }, error: function(errors){
                                showErrors(errors);
                            }
                        });
                    });
                } else {
                    swal({
                        title: "Cancelled Successfully",
                        text: "Your data is safe!",
                        icon: "warning",
                    });
                }
            });
        }

        function changeAction(id, operation = "changestatus"){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.coupon.submit')}}",
                    data: {"_token" : "{{csrf_token()}}", "operation" : operation, "id" : id},
                    method: "POST",
                    success: function(data){
                        notify(data.status, 'success');
                        $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                    },
                    error: function(errors) {
                        showErrors(errors);
                        $('#my-datatable').dataTable().api().ajax.reload(function (json) { }, false);
                    }
                });
            });
        }
    </script>
@endpush
