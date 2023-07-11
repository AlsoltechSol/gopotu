@section('pageheader', 'Categories Management')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Categories Management
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

            @if($level->type == 'level1')
                <li class="active">Categories</li>
            @elseif($level->type == 'level2' || $level->type == 'level3')
                <li><a href="{{ route('dashboard.master.index', ['type' => 'category']) }}">Categories</a></li>
                <li class="">{{ $parent_category->name }}</li>
                <li class="active">{{ $level->name }} Categories</li>
            @endif
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    All Categories
                    <small>{{$level->name}} {{ (isset($parent_category) && $parent_category) ? '| ' . $parent_category->name : null }}</small>
                </h3>

                <div class="box-tools pull-right">
                    @if (Myhelper::can('add_category'))
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
                            <th>Type</th>
                            @if (in_array($level->type, ['level2','level3']))
                                <th>Parent Category</th>
                            @endif
                            <th>Icon</th>
                            
                            <th>Last Updated</th>
                            <th>Featured</th>
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

    <div class="modal fade" id="categorymodal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <form action="{{route('dashboard.master.submit')}}" method="POST" id="categoryform" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="parent_id" value="">
                    <input type="hidden" name="operation" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" value="" name="name" class="form-control" placeholder="Enter Category Name" required>
                        </div>

                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                <option value="mart">Mart</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="meat">Meat</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Select Scheme <span class="text-danger">*</span></label>
                            <select name="scheme_id" class="form-control select2" style="width: 100%">
                                <option value="">Select from the dropdown</option>
                                @foreach ($schemes as $item)
                                    <option value="{{$item->id}}">{{isset(\App\Model\Commission::where('scheme_id',$item->id)->where('provider_id', 1)->first()->value) ? ucwords($item->name) : ''}}</option>
                                @endforeach
                                
                               
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Icon &nbsp;&nbsp;<code>Recommended Dimension - 250 X 150</code></label>
                            <input type="file" value="" name="icon" class="form-control" accept="image/*" required>
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
                url: "{{route('dashboard.fetchdata', ['type' => 'categories'])}}",
                type: "POST",
                data:function( d )
                {
                    d._token = '{{csrf_token()}}';
                    d.parent_id = '{{ (isset($parent_category) && $parent_category) ? $parent_category->id : null }}';
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
                    data:'type',
                    name: 'type',
                    render: function(data, type, full, meta){
                        return `<b class="text-capitalize">`+ data +`</b>`
                    },
                },
                /* @if (in_array($level->type, ['level2','level3'])) */
                {
                    data:'parent_category',
                    name: 'parent_category.name',
                    render: function(data, type, full, meta){
                        return data.name
                    },
                    orderable: false,
                    searchable: false,
                },
                /* @endif */
                {
                    render: function(data, type, full, meta){
                        return `<img class="datatable-icon" style="width: auto;" src="` + full.icon_path + `">`
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data:'updated_at',
                    name: 'updated_at',
                    render: function(data, type, full, meta){
                        return data
                    },
                },
                {
                    data:'is_featured',
                    name: 'is_featured',
                    render: function(data, type, full, meta){
                        var checked = "";
                        if(data == '1'){
                            checked = "checked";
                        }

                        return `<label class="switch">
                                    <input type="checkbox" ` + checked + ` onChange="changeAction(` + full.id + `, 'category-changefeatured')">
                                    <span class="slider round"></span>
                                </label>`;
                    },
                    className: 'text-center'
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

                        /* @if (in_array( $level->type, ['level1','level2'] )) */
                            /* @if ( $level->type == 'level1' ) */
                            html += `<li><a href="{{ route('dashboard.master.index', ['type' => 'level2-category']) }}/` + full.id + `"><i class="fa fa-list-alt"></i>Sub Categories</a></li>`;
                            /* @endif */

                            /* @if ( $level->type == 'level2' ) */
                            // html += `<li><a href="{{ route('dashboard.master.index', ['type' => 'level3-category']) }}/` + full.id + `"><i class="fa fa-list-alt"></i>Sub Categories</a></li>`;
                            /* @endif */
                        /* @endif */

                        /* @if (Myhelper::can('edit_category')) */
                        html += `<li><a href="javascript:;" onclick="edit('` + full.id + `')"><i class="fa fa-edit"></i>Edit</a></li>`;
                        /* @endif */

                        /* @if (Myhelper::can('delete_category')) */
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

        $('#categoryform').validate({
            rules: {
                name: {
                    required: true,
                },
                type: {
                    required: true,
                },
                icon: {
                    required: function(element) {
                        return $("#categoryform").find('[name=operation]').val() == 'category-new';
                    }
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
                var form = $('#categoryform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form[0].reset();
                            $('#categorymodal').modal('hide');
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
                    url: "{{route('dashboard.fetchdata', ['type' => 'categories', 'fetch' => 'single'])}}" + "/" + id,
                    data: {'token':'{{csrf_token()}}'},
                    success: function(data){
                        var result = data.result;
                        console.log(result);

                        $('#categoryform')[0].reset();
                        $('#categoryform').find('[name=parent_id]').val('{{ (isset($parent_category) && $parent_category) ? $parent_category->id : null }}');
                        $('#categoryform').find('[name=id]').val(id);
                        $('#categoryform').find('[name=operation]').val('category-edit');
                        $('#categoryform').find('[name=name]').val(result.name);
                        $('#categoryform').find('[name=type]').val(result.type).trigger('change');
                        $('#categoryform').find('[name=scheme_id]').val(result.scheme_id).trigger('change');

                        // $('#categorymodal').find('.modal-title').html('Edit Category <small>{{$level->name}}</small>');
                        $('#categorymodal').find('.modal-title').html('Edit Category');
                        $('#categorymodal').modal('show');
                    }, error: function(errors){
                        showErrors(errors, form);
                    }
                });
            });
        }

        function add(){
            $('#categoryform')[0].reset();
            $('#categoryform').find('[name=parent_id]').val('{{ (isset($parent_category) && $parent_category) ? $parent_category->id : null }}');
            $('#categoryform').find('[name=operation]').val('category-new');
            $('#categoryform').find('[name=id]').val('');
            $('#categoryform').find('[name=type]').val('').trigger('change');
            $('#categoryform').find('[name=scheme_id]').val('').trigger('change');


            // $('#categorymodal').find('.modal-title').html('Add New Category <small>{{$level->name}} {{ (isset($parent_category) && $parent_category) ? "| " . $parent_category->name : null }}</small>');
            $('#categorymodal').find('.modal-title').html('Add New Category');
            $('#categorymodal').modal('show');
        }

        function changeAction(id, operation = "category-changestatus"){
            Pace.track(function(){
                $.ajax({
                    dataType: "JSON",
                    url: "{{route('dashboard.master.submit')}}",
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
                                'operation':'category-delete',
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
    </script>
@endpush
