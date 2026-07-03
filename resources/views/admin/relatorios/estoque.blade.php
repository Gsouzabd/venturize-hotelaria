@extends('layouts.admin.master')

@section('title', 'Relatório de estoque')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection
@section('content')

@php
    $unidades = \App\Models\Produto::UNIDADES;
@endphp

<div class="container">
    <x-admin.ajuda titulo="Como funciona o relatório de estoque?">
        <ul class="mb-0 pl-3">
            <li><strong>De onde vem o saldo:</strong> cada linha mostra a quantidade atual de um produto em um sub-local (ex.: Cozinha › Dispensa). O saldo muda apenas através das <a href="{{ route('admin.movimentacoes-estoque.index') }}">Movimentações de Estoque</a> (entrada, saída ou transferência).</li>
            <li><strong>Filtro por local:</strong> escolher um local pai como "Cozinha (todos)" soma todos os sub-locais dele (Dispensa + Freezer + Geladeira). Para ver só um, escolha o sub-local (ex.: "Cozinha › Dispensa").</li>
            <li><strong>Exportar:</strong> os botões Excel e PDF geram o relatório exatamente com o filtro aplicado na tela.</li>
            <li><strong>Estoque mín./máx.:</strong> são os limites cadastrados no produto — use-os para saber o que precisa comprar.</li>
        </ul>
    </x-admin.ajuda>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.relatorios.estoque') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Produto (nome ou código)</label>
                        <input type="text" name="produto" class="form-control"
                               value="{{ $filters['produto'] ?? '' }}"
                               placeholder="Ex.: Arroz ou 303">
                    </div>
                    <div class="col-md-3">
                        <label>Local de estoque</label>
                        <select name="local_estoque_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($locaisEstoque as $local)
                                @if($local->children->isNotEmpty())
                                    <option value="{{ $local->id }}"
                                        {{ (string)($filters['local_estoque_id'] ?? '') === (string)$local->id ? 'selected' : '' }}>
                                        {{ $local->nome }} (todos)
                                    </option>
                                    @foreach($local->children->sortBy('nome') as $filho)
                                        <option value="{{ $filho->id }}"
                                            {{ (string)($filters['local_estoque_id'] ?? '') === (string)$filho->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;{{ $local->nome }} › {{ $filho->nome }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $local->id }}"
                                        {{ (string)($filters['local_estoque_id'] ?? '') === (string)$local->id ? 'selected' : '' }}>
                                        {{ $local->nome }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Produtos</label>
                        <select name="somente_ativos" class="form-control">
                            <option value="1" {{ ($filters['somente_ativos'] ?? '1') === '1' ? 'selected' : '' }}>Somente ativos</option>
                            <option value="0" {{ ($filters['somente_ativos'] ?? '') === '0' ? 'selected' : '' }}>Todos</option>
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
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Posição de estoque</h5>
            <div class="btn-group">
                <a href="{{ route('admin.relatorios.estoque.exportar', $filters) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.relatorios.estoque.exportar-pdf', $filters) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Cód. interno</th>
                            <th>Categoria</th>
                            <th>Local</th>
                            <th>Quantidade</th>
                            <th>Unidade</th>
                            <th>Estoque mín.</th>
                            <th>Estoque máx.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estoques as $row)
                            @php $p = $row->produto; @endphp
                            @if($p)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $p->descricao }}</td>
                                    <td>{{ $p->codigo_interno ?? '—' }}</td>
                                    <td>{{ $p->categoria->nome ?? '—' }}</td>
                                    <td>{{ $row->localEstoque ? trim(($row->localEstoque->parent->nome ?? '') . ' › ' . $row->localEstoque->nome, ' ›') : '—' }}</td>
                                    <td>{{ $row->quantidade }}</td>
                                    <td>{{ $unidades[$p->unidade] ?? $p->unidade }}</td>
                                    <td>{{ $p->estoque_minimo ?? '—' }}</td>
                                    <td>{{ $p->estoque_maximo ?? '—' }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Nenhum registro encontrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(trim($filters['produto'] ?? '') !== '' && $estoques->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right">Total em todos os locais:</td>
                                <td>{{ $estoques->sum('quantidade') }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.home') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@endsection
