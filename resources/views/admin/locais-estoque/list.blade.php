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
                <th>Descrição</th>
                <th>Criado em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($locaisEstoque as $localEstoque)
                <tr>
                    <td>{{ $localEstoque->id }}</td>
                    <td>{{ $localEstoque->nome }}</td>
                    <td>{{ $localEstoque->descricao }}</td>
                    <td>{{ timestamp_br($localEstoque->created_at) }}</td>
                    <td>{{ timestamp_br($localEstoque->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.locais-estoque.edit" :route-params="['id' => $localEstoque->id]"/>
                        <x-admin.delete-btn route="admin.locais-estoque.destroy" :route-params="['id' => $localEstoque->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection