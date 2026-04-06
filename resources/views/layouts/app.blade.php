@php
    $locale = app()->getLocale();
    $isRtl = str_starts_with((string) $locale, 'ar');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MyPOS | @yield('page-title', 'Dashboard')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

    @if ($isRtl)
        <!-- RTL CSS -->
        <link rel="stylesheet" href="{{ asset('dashboard/css/font-awesome-rtl.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dashboard/css/AdminLTE-rtl.min.css') }}">
        <link href="https://fonts.googleapis.com/css?family=Cairo:400,700" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('dashboard/css/bootstrap-rtl.min.css') }}">
        <link rel="stylesheet" href="{{ asset('dashboard/css/rtl.css') }}">
    <style>
            body, h1, h2, h3, h4, h5, h6 {
                font-family: 'Cairo', sans-serif !important;
            }
        </style>
    @endif

    @stack('head')

    <style>
        /* ===================================================
           SHARED THEME STYLES (both LTR and RTL)
           =================================================== */

        /* Navbar: blue background */
        .main-header.navbar {
            background-color: #3c8dbc !important;
            border-bottom: none !important;
        }

        /* Sidebar brand: same blue as navbar */
        .brand-link {
            background-color: #3c8dbc !important;
            border-bottom: 1px solid rgba(0, 0, 0, .15) !important;
        }

        .brand-link .brand-text {
            color: #fff !important;
            font-size: 1.5rem;
        }

        /* Sidebar body background */
        .main-sidebar,
        .main-sidebar .sidebar {
            background-color: #222d32 !important;
        }

        /* User panel */
        .user-panel .info a {
            color: #fff;
        }

        .user-panel .user-status {
            color: #3c763d;
            font-size: 12px;
        }

        .user-panel .user-status i {
            color: #3c763d;
            font-size: 8px;
        }

        /* Sidebar nav */
        .sidebar .nav-link {
            color: #b8c7ce;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: transparent !important;
            color: #fff !important;
        }

        .custom-error-box {
            background: #e74c3c;
            color: #fff;
            padding: 20px 25px;
            border-radius: 6px;
            margin-bottom: 20px;
            direction: rtl;
            text-align: right;
        }

        .custom-error-box ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .custom-error-box li {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .custom-error-box li span {
            margin-right: 5px;
        }

        @stack('styles')
    </style>
</head>

<body class="sidebar-mini layout-fixed layout-navbar-fixed {{ $isRtl ? 'sidebar-on-right' : '' }}">
    <div class="wrapper">

        {{-- Preloader --}}
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ asset('adminlte/dist/img/AdminLTELogo.png') }}" alt="MyPOS"
                height="60" width="60">
        </div>

        {{-- Navbar --}}
        @include('partials.navbar')

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Content Wrapper --}}
        <div class="content-wrapper">

            {{-- Content Header --}}
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-left" style="margin-bottom:0;">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard.index') }}"><i class="fas fa-home"></i> {{ __('site.dashboard') }}</a>
                                </li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                        <div class="col-sm-6 text-right">
                            <small class="text-muted">@yield('page-subtitle')</small>
                            <span style="font-size:1.25rem;"> @yield('page-title', 'Blank page')</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Main Content --}}
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>

        </div>

        {{-- Footer --}}
        <footer class="main-footer">
            <div class="float-left d-none d-sm-block">
                <b>Version</b> 2.4.0
            </div>
            <div class="float-right">
                <strong>Copyright &copy; 2014-2016
                    <a href="https://adminlte.io" target="_blank">Almsaeed Studio</a>.</strong>
                All rights reserved
            </div>
        </footer>

    </div>

    <!-- jQuery -->
    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE -->
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

    @stack('scripts')
</body>

</html>

