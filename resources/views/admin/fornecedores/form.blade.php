@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Fornecedor')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection
@section('content')

<div class="container">    
    <!-- Exibe erros de validação, se houver -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário para cadastro de fornecedor -->
    <x-admin.form save-route="admin.fornecedores.save" back-route="admin.fornecedores.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $fornecedor->id }}">
        @endif
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>

            <!-- Nome -->
            <x-admin.field cols="6">
                <x-admin.label label="Nome" required/>
                <x-admin.text name="nome" id="nome" :value="old('nome', $fornecedor->nome)" required/>
            </x-admin.field>

            <!-- CNPJ -->
            <x-admin.field cols="6">
                <x-admin.label label="CNPJ"/>
                <x-admin.text name="cnpj" id="cnpj" :value="old('cnpj', $fornecedor->cnpj)"/>
            </x-admin.field>

        </x-admin.field-group>

        <x-admin.field-group>

            <!-- Telefone -->
            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="telefone" id="telefone" :value="old('telefone', $fornecedor->telefone)"/>
            </x-admin.field>

            <!-- Email -->
            <x-admin.field cols="6">
                <x-admin.label label="Email"/>
                <x-admin.text name="email" id="email" type="email" :value="old('email', $fornecedor->email)"/>
            </x-admin.field>

        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Endereço -->
            <x-admin.field cols="12">
                <x-admin.label label="Endereço"/>
                <x-admin.textarea name="endereco" id="endereco">{{ old('endereco', $fornecedor->endereco) }}</x-admin.textarea>
            </x-admin.field>
        </x-admin.field-group>

    </x-admin.form>
</div>
@endsection

