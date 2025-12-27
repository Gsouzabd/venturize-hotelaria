@extends('layouts.admin.master')

@section('title', 'Categorias de Despesas')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.categorias-despesas.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.categorias-despesas.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome'] ?? ''"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Status"/>
            <x-admin.select name="fl_ativo">
                <option value="">Todos</option>
                <option value="1" {{ ($filters['fl_ativo'] ?? '') == '1' ? 'selected' : '' }}>Ativo</option>
                <option value="0" {{ ($filters['fl_ativo'] ?? '') == '0' ? 'selected' : '' }}>Inativo</option>
            </x-admin.select>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$categorias">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Status</th>
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
                    <td>
                        @if($categoria->fl_ativo)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </td>
                    <td>{{ timestamp_br($categoria->created_at) }}</td>
                    <td>{{ timestamp_br($categoria->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.categorias-despesas.edit" :route-params="['id' => $categoria->id]"/>
                        <x-admin.delete-btn route="admin.categorias-despesas.destroy" :route-params="['id' => $categoria->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection

