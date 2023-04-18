@section('pageheader', 'Attributes Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Attributes Management <small>Attribute Variant</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Master</li>
            <li class=""><a href="{{ route('dashboard.master.index', ['type' => 'attribute']) }}">Attributes</a></li>
            <li class="">{{$attribute->name}}</li>
            <li class="">Variants</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Attributes</h3>

                <div class="box-tools pull-right">
                    @if (Myhelper::can('add_attribute_variant'))
                        <button class="btn btn-primary btn-sm" onclick="add()"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <table id="my-datatable" class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Attribute</th>
                            <th>Name</th>
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

    <div class="modal fade" id="attributevariantmodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.master.submit')}}" method="POST" id="attributevariantform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="attribute_id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" value="" name="name" class="form-control" placeholder="Enter Variant Name" required>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'attribute_varaints'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                    d.attribute_id = '{{$attribute->id}}';
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
                    data:'attribute',
                    name: 'attribute.name',
                    render: function(data, type, full, meta){
                        return data.name;
                    },
                },
                {
                    data:'name',
                    name: 'name',
                    render: function(data, type, full, meta){
                        return `<b class="text-primary">` + data + `</b>`
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

                        /* @if (Myhelper::can('edit_attribute_variant')) */
                            html += `<li><a href="javascript:;" onclick="edit('` + full.id + `')"><i class="fa fa-edit"></i>Edit</a></li>`;
                        /* @endif */

                        /* @if (Myhelper::can('delete_attribute_variant')) */
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

        $('#attributevariantform').validate({
            rules: {
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
                var form = $('#attributevariantform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#attributevariantmodal').modal('hide');
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
                    url: "{{route('dashboard.fetchdata', ['type' => 'attribute_varaints', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;

                        $('#attributevariantform')[0].reset();
                        $('#attributevariantform').find('[name=id]').val(id);
                        $('#attributevariantform').find('[name=operation]').val('attribute-variant-edit');
                        $('#attributevariantform').find('[name=name]').val(result.name);
                        $('#attributevariantform').find('[name=attribute_id]').val('{{$attribute->id}}');

                        $('#attributevariantmodal').find('.modal-title').text('Edit Attribute Variant | {{$attribute->name}}');
                        $('#attributevariantmodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

        function add(){
            $('#attributevariantform')[0].reset();
            $('#attributevariantform').find('[name=operation]').val('attribute-variant-new');
            $('#attributevariantform').find('[name=attribute_id]').val('{{$attribute->id}}');
            $('#attributevariantform').find('[name=id]').val('');

            $('#attributevariantmodal').find('.modal-title').text('Add New Attribute | {{$attribute->name}}');
            $('#attributevariantmodal').modal('show');
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
                                'operation':'attribute-variant-delete',
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

        $('[name="name"]').keyup(function(e){
            $(this).val($(this).val().toUpperCase().replace(' ', ''));
        })
    </script>
@endpush
