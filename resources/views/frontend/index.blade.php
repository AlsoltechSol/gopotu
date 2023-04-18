@section('pageheader', 'Home')
@extends('layouts.website')

@section('content')

    <!-- Header Start -->
    <header id="home" class="home-banner-area position-relative">
        <div class="particle-bg"><img src="{{ asset('website/img/hero-bg-effect.png') }}" alt="particle"></div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!--Hero Area Wrapper-->
                    <div class="hero-area-wrapper position-relative wow fadeInLeft">
                        <div class="hero-area-content">
                            <h1>
                                Arambagh Plaza
                                <small><i>&ndash; Best Quality & Lowest Price Guaranteed</i></small>
                            </h1>

                            <p>We are India's newest online food and grocery store. With over huge products and more brands in our catalogue you will find everything you are looking for.</p>

                            <div class="hero-button-box">
                                <a href="https://play.google.com/store/apps" class="theme-button" target="_blank"><i class="fab fa-google-play"></i>Play Store</a>
                                <a href="https://apps.apple.com/us/app/apple-store/id375380948" class="theme-button d-none"><i class="fab fa-apple"></i>App Store</a>
                            </div>
                        </div>
                    </div>
                    <!--Hero Area Wrapper-->
                </div>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <!-- About Area Start -->
    <section id="about" class="about-area">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-wrapper">
                        <!--About Left Side Start-->
                        <div class="about-left-side wow fadeInLeft">
                            <img src="{{ asset('website/img/about-page-left.png') }}" alt="About">
                        </div>
                        <!--About Left Side End-->

                        <!--About Right Side Start-->
                        <div class="about-right-side">
                            <div class="section-title-area">
                                <h2 class="section-heading">What is ArambaghPlaza?</h2>

                                <p class="section-subheading">
                                    ArambaghPlaza is India's newest online food and grocery store. With over huge products and more brands in our catalogue you will find everything you are looking for. Right from Rice, and Dals, Spices and seasoning to Packaged products, Beverages, personal care products - we have it all, Choose from a wide range of options in every category, exclusively handpicked to help you find the best quality available at the lowest prices. Select a time slot for delivery and your order will be delivered right to your doorstep, anywhere in selected area of West Bengal. You can pay online using your debit/credit card or by cash on delivery.

                                    <br><br>
                                    <i>We guarantee on-time delivery, and the best quality!</i>

                                    <br><br>
                                    <b><i>Happy Shopping...</i></b>
                                </p>
                            </div>

                            {{-- <!--Counter Area Start-->
                            <div class="counter-area wow fadeInDown">
                                <div class="counter-box">
                                    <img src="{{ asset('website/img/counter-bg1.png') }}" alt="Counter">
                                    <div class="count-content"><span class="counter">1.2</span>M</div>
                                    <h5 class="count-text">Downloads</h5>
                                </div>

                                <div class="counter-box">
                                    <img src="{{ asset('website/img/counter-bg2.png') }}" alt="Counter">
                                    <div class="count-content"><span class="counter">4.8</span></div>
                                    <h5 class="count-text">Avg Rating</h5>
                                </div>

                                <div class="counter-box">
                                    <img src="{{ asset('website/img/counter-bg3.png') }}" alt="Counter">
                                    <div class="count-content"><span class="counter">93</span>K</div>
                                    <h5 class="count-text">Reviews</h5>
                                </div>
                            </div>
                            <!--Counter Area End--> --}}
                        </div>
                        <!--About Right Side End-->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- About Area End -->

    <!-- Get Free Download Area Start -->
    <section id="download" class="get-free-download-area bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="get-free-download-wrapper">

                        <div class="section-title-area">
                            <h2 class="section-heading">Get Free Download</h2>

                            <p class="section-subheading">We are a startup organization comprising of few energetic youths. We foresee the necessity of services like ArambaghPlaza for the people of West Bengal..</p>

                            <div class="hero-button-box">
                                <a href="https://play.google.com/store/apps" class="d-none"><img src="{{ asset('website/img/apple-store-icon.png') }}" alt="Apple Store"></a>
                                <a href="https://apps.apple.com/us/app/apple-store/id375380948" target="_blank" class=""><img src="{{ asset('website/img/google-play-store-icon.png') }}" alt="Play Store"></a>
                            </div>
                        </div>

                        <div class="free-download-mobile-mockup wow fadeInRight">
                            <img src="{{ asset('website/img/get-free-download1.png') }}" alt="Mockup">
                            <img src="{{ asset('website/img/get-free-download2.png') }}" alt="Mockup2">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Get Free Download Area End -->

    <!-- Reviews Area Start -->
    <section id="reviews" class="reviews-area">
        <div class="container">
            @if(count($testimonials) > 0)
                <!-- Reviews Slider Area Start -->
                <div class="row reviews-slider-wrapper">
                    <div class="col-12 col-sm-12 col-md-6">
                        <div class="reviews-slider owl-carousel position-relative">
                            @foreach ($testimonials as $item)
                                <!-- single trainer start-->
                                <div class="item">
                                    <div class="single-review-slide-box">
                                        <div class="review-content">
                                            <span><i class="flaticon-straight-quotes"></i></span>
                                            <p>{{$item->content}}</p>
                                            <h2>- {{$item->name}}</h2>
                                        </div>

                                    </div>
                                </div>
                                <!-- single trainer end-->
                            @endforeach
                        </div>

                        <!-- Next/Preview Button Start-->
                        <div class="reviews_slide_nav">
                            <span class="testi_prev">
                                <i class="flaticon-left-arrow"></i>
                            </span>
                            <span class="testi_next">
                                <i class="flaticon-right-arrow"></i>
                            </span>
                        </div>
                        <!-- Next/Preview Button End-->
                    </div>

                    <div class="col-12 col-sm-12 col-md-6">
                        <div class="reviews-bg-area">
                            <img src="{{ asset('website/img/reviews-slider-bg.jpg') }}" alt="Review">
                        </div>
                    </div>
                </div>
                <!-- Reviews Slider Area End -->
            @endif

            <!-- Client Logo Slider Area Start -->
            <div class="row">
                <div class="client-logo-slider owl-carousel">

                    <!-- Single Client Logo Slider Logo start-->
                    <div class="item">
                        <div class="single-client-logo-box">
                            <figure><img src="{{ asset('website/img/client-logo-slider/1.jpg') }}" alt="Logo img"></figure>
                        </div>
                    </div>
                    <!-- Single Client Logo Slider Logo end-->

                    <!-- Single Client Logo Slider Logo start-->
                    <div class="item">
                        <div class="single-client-logo-box">
                            <figure><img src="{{ asset('website/img/client-logo-slider/2.jpg') }}" alt="Logo img"></figure>
                        </div>
                    </div>
                    <!-- Single Client Logo Slider Logo end-->

                    <!-- Single Client Logo Slider Logo start-->
                    <div class="item">
                        <div class="single-client-logo-box">
                            <figure><img src="{{ asset('website/img/client-logo-slider/3.jpg') }}" alt="Logo img"></figure>
                        </div>
                    </div>
                    <!-- Single Client Logo Slider Logo end-->

                    <!-- Single Client Logo Slider Logo start-->
                    <div class="item">
                        <div class="single-client-logo-box">
                            <figure><img src="{{ asset('website/img/client-logo-slider/4.jpg') }}" alt="Logo img"></figure>
                        </div>
                    </div>
                    <!-- Single Client Logo Slider Logo end-->

                    <!-- Single Client Logo Slider Logo start-->
                    <div class="item">
                        <div class="single-client-logo-box">
                            <figure><img src="{{ asset('website/img/client-logo-slider/5.jpg') }}" alt="Logo img"></figure>
                        </div>
                    </div>
                    <!-- Single Client Logo Slider Logo end-->

                </div>
            </div>
            <!-- Client Logo Slider Area End -->
        </div>
    </section>
    <!-- Reviews Area End -->

    <!-- Contact Area Start -->
    <section id="contact" class="contact-area bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8">
                    <!--contact-left-text-->
                    <div class="contact-form-wrapper">
                        <form id="contactform" method="post">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <!--form-group-->
                            <div class="form-group">
                                <label for="mobile">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" required>
                            </div>
                            <!--form-group-->
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <!--form-group-->
                            <div class="message-area form-group">
                                <label for="message">Write Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            </div>
                            <!--message-area-->
                            <div class="d-flex message-button-wrap">
                                <div class="subm-btn theme-button">
                                    <input type="submit" class="btn contact-btn" value="Send Message">
                                </div>
                                <div class="sending-gif" style="display: none">
                                    <img src="{{ asset('website/img/loading.gif') }}" alt="send-gif">
                                </div>
                            </div>
                            <!--d-flex-->
                        </form>
                        <!--form-->

                        <div class="success-msg alert alert-success" style="display: none"></div>
                        <div class="error-msg alert alert-danger" style="display: none"></div>
                    </div>
                    <!--contact-form-->
                </div>
                <!--col-md-8-->

                <div class="col-12 col-sm-12 col-md-4">
                    <div class="section-title-area">
                        <h2 class="section-heading">Get in touch</h2>
                    </div>
                    <div class="contact-address">
                        <ul>
                            <li><span><i class="flaticon-home"></i></span>{{ config('contact.address') }}</li>
                            <li><span><i class="flaticon-e-mail-envelope"></i></span>{{ config('contact.email') }}</li>
                            <li><span><i class="flaticon-call"></i></span>+91 {{ config('contact.mobile') }}</li>
                        </ul>
                    </div>
                    <!--contact-address-->
                </div>
                <!--col-md-4-->
            </div>
            <!--row-->
        </div>
        <!--container-->
        <div class="google-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3674.4505244672437!2d87.6753726!3d22.933629399999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjLCsDU2JzAxLjEiTiA4N8KwNDAnMzEuMyJF!5e0!3m2!1sen!2sin!4v1633983952909!5m2!1sen!2sin" style="border:0" allowfullscreen></iframe>
            <!--iframe-->
        </div>
    </section>
    <!-- Contact Area End -->

