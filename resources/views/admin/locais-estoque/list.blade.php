@extends('layouts.admin.master')

@section('title', 'Locais de Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.locais-estoque.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.locais-estoque.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$locaisEstoque">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Subestoques</th>
                <th>Descrição</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($locaisEstoque as $localEstoque)
                {{-- Linha do pai --}}
                <tr>
                    <td>{{ $localEstoque->id }}</td>
                    <td><strong>{{ $localEstoque->nome }}</strong></td>
                    <td>
                        @if($localEstoque->children->isNotEmpty())
                            <span class="badge badge-info">{{ $localEstoque->children->count() }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $localEstoque->descricao }}</td>
                    <td>{{ timestamp_br($localEstoque->created_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.locais-estoque.edit" :route-params="['id' => $localEstoque->id]"/>
                        <x-admin.delete-btn route="admin.locais-estoque.destroy" :route-params="['id' => $localEstoque->id]"/>
                    </td>
                </tr>
                {{-- Linhas dos filhos imediatamente abaixo --}}
                @foreach($localEstoque->children->sortBy('nome') as $filho)
                <tr class="table-secondary">
                    <td class="text-muted">{{ $filho->id }}</td>
                    <td style="padding-left:2rem">↳ {{ $filho->nome }}</td>
                    <td><span class="text-muted">—</span></td>
                    <td>{{ $filho->descricao }}</td>
                    <td>{{ timestamp_br($filho->created_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.locais-estoque.edit" :route-params="['id' => $filho->id]"/>
                        <x-admin.delete-btn route="admin.locais-estoque.destroy" :route-params="['id' => $filho->id]"/>
                    </td>
                </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection