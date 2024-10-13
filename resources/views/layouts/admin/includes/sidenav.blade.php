

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
            <ul class="sidenav-submenu">
                <li class="sidenav-item{{ is_active_path('admin/quartos/opcoes-extras') ? ' active' : '' }}">
                    <a href="{{ route('admin.quartos-opcoes-extras.index') }}" class="sidenav-link">
                        <i class="sidenav-icon fas fa-plus"></i>
                        <div>Opções Extras</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/produtos') ? ' active' : '' }}">
            <a href="{{ route('admin.produtos.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-box"></i>
                <div>Produtos</div>
            </a>
            <ul class="sidenav-submenu">
                <!-- Existing menu items -->
            
                <!-- Local Estoque Menu Item -->
                {{-- <li class="sidenav-item">
                    <a href="{{ route('admin.local_estoque.index') }}" class="sidenav-link">
                        <i class="fas fa-warehouse"></i>
                        <span>Local Estoque</span>
                    </a>
                </li>
             --}}
                <!-- Estoque Menu Item -->
                <li class="sidenav-item">
                    <a href="{{ route('admin.estoque.index') }}" class="sidenav-link">
                        <i class="sidenav-icon  fas fa-boxes"></i>
                        <span>Estoque</span>
                    </a>
                </li>
                <li class="sidenav-item">
                    <a href="{{ route('admin.movimentacoes-estoque.index') }}" class="sidenav-link">
                        <i class="sidenav-icon fas fa-exchange-alt"></i>
                        <span>Movimentações</span>
                    </a>
                </li>
            
            </ul>

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
