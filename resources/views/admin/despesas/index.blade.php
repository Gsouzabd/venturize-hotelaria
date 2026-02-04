@extends('layouts.admin.master')

@section('title', 'Despesas')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.despesas.create"/>
        <a href="{{ route('admin.despesas.relatorios') }}" class="btn btn-info">
            <i class="fas fa-chart-bar"></i> Relatórios
        </a>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.despesas.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Número da Nota Fiscal"/>
            <x-admin.text name="numero_nota_fiscal" :value="$filters['numero_nota_fiscal'] ?? ''"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Data Inicial"/>
            <x-admin.text name="data_inicial" :value="$filters['data_inicial'] ?? ''" class="date-mask"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Data Final"/>
            <x-admin.text name="data_final" :value="$filters['data_final'] ?? ''" class="date-mask"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Categoria"/>
            <x-admin.select name="categoria_id">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ ($filters['categoria_id'] ?? '') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nome }}
                    </option>
                @endforeach
            </x-admin.select>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Fornecedor"/>
            <select name="fornecedor_id" class="custom-select">
                <option value="">Todos</option>
                @foreach($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}" {{ ($filters['fornecedor_id'] ?? '') == $fornecedor->id ? 'selected' : '' }}>
                        {{ $fornecedor->nome }}
                    </option>
                @endforeach
            </select>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$despesas">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Número da Nota</th>
                <th>Descrição</th>
                <th>Fornecedor</th>
                <th>Data</th>
                <th>Valor Total</th>
                <th>Rateios</th>
                <th>Arquivo</th>
                <th>Cadastrado por</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($despesas as $despesa)
                <tr>
                    <td>{{ $despesa->id }}</td>
                    <td>{{ $despesa->numero_nota_fiscal }}</td>
                    <td>{{ strlen($despesa->descricao ?? '-') > 50 ? substr($despesa->descricao ?? '-', 0, 50) . '...' : ($despesa->descricao ?? '-') }}</td>
                    <td>{{ $despesa->fornecedor->nome ?? '-' }}</td>
                    <td>{{ $despesa->data->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($despesa->valor_total, 2, ',', '.') }}</td>
                    <td>
                        @if($despesa->despesaCategorias->count() > 0)
                            <ul class="list-unstyled mb-0">
                                @foreach($despesa->despesaCategorias as $rateio)
                                    <li>
                                        <small>
                                            {{ $rateio->categoriaDespesa ? $rateio->categoriaDespesa->nome : 'Sem categoria' }}: 
                                            R$ {{ number_format($rateio->valor, 2, ',', '.') }}
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">Sem rateio</span>
                        @endif
                    </td>
                    <td>
                        @if($despesa->arquivo_nota)
                            <a href="{{ asset('storage/' . $despesa->arquivo_nota) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-file"></i> Ver
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $despesa->usuario->nome ?? '-' }}</td>
                    <td class="cell-nowrap">
                        <a href="{{ route('admin.despesas.show', $despesa->id) }}" class="btn btn-sm btn-info" title="Visualizar">
                            <i class="fas fa-eye"></i>
                        </a>
                        <x-admin.edit-btn route="admin.despesas.edit" :route-params="['id' => $despesa->id]"/>
                        <x-admin.delete-btn route="admin.despesas.destroy" :route-params="['id' => $despesa->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.date-mask').mask('00/00/0000');
    });
</script>
@endpush

