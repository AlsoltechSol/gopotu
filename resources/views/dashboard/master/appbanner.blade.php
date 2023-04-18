@section('pageheader', 'App Banners')
@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Banners Management <small class="text-capitalize">{{$position}} Banners</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{route('dashboard.home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Master</li>
            <li class="">App Banners</li>
            <li class="active text-capitalize">{{$position}} Banners</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title text-capitalize">All {{$position}} Banners</h3>

                <div class="box-tools pull-right">
                    @if (Myhelper::can('add_app_banner'))
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#dropzone-modal"><i class="fa fa-plus"></i> Add New</button>
                    @endif
                </div>
            </div>
            <div class="box-body">
                <div class="row" id="appbanner-images">

                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="dropzone-modal">
        <div class="modal-dialog">
            <div class="modal-content modal-lg">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Add New {{ Str::ucfirst($position) }} Banner

                        @switch($position)
                            @case('top')
                                <small><code>Dimension: 950 X 450</code></small>
                                @break
                            @case('middle')
                            @case('footer')
                                <small><code>Dimension: 1300 X 350</code></small>
                                @break
                            @default
                        @endswitch
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @switch($position)
                        @case('top')
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <select name="type" class="form-control select2" style="width: 25%" onchange="changeDropZoneType(this)">
                                            <option value="">Select Category *</option>
                                            <option value="mart">Mart</option>
                                            <option value="restaurant">Restaurant</option>
                                            <option value="service">Service</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @break
                    @endswitch
                    <form action="{{ route('dashboard.master.submit') }}" method="POST" class="dropzone" id="dropzone">
                        @csrf
                        <input type="hidden" name="operation" value="appbanner-new">
                        <input type="hidden" name="position" value="{{ $position }}">
                        <input type="hidden" name="type" value="">
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .box-profile .product-name{
            margin-top: 15px;
            font-weight: 500;
        }
    </style>
@endpush

@push('script')
    <script>
        $( document ).ready(function() {
            loadGalleryImages()
        })

        Dropzone.options.dropzone = {
            acceptedFiles: `image/*`,
            method: "post",
            init: function() {
                this.on("success", function(file, response) {
                    loadGalleryImages()
                })
            },
            error: function(file, errors) {
                showErrors(errors, file, 'dropzone');
            }
        };

        $('[data-dismiss="modal"]').on('click', function(e){
            var dropzone = Dropzone.forElement("#dropzone");
            dropzone.removeAllFiles(true);
        })

        function loadGalleryImages(){
            Pace.track(function(){
                $.ajax({
                dataType: "JSON",
                    url: "{{route('dashboard.master.ajax')}}",
                    method: "POST",
                    data: {'_token' : '{{csrf_token()}}', 'position' : '{{ $position }}', 'type' : 'fetch-appbanners' },
                    success: function(result){
                        var html = "";

                        if(result.images.length > 0) {
                            result.images.forEach(element => {
                                html += `<div class="col-sm-4" style="margin-bottom: 2rem">
                                    <a href="` + element.image_path + `" data-toggle="lightbox" data-gallery="gallery">
                                        <img src="` + element.image_path + `" class="img-fluid mb-2" style="width: 100%;"/>
                                    </a>`

                                /* @if(Myhelper::can('delete_app_banner')) */
                                    html += `<button class="btn btn-block btn-danger" onclick="deleteBannerImage(` + element.id + `)"><i class="fa fa-trash"></i> Delete</button>`;
                                /* @endif */

                                if(element.type){
                                    html += `<p class="text-center" style="margin: 7px 0 0 0;">Category: <b class="text-capitalize">` + element.type + `</b></p>`
                                }

                                html += `</div>`;
                            });
                        } else{
                            html += `<div class="col-md-12">
                                        <div style="background: aliceblue; padding: 20px 0; border: 1px solid #3c8dbc;">
                                            <h5 class="text-center">No App Banners Uploaded</h5>
                                        </div>
                                    </div>`
                        }

                        $('#appbanner-images').html(html);
                    }, error: function(errors){
                        showErrors(errors);
                    }
                });
            });
        }

        function deleteBannerImage(image_id){
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
                            url: "{{route('dashboard.master.submit')}}",
                            method: "POST",
                            data: {'_token':'{{csrf_token()}}','operation':'appbanner-delete','id':image_id},
                            success: function(data){
                                loadGalleryImages()
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

        function changeDropZoneType(vle){
            $('#dropzone').find('[name="type"]').val($(vle).val())
        }
    </script>
@endpush
