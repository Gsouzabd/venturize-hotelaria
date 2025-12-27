@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Categoria de Despesa')
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
    <x-admin.form save-route="admin.categorias-despesas.save" back-route="admin.categorias-despesas.index">
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

            <!-- Status -->
            <x-admin.field cols="6">
                <x-admin.label label="Status"/>
                <x-admin.switcher name="fl_ativo" :checked="$errors->any() ? old('fl_ativo', $categoria->fl_ativo ?? true) : ($categoria->fl_ativo ?? true)"/>
            </x-admin.field>

        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Descrição -->
            <x-admin.field cols="12">
                <x-admin.label label="Descrição"/>
                <x-admin.textarea name="descricao" id="descricao">{{ old('descricao', $categoria->descricao) }}</x-admin.textarea>
            </x-admin.field>
        </x-admin.field-group>

    </x-admin.form>
</div>
@endsection

