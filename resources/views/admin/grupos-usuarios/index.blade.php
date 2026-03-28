@extends('layouts.admin.master')

@section('title', 'Grupos de Usuários')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.grupos-usuarios.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.grupos-usuarios.index">
        <x-admin.filter cols="3">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome'] ?? ''"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$grupos">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Nº de Usuários</th>
                <th>Permissões</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($grupos as $grupo)
                <tr>
                    <td>{{ $grupo->id }}</td>
                    <td>{{ $grupo->nome }}</td>
                    <td>{{ $grupo->usuarios_count }}</td>
                    <td>
                        @foreach($grupo->permissoes as $permissao)
                            <span class="badge badge-primary">{{ config('app.enums.permissoes_plano.' . $permissao->nome, $permissao->nome) }}</span>
                        @endforeach
                    </td>
                    <td>{{ timestamp_br($grupo->created_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.grupos-usuarios.edit" :route-params="['id' => $grupo->id]"/>
                        <x-admin.delete-btn route="admin.grupos-usuarios.destroy" :route-params="['id' => $grupo->id]"/>
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
