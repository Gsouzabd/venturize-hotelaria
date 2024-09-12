@extends('layouts.admin.master')

@section('title', 'Clientes')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.clientes.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.clientes.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="E-mail"/>
            <x-admin.text name="email" :value="$filters['email']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$clientes">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Criado em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->nome }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td>{{ $cliente->telefone }}</td>
                    <td>{{ timestamp_br($cliente->created_at) }}</td>
                    <td>{{ timestamp_br($cliente->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.clientes.edit" :route-params="['id' => $cliente->id]"/>
                        <x-admin.delete-btn route="admin.clientes.destroy" :route-params="['id' => $cliente->id]"/>
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
