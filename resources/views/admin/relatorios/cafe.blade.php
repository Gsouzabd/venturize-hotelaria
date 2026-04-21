@extends('layouts.admin.master')

@section('title', 'Listagem de café')
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
            <form method="GET" action="{{ route('admin.relatorios.cafe') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Data de referência</label>
                        <x-admin.datepicker name="data" :value="$filters['data'] ?? ''"/>
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
            <h5 class="mb-0">Hóspedes (titular e acompanhantes) — presentes no café (9h) do dia</h5>
            <div class="btn-group">
                <a href="{{ route('admin.relatorios.cafe.exportar', $filters) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.relatorios.cafe.exportar-pdf', $filters) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Check-in às 15h: no café da manhã do dia <strong>D</strong> entram só reservas <strong>HOSPEDADO</strong> com
                <strong>data de check-in anterior a D</strong> e <strong>data de check-out em D ou depois</strong>
                (ou seja, já pernoitaram antes das 9h e ainda estão ou saem nesse dia).
            </p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Quarto</th>
                            <th>Tipo</th>
                            <th>Nome</th>
                            <th>CPF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($linhas as $linha)
                            <tr>
                                <td>{{ $linha['quarto'] }}</td>
                                <td>{{ $linha['tipo'] }}</td>
                                <td>{{ $linha['nome'] }}</td>
                                <td>{{ $linha['cpf'] !== '' ? $linha['cpf'] : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum hóspede encontrado para esta data</td>
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
