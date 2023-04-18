<!--Main Menu/ Mobile Menu Section-->
<section class="menu-section-area">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top d-none d-sm-none d-md-block d-lg-block d-xl-block" id="mainNav">
        <div class="container">
            <a class="navbar-brand js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#home"><img src="{{ asset('website/img/logo-final.png') }}" alt="RATH Logo" class="img-fluid"></a>
            <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-capitalize ml-auto">
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#about">About</a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#features">Features</a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#reviews">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger theme-button" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#download">Download</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navigation -->

    <!-- Mobile Menu Start -->
    <nav class="mobile_menu hidden d-none">
        <a href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#page-top" class="js-scroll-trigger"><img class="mobile-logo img-fluid" src="{{ asset('website/img/logo-final.png') }}" alt="Arambagh Plaza Logo"></a>
        <ul class="nav navbar-nav navbar-right menu">
            <li class="nav-item">
                <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#home">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#about">About</a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#features">Features</a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link js-scroll-trigger" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#reviews">Reviews</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#contact">Contact</a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-scroll-trigger theme-button" href="{{ Request::route()->getName() == 'index' ? '' : route('index') }}#download">Download</a>
            </li>
        </ul>
    </nav>
    <!-- Mobile Menu End -->
</section>
<!--Main Menu/ Mobile Menu Section-->
