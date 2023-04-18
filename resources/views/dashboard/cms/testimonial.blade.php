@section('pageheader', 'Testimonials')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Content Management
            <small>Manage Testimonials</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Content Management</li>
            <li class="active">Testimonials</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Testimonials</h3>

                <div class="box-tools pull-right">
                    @if(Myhelper::can('add_testimonial'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            {{-- <th>Avatar</th> --}}
                            <th>Client Name</th>
                            <th style="max-width: 250px">Testimonial</th>
                            {{-- <th>Designation</th> --}}
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

    <div class="modal fade-in" id="testimonialmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update Profile Picture</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.cms.submitcms')}}" method="POST" id="testimonialform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Client Name">
                        </div>

                        <div class="form-group">
                            <label>Testimonial Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" placeholder="Enter Client Testimonial Content"></textarea>
                        </div>

                        {{-- <div class="form-group">
                            <label>Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" placeholder="Enter Client Designation">
                        </div> --}}

                        {{-- <div class="form-group">
                            <label>Avatar Image</label>
                            <input type="file" name="avatar_image" accept="image/*" class="form-control">
                        </div> --}}
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
                url: "{{route('dashboard.fetchdata', ['type' => 'testimonials'])}}",
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
                // {
                //     data:'avatar',
                //     name: 'avatar',
                //     render: function(data, type, full, meta){
                //         return `<img src="`+data+`" style="max-width: 25px">`;
                //     },
                //     orderable: false,
                //     searchable: false,
                // },
                {
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        return data
                    },
                    searchable: true,
                },
                // {
                //     data:'designation',
                //     name: 'designation',
                //     render: function(data, type, full, meta){
                //         return data
                //     },
                // },
                {
                    data:'content',
                    name: 'content',
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

                        @if(Myhelper::can('edit_testimonial'))
                            html += `<li><a href="javascript:;" onclick="edit('`+full.id+`')"><i class="fa fa-edit"></i>Edit</a></li>`;
                        @endif

                        @if(Myhelper::can('delete_testimonial'))
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

        $('#testimonialform').validate({
            rules: {
                name: {
                    required: true,
                },
                content: {
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
                var form = $('#testimonialform');

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
                            $('#testimonialmodal').modal('hide');
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
                    url: "{{route('dashboard.fetchdata', ['type' => 'testimonials', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;
                        $('#testimonialmodal').find('.modal-title').text('Edit Role');
                        $('#testimonialmodal').find('[name=id]').val(id);
                        $('#testimonialmodal').find('[name=operation]').val('testimonialedit');
                        $('#testimonialmodal').find('[name=name]').val(result.name);
                        // $('#testimonialmodal').find('[name=designation]').val(result.designation);
                        $('#testimonialmodal').find('[name=content]').val(result.content);
                        $('#testimonialmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        function add(){
            $('#testimonialmodal').find('.modal-title').text('Add New Testimonial');
            $('#testimonialmodal').find('[name=id]').val('');
            $('#testimonialmodal').find('[name=operation]').val('testimonialnew');
            $('#testimonialmodal').modal('show');
        }

        function changeAction(id){
            @if(!Myhelper::can('edit_testimonial'))
                return false;
            @endif

            Pace.track(function(){
                $.ajax({
                    url: "{{route('dashboard.cms.submitcms')}}",
                    method: "POST",
                    data: {'_token':'{{csrf_token()}}','operation':'testimonialchangeaction','id':id},
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
                            data: {'_token':'{{csrf_token()}}','operation':'testimonialdelete','id':id},
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
