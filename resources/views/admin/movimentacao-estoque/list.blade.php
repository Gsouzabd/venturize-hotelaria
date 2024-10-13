@extends('layouts.admin.master')

@section('title', 'Movimentações de Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.movimentacoes-estoque.create" label="Inserir Entrada/Saída"/>
        <x-admin.create-btn route="admin.movimentacoes-estoque.transf" label="Realizar Transferência"/>    </x-admin.page-header>
@endsection

@section('content')
    <ul class="nav nav-tabs" id="movimentacoesTab" role="tablist">
        @foreach ($locaisEstoque as $index => $local)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $local->id }}" data-toggle="tab" href="#content-{{ $local->id }}" role="tab" aria-controls="content-{{ $local->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ $local->nome }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="movimentacoesTabContent">
        @foreach ($locaisEstoque as $index => $local)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $local->id }}" role="tabpanel" aria-labelledby="tab-{{ $local->id }}">
                <x-admin.grid>
                    <table class="table table-striped table-bordered table-hover card-table">
                        <thead>
                            <tr>
                                <th>ID Movimentação</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Tipo</th>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $movimentacoes = $local->movimentacoesOrigem->merge($local->movimentacoesDestino);
                            @endphp
                            @forelse ($movimentacoes as $movimentacao)
                                <tr>
                                    <td>{{ $movimentacao->id }}</td>
                                    <td>{{ $movimentacao->produto->descricao }}</td>
                                    <td>{{ $movimentacao->quantidade }}</td>
                                    <td>{{ ucfirst($movimentacao->tipo) }}</td>
                                    <td>{{ $movimentacao->usuario->name }}</td>
                                    <td>{{ Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <!-- Add action buttons here -->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nenhuma movimentação encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.grid>
            </div>
        @endforeach
    </div>
@endsection
