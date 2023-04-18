<header class="main-header">
    <a href="{{route('dashboard.home')}}" class="logo">

    <span class="logo-mini"><b>G</b>PT</span>
    <span class="logo-lg"><b>GOPOTU</b> APP</span>
    </a>

    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>

        @if (Myhelper::hasRole('branch'))
            <a href="{{route('dashboard.report.index', ['type' => 'branchwallet'])}}" class="btn btn-light text-uppercase btn-sm wallet-btn"><i class="fas fa-wallet"></i> Main Wallet: <b>&#8377;{{Auth::user()->branchwallet}}</b></a>
        @endif

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{Auth::user()->avatar}}" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{Auth::user()->name}}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{Auth::user()->avatar}}" class="img-circle" alt="User Image">

                            <p>
                            {{Auth::user()->name}} - {{Auth::user()->role->name}}
                            <small>Member since {{Auth::user()->created_at}}</small>
                            </p>
                        </li>

                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{route('dashboard.profile')}}" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{route('logout')}}" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
