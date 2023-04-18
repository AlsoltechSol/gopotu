<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('pageheader', 'Home') | {{config('app.name', 'Laravel')}} - {{config('app.title', 'Another Laravel Website')}}</title>

    <!--=======================================
      All Css Style link
    ===========================================-->

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('website/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome for this template -->
    <link href="{{ asset('website/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Flat Icon for this template -->
    <link href="{{ asset('website/vendor/rath-flat-icon/flaticon.css') }}" rel="stylesheet" type="text/css">

    <!-- Custom fonts for this template -->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap" rel="stylesheet">

    <!-- Owl carousel 2 css -->
    <link rel="stylesheet" href="{{ asset('website/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('website/css/owl.theme.default.min.css') }}">

    <!-- Venobox Css-->
    <link rel="stylesheet" href="{{ asset('website/css/venobox.css') }}">

    <!-- Animate Css-->
    <link rel="stylesheet" href="{{ asset('website/css/animate.min.css') }}">

    <!-- Mean Menu/Mobile Menu Css-->
    <link rel="stylesheet" href="{{ asset('website/css/meanmenu.min.css') }}">

    <!-- Custom styles for this template -->
    <link href="{{ asset('website/css/style.css') }}" rel="stylesheet">

    {{-- <!-- FAVICONS -->
    <link rel="icon" href="{{ asset('website/img/favicon-16x16.png') }}" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ asset('website/img/favicon-16x16.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('website/img/favicon-16x16.png') }}">

    <link rel="apple-touch-icon-precomposed" type="image/x-icon" href="{{ asset('website/img/apple-icon-72x72.png') }}" sizes="72x72" />
    <link rel="apple-touch-icon-precomposed" type="image/x-icon" href="{{ asset('website/img/apple-icon-114x114.png') }}" sizes="114x114" />
    <link rel="apple-touch-icon-precomposed" type="image/x-icon" href="{{ asset('website/img/apple-icon-144x144.png') }}" sizes="144x144" />
    <link rel="apple-touch-icon-precomposed" type="image/x-icon" href="{{ asset('website/img/favicon-16x16.png') }}" /> --}}

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @stack('style')
</head>

<body id="page-top" class="home-1">

    @include('inc.website.header')

    @yield('content')

    @include('inc.website.footer')

    <!--Top Arrow area start-->
    <div class="top-arrow">
        <a href="#" id="scroll" style="display: none;"><span></span></a>
    </div>
    <!--Top Arrow area End-->

    <!--=======================================
    All Jquery Script link
    ===========================================-->

    <!-- Bootstrap core JavaScript -->
    <script src="{{ asset('website/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('website/vendor/jquery/popper.min.js') }}"></script>
    <script src="{{ asset('website/vendor/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- ==== Plugin JavaScript ==== -->

    <!-- jQuery owl carousel -->
    <script src="{{ asset('website/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('website/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <!--WOW JS Script-->
    <script src="{{ asset('website/js/wow.min.js') }}"></script>
    <!--WayPoints JS Script-->
    <script src="{{ asset('website/js/waypoints.min.js') }}"></script>
    <!--Counter Up JS Script-->
    <script src="{{ asset('website/js/jquery.counterup.min.js') }}"></script>
    <!--VenoBox Script-->
    <script src="{{ asset('website/js/venobox.min.js') }}"></script>
    <!--Mean Menu/Mobile Menu Script-->
    <script src="{{ asset('website/js/jquery.meanmenu.min.js') }}"></script>
    <!-- Custom scripts for this template -->
    <script src="{{ asset('website/js/custom.js') }}"></script>

    {{-- myPlugins JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js" integrity="sha512-YUkaLm+KJ5lQXDBdqBqk7EVhJAdxRnVdT2vtCzwPHSweCzyMgYV/tgGF4/dCyqtCC2eCphz0lRQgatGVdfR0ww==" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>

    @stack('script')
</body>

</html>
