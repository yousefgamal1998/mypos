<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand -->
    <a href="{{ url('/dashboard') }}" class="brand-link text-center">
        <span class="brand-text font-weight-light">AdminLTE</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- User panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="{{ asset('adminlte/dist/img/yousef.jpg') }}"
                     class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block font-weight-bold" style="color:#fff;">Yousef Gamal</a>
                <span class="user-status">
                    <i class="fas fa-circle"></i> Online
                </span>
            </div>
        </div>

        <!-- Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="{{ route('dashboard.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-th"></i>

                        <p>{{ __('site.dashboard') }}</p>

                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard.users.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.users.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-users"></i>

                        <p>{{ __('site.users') }}</p>

                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard.customers.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.customers.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-user-friends"></i>

                        <p>{{ __('site.customers') }}</p>

                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard.orders.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.orders.*') || request()->routeIs('dashboard.customers.orders.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shopping-cart"></i>

                        <p>{{ __('site.orders') }}</p>

                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard.products.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.products.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-box"></i>

                        <p>{{ __('site.products') }}</p>

                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard.categories.index') }}"
                       class="nav-link {{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-th-large"></i>

                        <p>{{ __('site.categories') }}</p>

                    </a>
                </li>
            </ul>
        </nav>

    </div>
</aside>