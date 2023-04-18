@section('pageheader', $cmscontent->page_title)
@extends('layouts.website')

@section('content')
<!-- Header Start -->
<header class="page-banner-area" id="home">
    <div class="section-overlay d-flex">
        <div class="container">
            <div class="header-caption">
                <h1 class="header-caption-heading text-capitalize">{{$cmscontent->page_title}}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item text-capitalize"><a href="{{ route('index') }}">Home</a></li>
                        <li class="breadcrumb-item active text-capitalize" aria-current="page"><i class="fas fa-angle-right"></i>{{$cmscontent->page_title}}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</header>
<!-- Header End -->

<section class="blog-page-area blog-grid-page-area classic-blog-page">
    <div class="container">
        <!-- Row Start-->
        <div class="row">
            <div class="col-md-12">
                {!! $cmscontent->content !!}
            </div>
        </div>
    </div>
</section>
@endsection

@push('style')
    <style>
        h2, h3 {
            margin-top: 20px;
            margin-bottom: 10px;
        }

        h1 {
            margin-bottom: 10px;
        }

        p {
            /* text-align: justify; */
        }
    </style>
@endpush
