@section('pageheader', 'CMS')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Content Management
            <small>Manage CMS</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Content Management</li>
            <li class=""><a href="{{route('dashboard.cms.index', ['type' => 'contents'])}}">CMS</a></li>
            <li class="active">{{$content->page_name}}</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Update Content</h3>

                <div class="box-tools pull-right">
                    {{-- Tools --}}
                </div>
            </div>
            <form action="{{route('dashboard.cms.submitcms')}}" method="POST" id="contentform">
                <div class="box-body">
                    @csrf
                    <input type="hidden" name="operation" value="contentedit">
                    <input type="hidden" name="id" value="{{$content->id}}">

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Page Name</label>
                            <input type="text" class="form-control" disabled value="{{$content->page_name}}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Identification Slug</label>
                            <input type="text" class="form-control" disabled value="{{$content->slug}}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="page_title" class="form-control" value="{{$content->page_title}}">
                        </div>

                        <div class="form-group col-md-12">
                            <label>Page Content</label>
                            <textarea id="ck-editor" name="content">{!!$content->content!!}</textarea>
                        </div>
                    </div>

                    {{-- <hr> --}}

                    {{-- <div class="row">
                        <div class="form-group col-md-6">
                            <label>Meta Tags</label>
                            <textarea class="form-control" name="meta_tags" placeholder="SEO Tools">{!!$content->meta_tags!!}</textarea>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Meta Title</label>
                            <textarea class="form-control" name="meta_title" placeholder="SEO Tools">{!!$content->meta_title!!}</textarea>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Meta Description</label>
                            <textarea class="form-control" name="meta_description" placeholder="SEO Tools">{!!$content->meta_title!!}</textarea>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Meta Keywords</label>
                            <textarea class="form-control" name="meta_keywords" placeholder="SEO Tools">{!!$content->meta_keywords!!}</textarea>
                        </div>
                    </div> --}}
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $(function () {
            CKEDITOR.replace('ck-editor');
        });

        $('#contentform').validate({
            rules: {
                page_title: {
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
                for (instance in CKEDITOR.instances) {
                    CKEDITOR.instances[instance].updateElement();
                }

                var form = $('#contentform');

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
