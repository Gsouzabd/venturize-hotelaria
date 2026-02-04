@extends('layouts.admin.master')

@section('title', 'Fornecedores')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.fornecedores.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.fornecedores.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome'] ?? ''"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="CNPJ"/>
            <x-admin.text name="cnpj" :value="$filters['cnpj'] ?? ''"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$fornecedores">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($fornecedores as $fornecedor)
                <tr>
                    <td>{{ $fornecedor->id }}</td>
                    <td>{{ $fornecedor->nome }}</td>
                    <td>{{ $fornecedor->cnpj ?? '-' }}</td>
                    <td>{{ $fornecedor->telefone ?? '-' }}</td>
                    <td>{{ $fornecedor->email ?? '-' }}</td>
                    <td>{{ timestamp_br($fornecedor->created_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.fornecedores.edit" :route-params="['id' => $fornecedor->id]"/>
                        <x-admin.delete-btn route="admin.fornecedores.destroy" :route-params="['id' => $fornecedor->id]"/>
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

