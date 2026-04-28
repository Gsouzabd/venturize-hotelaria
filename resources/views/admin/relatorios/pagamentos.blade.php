@extends('layouts.admin.master')

@section('title', 'Relatório de Pagamentos')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
@php use App\Models\Pagamento; @endphp

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.relatorios.pagamentos') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo de Pagamento</label>
                        <select name="tipo_pagamento" class="form-control">
                            <option value="">Todos</option>
                            @foreach($tiposPagamento as $key => $dado)
                                <option value="{{ $key }}" {{ ($filters['tipo_pagamento'] ?? '') === $key ? 'selected' : '' }}>
                                    {{ $dado['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Tipo de Quarto</label>
                        <select name="tipo_quarto" class="form-control">
                            <option value="">Todos</option>
                            @foreach($tiposQuarto as $tipo)
                                <option value="{{ $tipo }}" {{ ($filters['tipo_quarto'] ?? '') === $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Nome do Hóspede</label>
                        <input type="text" name="nome" class="form-control" value="{{ $filters['nome'] ?? '' }}" placeholder="Buscar por nome...">
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>Check-in De</label>
                        <x-admin.datepicker name="data_checkin_inicial" :value="$filters['data_checkin_inicial'] ?? ''"/>
                    </div>
                    <div class="col-md-3">
                        <label>Check-in Até</label>
                        <x-admin.datepicker name="data_checkin_final" :value="$filters['data_checkin_final'] ?? ''"/>
                    </div>
                    <div class="col-md-3">
                        <label>Check-out De</label>
                        <x-admin.datepicker name="data_checkout_inicial" :value="$filters['data_checkout_inicial'] ?? ''"/>
                    </div>
                    <div class="col-md-3">
                        <label>Check-out Até</label>
                        <x-admin.datepicker name="data_checkout_final" :value="$filters['data_checkout_final'] ?? ''"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Pagamentos de reservas ({{ $reservas->count() }} registros)</h5>
            <div class="btn-group">
                <a href="{{ route('admin.relatorios.pagamentos.exportar', array_filter($filters)) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.relatorios.pagamentos.exportar-pdf', array_filter($filters)) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID Reserva</th>
                            <th>Hóspede</th>
                            <th>Quarto</th>
                            <th>Tipo Quarto</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Tipo Pagamento</th>
                            <th>Valor Pago</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            @php
                                $cliente = $reserva->clienteResponsavel ?? $reserva->clienteSolicitante;
                                $pagamento = $reserva->pagamentos->first();
                                $valorPago = $reserva->pagamentos->sum('valor_pago');

                                $metodosLabel = '';
                                if ($pagamento && $pagamento->valores_recebidos) {
                                    $valores = is_array($pagamento->valores_recebidos)
                                        ? $pagamento->valores_recebidos
                                        : json_decode($pagamento->valores_recebidos, true) ?? [];
                                    $labels = [];
                                    foreach (array_keys($valores) as $chave) {
                                        $key = explode('-', $chave)[0];
                                        foreach (Pagamento::METODOS_PAGAMENTO as $catKey => $cat) {
                                            if ($key === $catKey) { $labels[] = $cat['label']; break; }
                                            foreach ($cat['submetodos'] as $subKey => $subLabel) {
                                                if ($key === $subKey) { $labels[] = $subLabel; break 2; }
                                            }
                                        }
                                    }
                                    $metodosLabel = implode(', ', array_unique($labels));
                                }
                            @endphp
                            <tr>
                                <td>{{ $reserva->id }}</td>
                                <td>{{ $cliente->nome ?? '—' }}</td>
                                <td>{{ $reserva->quarto->numero ?? '—' }}</td>
                                <td>{{ $reserva->quarto->classificacao ?? '—' }}</td>
                                <td>{{ $reserva->data_checkin ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') : '—' }}</td>
                                <td>{{ $reserva->data_checkout ? \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '—' }}</td>
                                <td>{{ $metodosLabel ?: '—' }}</td>
                                <td class="text-right">R$ {{ number_format($valorPago, 2, ',', '.') }}</td>
                                <td>
                                    @if($pagamento)
                                        @php $status = $pagamento->status_pagamento ?? ''; @endphp
                                        <span class="badge badge-{{ $status === 'PAGO' ? 'success' : ($status === 'PARCIAL' ? 'warning' : 'secondary') }}">
                                            {{ Pagamento::STATUS_PAGAMENTO[$status] ?? $status }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Nenhum registro encontrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($reservas->isNotEmpty())
                        @php $totalGeral = $reservas->sum(fn ($r) => $r->pagamentos->sum('valor_pago')); @endphp
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="7" class="text-right">Total:</td>
                                <td class="text-right">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                                <td></td>
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
