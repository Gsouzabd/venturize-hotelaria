@extends('layouts.admin.master')

@section('title', 'Categorias')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.categorias.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.categorias.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$categorias">
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
            @forelse($categorias as $categoria)
                <tr>
                    <td>{{ $categoria->id }}</td>
                    <td>{{ $categoria->nome }}</td>
                    <td>{{ $categoria->descricao }}</td>
                    <td>{{ timestamp_br($categoria->created_at) }}</td>
                    <td>{{ timestamp_br($categoria->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.categorias.edit" :route-params="['id' => $categoria->id]"/>
                        <x-admin.delete-btn route="admin.categorias.destroy" :route-params="['id' => $categoria->id]"/>
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