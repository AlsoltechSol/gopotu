@section('pageheader', 'Order Cancellation Reasons')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Content Management
            <small>Manage Order Cancellation Reasons</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Content Management</li>
            <li class="active">Order Cancellation Reasons</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Order Cancellation Reasons</h3>

                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_cancellation_reason'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
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

    <div class="modal fade-in" id="cancellationreasonmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.cms.submitcms')}}" method="POST" id="cancellationreasonform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">
                    <input type="hidden" name="usertype" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea type="text" name="description" class="form-control" placeholder=""></textarea>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'cancellationreasons'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                    d.usertype = '{{$usertype}}';
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
                    data:'description',
                    name: 'description',
                    render: function(data, type, full, meta){
                        return data
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
                        if(data == 1){
                            html = `<a onclick="changeAction(`+full.id+`)" class="btn btn-sm btn-success"><i class="fa fa-check-circle"></i>&nbsp;Active</a>`;
                        } else{
                            html = `<a onclick="changeAction(`+full.id+`)" class="btn btn-sm btn-warning"><i class="fa fa-remove"></i>&nbsp;Inactive</a>`;
                        }

                        return html;
                    },
                },
                {
                    render: function(data, type, full, meta){
                        var html = '';

                        @if(Myhelper::can('edit_cancellation_reason'))
                            html += `<li><a href="javascript:;" onclick="edit('`+full.id+`')"><i class="fa fa-edit"></i>Edit</a></li>`;
                        @endif

                        @if(Myhelper::can('delete_cancellation_reason'))
                            html += `<li><a href="javascript:;" onclick="deleteitem('`+full.id+`')"><i class="fa fa-trash"></i>Delete</a></li>`;
                        @endif

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
                }
            ],
            "order": [
                [0, 'asc']
            ]
        });

        $('#cancellationreasonform').validate({
            rules: {
                description: {
                    required: true,
                },
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function() {
                var form = $('#cancellationreasonform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            $('#my-datatable').dataTable().api().ajax.reload();
                            form.find('button[type="submit"]').button('reset');
                            form[0].reset();
                            $('#cancellationreasonmodal').modal('hide');
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
                    url: "{{route('dashboard.fetchdata', ['type' => 'cancellationreasons', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;
                        $('#cancellationreasonmodal').find('.modal-title').text('Edit Order Cancellation Reason');
                        $('#cancellationreasonmodal').find('[name=id]').val(id);
                        $('#cancellationreasonmodal').find('[name=operation]').val('cancellationreasonedit');
                        $('#cancellationreasonmodal').find('[name=description]').text(result.description);
                        $('#cancellationreasonmodal').find('[name=usertype]').val('{{$usertype}}');
                        $('#cancellationreasonmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        function add(){
            $('#cancellationreasonmodal').find('.modal-title').text('Add New Order Cancellation Reason');
            $('#cancellationreasonmodal').find('[name=id]').val('');
            $('#cancellationreasonmodal').find('[name=operation]').val('cancellationreasonnew');
            $('#cancellationreasonmodal').find('[name=usertype]').val('{{$usertype}}');
            $('#cancellationreasonmodal').modal('show');
        }

        function changeAction(id){
            @if(!Myhelper::can('edit_cancellation_reason'))
                return false;
            @endif

            Pace.track(function(){
                $.ajax({
                    url: "{{route('dashboard.cms.submitcms')}}",
                    method: "POST",
                    data: {'_token':'{{csrf_token()}}','operation':'cancellationreasonchangeaction','id':id},
                    success: function(data){
                        $('#my-datatable').dataTable().api().ajax.reload();
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

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
                            url: "{{route('dashboard.cms.submitcms')}}",
                            method: "POST",
                            data: {'_token':'{{csrf_token()}}','operation':'cancellationreasondelete','id':id},
                            success: function(data){
                                $('#my-datatable').dataTable().api().ajax.reload();
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
    </script>
@endpush
