<nav class="main-header navbar navbar-expand navbar-dark">

    <!-- Left: User info + icons -->
    <ul class="navbar-nav align-items-center">
        @auth
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" href="#" role="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false" onclick="event.preventDefault();">
                    <span class="mr-2" style="font-weight:600;">{{ auth()->user()->full_name }}</span>
                    <img src="{{ auth()->user()->avatar_url }}" class="img-circle" alt=""
                        style="width:30px;height:30px;object-fit:cover;">
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <form action="{{ route('logout') }}" method="post" class="px-0 py-0 mb-0">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </li>
        @endauth
        <li class="nav-item dropdown"> 
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-flag"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-header"><i class="fas fa-globe mr-1"></i> {{ __('site.languages') }}</span>
                <div class="dropdown-divider"></div>
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <a class="dropdown-item {{ app()->getLocale() === $localeCode ? 'active' : '' }}"
                       href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                        {{ $properties['native'] }}
                    </a>
                @endforeach
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge">15</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="far fa-envelope"></i></a>
        </li>
    </ul>

    <!-- Right: Hamburger -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="#" role="button" data-widget="pushmenu" aria-label="Toggle sidebar"
                onclick="event.preventDefault();">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

</nav>
