@extends('layouts.admin.master')

@section('title', 'Planos de Day Use')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Planos de Day Use</h5>
            <a href="{{ route('admin.day-use-precos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Plano
            </a>
        </div>
        <div class="card-body">
            @if($planos->isEmpty())
                <p>Nenhum plano de Day Use cadastrado.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Período</th>
                                <th>Plano Padrão</th>
                                <th>Seg</th>
                                <th>Ter</th>
                                <th>Qua</th>
                                <th>Qui</th>
                                <th>Sex</th>
                                <th>Sáb</th>
                                <th>Dom</th>
                                <th>Café</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($planos as $plano)
                                <tr>
                                    <td>{{ $plano->id }}</td>
                                    <td>
                                        @if($plano->data_inicio || $plano->data_fim)
                                            {{ optional($plano->data_inicio)->format('d/m/Y') ?? '—' }}
                                            até
                                            {{ optional($plano->data_fim)->format('d/m/Y') ?? '—' }}
                                        @else
                                            <span class="badge badge-info">Sem período (default)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plano->is_default)
                                            <span class="badge badge-success">Sim</span>
                                        @else
                                            <span class="badge badge-secondary">Não</span>
                                        @endif
                                    </td>
                                    <td>{{ $plano->preco_segunda ? 'R$ '.number_format($plano->preco_segunda, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_terca ? 'R$ '.number_format($plano->preco_terca, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_quarta ? 'R$ '.number_format($plano->preco_quarta, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_quinta ? 'R$ '.number_format($plano->preco_quinta, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_sexta ? 'R$ '.number_format($plano->preco_sexta, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_sabado ? 'R$ '.number_format($plano->preco_sabado, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_domingo ? 'R$ '.number_format($plano->preco_domingo, 2, ',', '.') : '—' }}</td>
                                    <td>{{ $plano->preco_cafe ? 'R$ '.number_format($plano->preco_cafe, 2, ',', '.') : '—' }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.day-use-precos.edit', $plano->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.day-use-precos.destroy', $plano->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Deseja realmente excluir este plano?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

