

<div id="layout-sidenav"
     class="{{ !config('app.horizontal_sidenav') ? 'layout-sidenav sidenav sidenav-vertical bg-dark' : 'layout-sidenav-horizontal sidenav sidenav-horizontal flex-grow-0 bg-dark container-p-x' }}">

    <ul class="sidenav-inner{{ !config('app.horizontal_sidenav') ? ' py-1' : '' }}">

        <li class="sidenav-item{{ is_active_path('admin/usuarios') ? ' active' : '' }}">
            <a href="{{ route('admin.usuarios.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-users"></i>
                <div>Usuários</div>
            </a>
        </li>



    </ul>
</div>
