@extends('layouts.admin.master')
@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Opção Extra do Quarto')
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

    <!-- Formulário para cadastro de opção extra do quarto -->
    <x-admin.form save-route="admin.quartos-opcoes-extras.save" back-route="admin.quartos.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $opcaoExtra->id }}">
        @endif

        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>
            <!-- Nome -->
            <x-admin.field cols="6">
                <x-admin.label label="Nome" required/>
                <x-admin.text name="nome" id="nome" :value="old('nome', $opcaoExtra->nome ?? '')" required/>
            </x-admin.field>

            <!-- Preço -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço" required/>
                <x-admin.number name="preco" id="preco" :value="old('preco', $opcaoExtra->preco ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>
    </x-admin.form>
</div>
@endsection