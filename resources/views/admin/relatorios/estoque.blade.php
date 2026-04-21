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
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.relatorios.estoque') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Local de estoque</label>
                        <select name="local_estoque_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($locaisEstoque as $local)
                                <option value="{{ $local->id }}"
                                    {{ (string)($filters['local_estoque_id'] ?? '') === (string)$local->id ? 'selected' : '' }}>
                                    {{ $local->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Produtos</label>
                        <select name="somente_ativos" class="form-control">
                            <option value="1" {{ ($filters['somente_ativos'] ?? '1') === '1' ? 'selected' : '' }}>Somente ativos</option>
                            <option value="0" {{ ($filters['somente_ativos'] ?? '') === '0' ? 'selected' : '' }}>Todos</option>
                        </select>
                    </div>
                    <div class="col-md-4">
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
                                    <td>{{ $row->localEstoque->nome ?? '—' }}</td>
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