@endsection

@push('script')
    <script>
        $('#contactform').validate({
            rules: {
                name: {
                    required: true,
                },
                mobile: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 10,
                },
                email: {
                    required: true,
                    email: true,
                },
                message: {
                    required: true,
                },
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                error.insertAfter( element );
            },
            submitHandler: function() {
                var form = $('#contactform');

                var name = form.find('[name="name"]').val()
                var email = form.find('[name="email"]').val()
                var mobile = form.find('[name="mobile"]').val()
                var message = form.find('[name="message"]').val()

                form.find('.sending-gif').show();
                $('#contact').find('.error-msg').hide();
                $('#contact').find('.success-msg').hide();

                $.ajax({
                    url: "{{url('api/support-ticket/submit')}}",
                    method: "POST",
                    dataType: "JSON",
                    data: {'name': name, 'email': email, 'mobile': mobile, 'subject': 'contactrequest', 'message': message},
                    success: function(result){
                        form.find('.sending-gif').hide();

                        if(result.status == "success"){
                            form[0].reset();

                            $('#contact').find('.success-msg').show();
                            $('#contact').find('.success-msg').html("<strong>Success!!</strong> Contact request sent successfully");

                            setTimeout(function(){
                                $('#contact').find('.success-msg').hide();
                            }, 3000);
                        } else{
                            $('#contact').find('.error-msg').show();
                            $('#contact').find('.error-msg').text(result.message);

                            setTimeout(function(){
                                $('#contact').find('.error-msg').hide();
                            }, 3000);
                        }
                    },
                    error: function(errors){
                        console.log(errors);
                        form.find('.sending-gif').hide();

                        $('#contact').find('.error-msg').show();
                        $('#contact').find('.error-msg').text("Oops!! Something went wrong.");

                        setTimeout(function(){
                            $('#contact').find('.error-msg').hide();
                        }, 3000);
                    }
                });
            }
        });
    </script>
@endpush
