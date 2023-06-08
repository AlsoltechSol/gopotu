@section('pageheader', 'Colors Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Colors Management
        </h1>
        @if(Myhelper::hasRole(['branch', 'admin']))
            @if (session('admin'))
                <div class="mt-5">

                    <a href="{{ route('admin.login') }}"><button class="btn btn-danger">Back to admin</button> </a>
                </div>

            @endif
        @endif
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Master</li>
            <li class="active">Colors</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Colors</h3>

                <div class="box-tools pull-right">
                    @if (Myhelper::can('add_color'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Color</th>
                            <th>Color Code </th>
                            <th>Last Updated</th>
                             <th>Action</th> 
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade" id="colormodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.master.submit')}}" method="POST" id="colorform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Code <span class="text-danger">*</span></label>
                            <div id="cp11" class="input-group colorpicker-component">
                                <input type="text" value="" name="code" class="form-control" placeholder="Enter Color Code" required/>
                                <span class="input-group-addon"><i></i></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" value="" name="name" class="form-control" placeholder="Enter Color Name" required>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'colors'])}}",
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
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    render: function(data, type, full, meta){
                        return `<img class="datatable-color" src="https://www.thecolorapi.com/id?format=svg&named=false&hex=` + full.code.replace('#', '') + `">`
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data:'code',
                    name: 'code',
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
                    render: function(data, type, full, meta){
                        var html = '';

                        /* @if (Myhelper::can('edit_color')) */
                         html += `<li><a href="javascript:;" onclick="edit('` + full.id + `')"><i class="fa fa-edit"></i>Edit</a></li>`;
                        /* @endif */

                        /* @if (Myhelper::can('delete_color')) */
                         html += `<li><a href="javascript:;" onclick="deleteitem('` + full.id + `')"><i class="fa fa-trash"></i>Delete</a></li>`;
                        /* @endif */

                        var menu = `<div class="btn-group">\
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">\
                                    <i class="fa fa-bars"></i>&nbsp;&nbsp;<span class="fa fa-caret-down"></span>\
                                </button>\
                                <ul class="dropdown-menu">` + html + `</ul>\
                            </div>`;

                        return menu;
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

        $('#colorform').validate({
            rules: {
                code: {
                    required: true,
                },
                name: {
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
                var form = $('#colorform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#colormodal').modal('hide');
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
                    url: "{{route('dashboard.fetchdata', ['type' => 'colors', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;

                        $('#colorform')[0].reset();
                        $('#colorform').find('[name=id]').val(id);
                        $('#colorform').find('[name=operation]').val('color-edit');
                        $('#colorform').find('[name=name]').val(result.name);
                        $('#colorform').find('[name=code]').val(result.code);

                        $('#colormodal').find('.modal-title').text('Edit Color');
                        $('#colormodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        function add(){
            $('#colorform')[0].reset();
            $('#colorform').find('[name=operation]').val('color-new');

            $('#colormodal').find('.modal-title').text('Add New Color');
            $('#colormodal').modal('show');
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
                    dataType: "JSON",
                            url: "{{ route('dashboard.master.submit') }}",
                            method: "POST",
                            data: {
                                '_token':'{{csrf_token()}}',
                                'operation':'color-delete',
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

        $('[name="code"]').change(function(e){
            var colorcode = $(this).val();
            if(colorcode){
                Pace.track(function(){
                    $.ajax({
                    dataType: "JSON",
                        url: "https://www.thecolorapi.com/id?format=json&hex=" + colorcode.replace('#',''),
                        success: function(data){
                            if(data.code == 400){
                                console.log(data)
                            } else{
                                $('#colorform').find('[name=name]').val(data.name.value);
                            }
                        }, error: function(errors){
                            console.log(errors)
                        }
                    });
                });
            }
        })

        $( document ).ready(function() {
            $('#cp11').colorpicker();
        });
    </script>
@endpush
