@extends('layouts.admin.master')

@php 
use Carbon\Carbon; 
use App\Models\Produto;
@endphp

@section('title', 'Produtos')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.produtos.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.produtos.index">
        <x-admin.filter cols="2">
            <x-admin.label label="ID"/>
            <x-admin.text name="id" :value="$filters['id']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Código Interno"/>
            <x-admin.text name="codigo_interno" :value="$filters['codigo_interno']"/>
        </x-admin.filter>
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>
    
        <x-admin.filter cols="2">
            <x-admin.label label="Categoria"/>
            <select name="categoria_id" class="form-control">
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria['id'] }}">{{ $categoria['nome'] }}</option>
                @endforeach
            </select>
        </x-admin.filter>
        
        <!-- Filtro de Data de Criação -->
        <x-admin.filter cols="2">
            <x-admin.label label="Data de Criação"/>
            <x-admin.datepicker name="created_at" :value="$filters['created_at']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$produtos">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço de Venda</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($produtos as $produto)
                <tr>
                    <td>{{ $produto->id }}</td>
                    <td>{{ $produto->descricao }}</td>
                    <td>{{ $produto->categoria->nome ?? $produto->categoria_produto }}</td>
                    <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                    <td>{{ Carbon::parse($produto->created_at)->format('d-m-Y') }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.produtos.edit" :route-params="['id' => $produto->id]" :label="html_entity_decode('<i class=\'fas fa-edit\'></i>')"/>
                        <x-admin.delete-btn route="admin.produtos.destroy" :route-params="['id' => $produto->id]" :label="html_entity_decode('<i class=\'fas fa-trash-alt\'></i>')"/>
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