@extends('layouts.admin.master')

@section('title', 'Relatórios de Despesas')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.despesas.relatorios') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Data Inicial</label>
                        <input type="text" name="data_inicial" class="form-control date-mask" 
                               value="{{ $filters['data_inicial'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label>Data Final</label>
                        <input type="text" name="data_final" class="form-control date-mask" 
                               value="{{ $filters['data_final'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" 
                                        {{ ($filters['categoria_id'] ?? '') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Resumo Consolidado</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total Geral:</strong> R$ {{ number_format($totalGeral, 2, ',', '.') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <strong>Total de Despesas:</strong> {{ $despesas->count() }}
                    </div>
                </div>
            </div>
            
            <h6>Consolidado por Categoria</h6>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Valor Total</th>
                        <th>Percentual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consolidado as $item)
                        <tr>
                            <td>{{ $item['nome'] }}</td>
                            <td>{{ $item['quantidade'] }}</td>
                            <td>R$ {{ number_format($item['valor'], 2, ',', '.') }}</td>
                            <td>
                                @if($totalGeral > 0)
                                    {{ number_format(($item['valor'] / $totalGeral) * 100, 2, ',', '.') }}%
                                @else
                                    0%
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Nenhum dado encontrado para o período selecionado</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th>{{ collect($consolidado)->sum('quantidade') }}</th>
                        <th>R$ {{ number_format($totalGeral, 2, ',', '.') }}</th>
                        <th>100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Detalhamento de Despesas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Número da Nota</th>
                            <th>Data</th>
                            <th>Valor Total</th>
                            <th>Rateios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($despesas as $despesa)
                            <tr>
                                <td>{{ $despesa->id }}</td>
                                <td>{{ $despesa->numero_nota_fiscal }}</td>
                                <td>{{ $despesa->data->format('d/m/Y') }}</td>
                                <td>R$ {{ number_format($despesa->valor_total, 2, ',', '.') }}</td>
                                <td>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhuma despesa encontrada para o período selecionado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="{{ route('admin.despesas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.date-mask').mask('00/00/0000');
    });
</script>
@endpush

