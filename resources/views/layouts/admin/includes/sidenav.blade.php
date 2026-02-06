

<div id="layout-sidenav"
     class="{{ !config('app.horizontal_sidenav') ? 'layout-sidenav sidenav sidenav-vertical bg-dark' : 'layout-sidenav-horizontal sidenav sidenav-horizontal flex-grow-0 bg-dark container-p-x' }}">

    <ul class="sidenav-inner{{ !config('app.horizontal_sidenav') ? ' py-1' : '' }}">
        @if (!request()->is('*bar*'))

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
                <ul class="sidenav-submenu">
                    <li class="sidenav-item{{ is_active_path('admin/reservas/day-use') ? ' active' : '' }}">
                        <a href="{{ route('admin.reservas.day-use') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-sun"></i>
                            <div>Day Use</div>
                        </a>
                    </li>
                </ul>
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
                    <li class="sidenav-item{{ is_active_path('admin/day-use-precos') ? ' active' : '' }}">
                        <a href="{{ route('admin.day-use-precos.index') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-sun"></i>
                            <div>Planos Day Use</div>
                        </a>
                    </li>
                </ul>
            </li>
                <!-- Produtos Menu -->
            <li class="sidenav-item{{ is_active_path('admin/produtos') ? ' active' : '' }}">
                <a href="{{ route('admin.produtos.index') }}" class="sidenav-link">
                    <i class="sidenav-icon fas fa-box"></i>
                    <div>Produtos</div>
                </a>
                <ul class="sidenav-submenu">
                    <!-- Categorias Menu Item -->
                    <li class="sidenav-item">
                        <a href="{{ route('admin.categorias.index') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-list"></i>
                            <span>Categorias</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Estoque Menu -->
            <li class="sidenav-item{{ is_active_path('admin/estoque') ? ' active' : '' }}">
                <a href="{{ route('admin.estoque.index') }}" class="sidenav-link">
                    <i class="sidenav-icon fas fa-boxes"></i>
                    <div>Estoque</div>
                </a>
                <ul class="sidenav-submenu">
                    <!-- Local Estoque Menu Item -->
                    <li class="sidenav-item">
                        <a href="{{ route('admin.locais-estoque.index') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-warehouse"></i>
                            <span>Localizações</span>
                        </a>
                    </li>
                    <!-- Movimentações Menu Item -->
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
            <li class="sidenav-item{{ is_active_path('admin/impressoras') ? ' active' : '' }}">
                <a href="{{ route('admin.impressoras.index') }}" class="sidenav-link">
                    <i class="sidenav-icon fas fa-print"></i>
                    <div>Impressoras</div>
                </a>
            </li>
            
            <!-- Despesas Menu -->
            <li class="sidenav-item{{ is_active_path('admin/despesas') ? ' active' : '' }}">
                <a href="{{ route('admin.despesas.index') }}" class="sidenav-link">
                    <i class="sidenav-icon fas fa-receipt"></i>
                    <div>Despesas</div>
                </a>
                <ul class="sidenav-submenu">
                    <!-- Categorias de Despesas Menu Item -->
                    <li class="sidenav-item{{ is_active_path('admin/categorias-despesas') ? ' active' : '' }}">
                        <a href="{{ route('admin.categorias-despesas.index') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-tags"></i>
                            <span>Categorias</span>
                        </a>
                    </li>
                    <!-- Fornecedores Menu Item -->
                    <li class="sidenav-item{{ is_active_path('admin/fornecedores') ? ' active' : '' }}">
                        <a href="{{ route('admin.fornecedores.index') }}" class="sidenav-link">
                            <i class="sidenav-icon fas fa-truck"></i>
                            <span>Fornecedores</span>
                        </a>
                    </li>
                </ul>
            </li>
        @else  

        <li class="sidenav-item{{ is_active_path('admin') ? ' active' : '' }}">
            <a href="{{ route('admin.bar.home') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-tachometer-alt"></i>
                <div>Dashboard</div>
            </a>
        </li>
            
        <li class="sidenav-item{{ is_active_path('admin/mesas') ? ' active' : '' }}">
            <a href="{{ route('admin.bar.mesas.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-utensils"></i>
                <div>Mesas</div>
            </a>
        </li>
        <li class="sidenav-item{{ is_active_path('admin/pedidos') ? ' active' : '' }}">
            <a href="{{ route('admin.bar.pedidos.index') }}" class="sidenav-link">
                <i class="sidenav-icon fas fa-clipboard-list"></i>
                <div>Pedidos</div>
            </a>
        </li>
        @endif

    </ul>
</div>
