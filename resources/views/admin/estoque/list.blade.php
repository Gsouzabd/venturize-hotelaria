@extends('layouts.admin.master')

@php
use Carbon\Carbon;
use App\Models\Produto;
@endphp

@section('title', 'Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        {{-- <x-admin.create-btn route="admin.estoque.create"/> --}}
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.ajuda titulo="Como funciona o estoque?">
        <ul class="mb-0 pl-3">
            <li><strong>O que aparece aqui:</strong> o saldo atual de cada produto por sub-local. As abas de cima são os locais pais (ex.: Cozinha soma Dispensa + Freezer + Geladeira); ao clicar em um, aparecem abaixo os sub-locais dele para filtrar um por um.</li>
            <li><strong>Como dar entrada:</strong> em <a href="{{ route('admin.movimentacoes-estoque.index') }}">Movimentações de Estoque</a> → "Inserir Entrada/Saída", escolha o produto, o sub-local e a quantidade. Compras e chegadas de mercadoria entram por lá.</li>
            <li><strong>Como dar saída:</strong> mesma tela, tipo "Saída" — use para consumo, perdas e quebras, com a justificativa.</li>
            <li><strong>Como mover entre locais:</strong> "Realizar Transferência" move a quantidade de um sub-local para outro (ex.: do Freezer para a Geladeira).</li>
            <li><strong>Produto novo:</strong> cadastre primeiro em Produtos e depois dê a primeira entrada no sub-local correto.</li>
        </ul>
    </x-admin.ajuda>

    @php
        // Local pai ativo: o próprio selecionado ou o pai do sub-local selecionado
        $filtroId = (int) request('local_estoque_id');
        $paiAtivo = $locaisEstoque->first(fn ($l) => $l->id === $filtroId || $l->children->contains('id', $filtroId));
    @endphp

    {{-- Nível 1: locais pais --}}
    <ul class="nav nav-tabs mb-0 flex-wrap" id="estoqueTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ !$filtroId ? 'active' : '' }}"
               href="{{ route('admin.estoque.index', request()->except(['local_estoque_id', 'page'])) }}">
               Todos
            </a>
        </li>
        @foreach ($locaisEstoque as $local)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $paiAtivo?->id === $local->id ? 'active' : '' }}"
                   href="{{ route('admin.estoque.index', array_merge(request()->except('page'), ['local_estoque_id' => $local->id])) }}">
                   {{ $local->nome }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Nível 2: sub-locais do pai selecionado --}}
    @if($paiAtivo && $paiAtivo->children->isNotEmpty())
        <ul class="nav nav-pills flex-wrap py-2 px-2 bg-white border-left border-right">
            <li class="nav-item">
                <a class="nav-link py-1 {{ $filtroId === $paiAtivo->id ? 'active' : '' }}"
                   href="{{ route('admin.estoque.index', array_merge(request()->except('page'), ['local_estoque_id' => $paiAtivo->id])) }}">
                   Todos de {{ $paiAtivo->nome }}
                </a>
            </li>
            @foreach($paiAtivo->children->sortBy('nome') as $filho)
                <li class="nav-item">
                    <a class="nav-link py-1 {{ $filtroId === $filho->id ? 'active' : '' }}"
                       href="{{ route('admin.estoque.index', array_merge(request()->except('page'), ['local_estoque_id' => $filho->id])) }}">
                       {{ $filho->nome }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <x-admin.grid :pagination="$estoques">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
                <tr>
                    <th>ID Estoque</th>
                    <th>ID Produto</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Unidade</th>
                    <th>Preço Custo</th>
                    <th>Preço Venda</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estoques as $estoque)
                    <tr>
                        <td>{{ $estoque->id }}</td>
                        <td>{{ $estoque->produto->id }}</td>
                        <td>{{ $estoque->produto->descricao }}</td>
                        <td>{{ $estoque->quantidade }}</td>
                        <td>{{ $estoque->produto->unidade }} - {{ \App\Models\Produto::UNIDADES[$estoque->produto->unidade] }}</td>
                        <td>R$ {{ number_format($estoque->produto->preco_custo ?? 0, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($estoque->produto->preco_venda ?? 0, 2, ',', '.') }}</td>
                        <td>{{ Carbon::parse($estoque->created_at)->format('d-m-Y') }}</td>
                        <td class="cell-nowrap">
                            <x-admin.edit-btn route="admin.estoque.edit" :route-params="['local_estoque_id' => $estoque->local_estoque_id, 'id' => $estoque->id]" :label="html_entity_decode('<i class=\'fas fa-edit\'></i>')"/>
                            <x-admin.delete-btn route="admin.estoque.destroy" :route-params="['id' => $estoque->id]" :label="html_entity_decode('<i class=\'fas fa-trash-alt\'></i>')"/>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Nenhum estoque encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection
