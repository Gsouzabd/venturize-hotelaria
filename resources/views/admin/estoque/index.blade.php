@extends('layouts.admin.master')

@section('title', 'Movimentações de Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.estoque.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.grid :pagination="$movimentacoes">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Quantidade</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th>Usuário</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movimentacoes as $movimentacao)
                    <tr>
                        <td>{{ $movimentacao->id }}</td>
                        <td>{{ $movimentacao->produto->descricao }}</td>
                        <td>{{ $movimentacao->localEstoqueOrigem->nome ?? 'N/A' }}</td>
                        <td>{{ $movimentacao->localEstoqueDestino->nome ?? 'N/A' }}</td>
                        <td>{{ $movimentacao->quantidade }}</td>
                        <td>{{ ucfirst($movimentacao->tipo) }}</td>
                        <td>{{ \Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d-m-Y H:i') }}</td>
                        <td>{{ $movimentacao->usuario->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Nenhuma movimentação encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection