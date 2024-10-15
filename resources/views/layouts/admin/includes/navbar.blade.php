<nav class="layout-navbar navbar navbar-expand-lg align-items-lg-center bg-white container-p-x" id="layout-navbar">

    <!-- Brand -->
    <a href="{{ route('admin.home') }}" class="navbar-brand app-brand demo d-lg-none py-0 mr-4">
            <img src="{{ asset('assets/admin/images/aldeiadoscamaras.png') }}" style="width: 40%"/></a>
            <span></span>

        </a>
    {{-- @if(!config('app.horizontal_sidenav'))
        <!-- Sidenav toggle -->
        <div class="layout-sidenav-toggle navbar-nav d-lg-none align-items-lg-center mr-auto">
            <a class="nav-item nav-link px-0 mr-lg-4" href="javascript: void(0);">
                <i class="ion ion-md-menu text-large align-middle"></i>
            </a>
        </div>
    @endif --}}

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#layout-navbar-collapse">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse collapse" id="layout-navbar-collapse">
        <!-- Divider -->
        <hr class="d-lg-none w-100 my-2">
        <div class="navbar-nav align-items-lg-center ml-auto" id="menu-topo">
            <small style="margin-right: 5%">Sessão:</small>
            <!-- Menu Hotelaria -->
            <li class="nav-item dropdown {{ !request()->is('*bar*') ? 'show' : '' }}">
                <a class="nav-link "  href="{{ route('admin.home') }}"   role="tab" aria-controls="hotelaria" aria-selected="{{ !request()->is('/bar*') ? 'true' : 'false' }}">
                    <h5 class="d-inline">Hotelaria</h5>
                </a>
            </li>
        
            <!-- Menu Bar -->
            <li class="nav-item dropdown {{ request()->is('admin/bar*') ? 'show' : '' }}">
                <a class="nav-link {{ request()->is('admin/bar*') ? 'active' : '' }}" href="{{ route('admin.bar.home') }}" id="navbarBar" role="button" aria-haspopup="true" aria-expanded="{{ request()->is('admin/bar*') ? 'true' : 'false' }}">
                    <h5 class="d-inline">Bar</h5>
                </a>
            </li>
        </div>

        <div class="navbar-nav align-items-lg-center ml-auto">
            <div class="demo-navbar-user nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <span class="d-inline-flex flex-lg-row-reverse align-items-center align-middle">
                        <img src="{{ asset('assets/admin/images/user.png') }}" class="d-block ui-w-30 rounded-circle">
                        <span class="px-1 mr-lg-2 ml-2 ml-lg-0">Olá, {{ auth()->user()->nome }}!</span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ route('admin.logout') }}" class="dropdown-item">
                        <i class="ion ion-ios-log-out text-danger"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
