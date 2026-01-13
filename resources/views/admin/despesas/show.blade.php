@extends('layouts.admin.master')

@section('title', 'Visualizar Despesa')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection
@section('content')

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Despesa #{{ $despesa->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número da Nota Fiscal:</strong> {{ $despesa->numero_nota_fiscal }}</p>
                    <p><strong>Descrição:</strong> {{ $despesa->descricao }}</p>
                    <p><strong>Fornecedor:</strong> {{ $despesa->fornecedor->nome ?? '-' }}</p>
                    <p><strong>Data:</strong> {{ $despesa->data->format('d/m/Y') }}</p>
                    <p><strong>Valor Total:</strong> R$ {{ number_format($despesa->valor_total, 2, ',', '.') }}</p>
                    <p><strong>Cadastrado por:</strong> {{ $despesa->usuario->nome ?? '-' }}</p>
                    <p><strong>Data de Cadastro:</strong> {{ $despesa->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <div class="col-md-6">
                    @if($despesa->arquivo_nota)
                        <p><strong>Arquivo:</strong></p>
                        <a href="{{ asset('storage/' . $despesa->arquivo_nota) }}" target="_blank" class="btn btn-info">
                            <i class="fas fa-file"></i> Visualizar Arquivo
                        </a>
                    @else
                        <p><strong>Arquivo:</strong> Não informado</p>
                    @endif
                </div>
            </div>
            
            @if($despesa->observacoes)
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Observações:</strong></p>
                        <p>{{ $despesa->observacoes }}</p>
                    </div>
                </div>
            @endif
            
            <div class="row mt-4">
                <div class="col-12">
                    <h6>Rateio de Despesas</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Valor</th>
                                <th>Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($despesa->despesaCategorias as $rateio)
                                <tr>
                                    <td>{{ $rateio->categoriaDespesa ? $rateio->categoriaDespesa->nome : 'Sem categoria' }}</td>
                                    <td>R$ {{ number_format($rateio->valor, 2, ',', '.') }}</td>
                                    <td>{{ $rateio->observacoes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Nenhum rateio cadastrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total Rateado</th>
                                <th>R$ {{ number_format($despesa->valor_total_rateado, 2, ',', '.') }}</th>
                                <th>
                                    @if($despesa->isRateioCompleto())
                                        <span class="badge bg-success">Completo</span>
                                    @else
                                        <span class="badge bg-warning">Pendente: R$ {{ number_format($despesa->valor_pendente_rateio, 2, ',', '.') }}</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <a href="{{ route('admin.despesas.edit', $despesa->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('admin.despesas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

