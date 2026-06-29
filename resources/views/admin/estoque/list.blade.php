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
    <ul class="nav nav-tabs mb-0 flex-wrap" id="estoqueTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ !request('local_estoque_id') ? 'active' : '' }}"
               href="{{ route('admin.estoque.index', request()->except(['local_estoque_id', 'page'])) }}">
               Todos
            </a>
        </li>
        @foreach ($locaisEstoque as $local)
            @if($local->children->isNotEmpty())
                {{-- Local pai com filhos: mostra cada filho como tab separada --}}
                @foreach($local->children as $filho)
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ request('local_estoque_id') == $filho->id ? 'active' : '' }}"
                           href="{{ route('admin.estoque.index', array_merge(request()->except('page'), ['local_estoque_id' => $filho->id])) }}">
                           <small class="text-muted">{{ $local->nome }} ›</small> {{ $filho->nome }}
                        </a>
                    </li>
                @endforeach
            @else
                {{-- Local folha sem pai: mostra como tab simples --}}
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('local_estoque_id') == $local->id ? 'active' : '' }}"
                       href="{{ route('admin.estoque.index', array_merge(request()->except('page'), ['local_estoque_id' => $local->id])) }}">
                       {{ $local->nome }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>

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
