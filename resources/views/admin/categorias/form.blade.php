@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Categoria')
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

    <!-- Formulário para cadastro de categoria -->
    <x-admin.form save-route="admin.categorias.save" back-route="admin.categorias.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $categoria->id }}">
        @endif
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>

            <!-- Nome -->
            <x-admin.field cols="6">
                <x-admin.label label="Nome" required/>
                <x-admin.text name="nome" id="nome" :value="old('nome', $categoria->nome)" required/>
            </x-admin.field>

            <!-- Descrição -->
            <x-admin.field cols="6">
                <x-admin.label label="Descrição"/>
                <x-admin.textarea name="descricao" id="descricao" :value="old('descricao', $categoria->descricao)"/>
            </x-admin.field>

        </x-admin.field-group>

    </x-admin.form>
</div>
@endsection