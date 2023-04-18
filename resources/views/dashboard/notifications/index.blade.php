@section('pageheader', 'Notifications')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Notifications
            <small>{{ucfirst($type)}} Notifications</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Notifications</li>
            <li class="active">{{ucfirst($type)}} Notifications</li>
        </ol>
    </section>

    <section class="content">
        @include('dashboard.notifications.inc.filter')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Users List</h3>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            {{-- <th><input type="checkbox" name="id[]" value="all" id="select-all-checkbox"></th> --}}
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
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
    </section>

    <div class="modal fade-in" id="notificationmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Profile Picture</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.notifications.submit')}}" method="POST" id="notificationform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="{{$type}}">
                    <input type="hidden" name="user_id[]" value="">

                    <div class="modal-body">
                        @if(in_array($type, ['account','push','email']))
                            <div class="form-group">
                                <label>Heading <span class="text-danger">*</span></label>
                                <textarea name="heading" class="form-control" placeholder="Enter Heading"></textarea>
                            </div>
                        @endif

                        @if(in_array($type, ['account','sms','push','email']))
                            <div class="form-group">
                                <label>Body <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-control" placeholder="Enter Body"></textarea>
                            </div>
                        @endif
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
                url: "{{route('dashboard.fetchdata', ['type' => 'notificationusers'])}}",
                type: "POST",
                data:function( d )
                {
                    d.role_id = $('[name="role_id"]').val();
                    d.status = $('[name="status"]').val();
                    d._token = '{{csrf_token()}}';
                },
            },
            columns:[
                // {
                //     targets: 0,
                //     searchable: false,
                //     orderable: false,
                //     className: 'text-center',
                //     render: function (data, type, full, meta){
                //         var checked = document.getElementById('select-all-checkbox').checked;

                //         if(checked){
                //             return `<input type="checkbox" checked name="user_id[]" value="">`;
                //         } else{
                //             return `<input type="checkbox" name="user_id[]" value="">`;
                //         }
                //     }
                // },
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
                    data:'role',
                    name: 'role',
                    render: function(data, type, full, meta){
                        return `<b class="text-primary"><i>`+data.name+`</i></b>`;
                    },
                    orderable: false,
                    searchable: false,
                },
                {
                    data:'mobile',
                    name: 'mobile',
                    render: function(data, type, full, meta){
                        if(data == null)
                            data = 'N/A';

                        return data;
                    },
                },
                {
                    data:'email',
                    name: 'email',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'status',
                    name: 'status',
                    className: 'text-center',
                    render: function(data, type, full, meta){
                        if(data == 1){
                            html = `<span class="badge badge-md bg-green-active">Active</span>`
                        } else{
                            html = `<span class="badge badge-md bg-yellow-active">Inactive</span>`
                        }

                        return html;
                    },
                    // orderable: false,
                    searchable: false,
                },
                {
                    className: 'text-center',
                    render: function(data, type, full, meta){
                        var html = '';

                        html += `<button onclick="sendnotification(`+full.id+`, '`+full.name+`')" class="btn btn-xs btn-primary"><i class="fa fa-paper-plane"></i> Send</button>`;

                        return html;
                    },
                    orderable: false,
                    searchable: false,
                }
            ],
            "order": [
                [1, 'asc']
            ]
        });

        function sendnotification(id, name){
            $('#notificationmodal').find('.modal-title').text('Send notification to '+name);
            $('#notificationmodal').find('[name="type"]').val('{{$type}}');
            $('#notificationmodal').find('[name="user_id[]"]').val(id);
            $('#notificationmodal').find('[name="heading"]').val('');
            $('#notificationmodal').find('[name="body"]').val('');

            $('#notificationmodal').modal();
        }

        $('#notificationform').validate({
            rules: {
                heading: {
                    required: true,
                },
                body: {
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
                var form = $('#notificationform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form.find('button[type="submit"]').button('reset');
                            form[0].reset();
                            $('#notificationmodal').modal('hide');
                            $('#my-datatable').dataTable().api().ajax.reload();
                            document.getElementById('select-all-checkbox').checked = false;
                        },
                        error: function(errors) {
                            form.find('button[type="submit"]').button('reset');
                            showErrors(errors, form);
                        }
                    });
                });
            }
        });

        // $('#select-all-checkbox').on('change', function(){
        //     var checked = document.getElementById($(this).attr('id')).checked;

        //     if(checked){
        //         $("input[name=user_id\\[\\]]").each(function() {
        //             this.checked = true;
        //         });
        //     } else{
        //         $("input[name=user_id\\[\\]]").each(function() {
        //             this.checked = false;
        //         });
        //     }
        // });
    </script>
@endpush
