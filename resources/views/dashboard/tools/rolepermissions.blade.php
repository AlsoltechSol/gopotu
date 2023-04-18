@section('pageheader', 'Default Permission')
@extends('layouts.app')

@section('content')

    <section class="content-header">
        <h1>
            Roles & Permissions
            <small>Manage Permission for {{$role->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Tools</li>
            <li class=""><a href="{{route('dashboard.tools.roles')}}">Roles</a></li>
            <li class="active">Default Permission</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Permission List</h3>

                <div class="box-tools pull-right"></div>
            </div>
            <form action="{{route('dashboard.tools.rolepermissions.submit')}}" method="POST" id="permissionform">
                @csrf
                <input type="hidden" name="role_id" value="{{$role->id}}">
                <div class="box-body">
                    <table class="table table-bordered table-striped" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $flag = false; @endphp
                            @foreach($permissions as $key => $value)
                                @if(count($permissions[$key]) > 0)
                                    @php $flag = true; @endphp
                                    <tr>
                                        <td style="width: 10%;">{{ucfirst($key)}}</td>
                                        <td>
                                            <div class="row">
                                                @foreach($permissions[$key] as $item)
                                                    <div class="col-md-3">
                                                        <input {{(in_array($item->id, $default)) ? 'checked' : ''}} type="checkbox" name="permissions[]" id="{{$item->slug}}" value="{{$item->id}}"> <label for="{{$item->slug}}">&nbsp;{{$item->name}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            @if($flag == false)
                                <tr><td colspan="2" class="text-center">No Data Found</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('style')
    <style>
        td{
            padding: 10px !important;
        }
    </style>
@endpush

@push('script')
    <script>
        $('#permissionform').validate({
            rules: {
                name: {
                    required: true,
                },
                slug: {
                    required: true,
                },
                type: {
                    required: true,
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
                var form = $('#permissionform');

                Pace.track(function(){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            notify(data.status, 'success');
                            form.find('button[type="submit"]').button('reset');
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
