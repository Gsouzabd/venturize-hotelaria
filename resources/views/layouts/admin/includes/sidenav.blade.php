

<div id="layout-sidenav"
     class="{{ !config('app.horizontal_sidenav') ? 'layout-sidenav sidenav sidenav-vertical bg-dark' : 'layout-sidenav-horizontal sidenav sidenav-horizontal flex-grow-0 bg-dark container-p-x' }}">

    <ul class="sidenav-inner{{ !config('app.horizontal_sidenav') ? ' py-1' : '' }}">
        <li class="sidenav-item{{ is_active_path('admin') ? ' active' : '' }}">
            <a href="{{ route('admin.home') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-tachometer-alt"></i>
                <div>Dashboard</div>
            </a>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/reservas/mapa') ? ' active' : '' }}">
            <a href="{{ route('admin.reservas.mapa') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-calendar-check"></i>
                <div>Mapa de Ocupação</div>
            </a>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/reservas') ? ' active' : '' }}">
            <a href="{{ route('admin.reservas.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-calendar-week"></i>
                <div>Reservas</div>
            </a>
        </li>

        <li class="sidenav-item{{ is_active_path('admin/quartos') ? ' active' : '' }}">
            <a href="{{ route('admin.quartos.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-bed"></i>
                <div>Quartos</div>
            </a>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/clientes') ? ' active' : '' }}">
            <a href="{{ route('admin.clientes.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-user-tie"></i>
                <div>Clientes</div>
            </a>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/usuarios') ? ' active' : '' }}">
            <a href="{{ route('admin.usuarios.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-users"></i>
                <div>Usuários</div>
            </a>
        </li>
        

        

        

        

    </ul>
</div>
