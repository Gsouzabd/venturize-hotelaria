@extends('layouts.admin.master')
@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Plano de Preço')
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

    <!-- Formulário para cadastro de plano de preço -->
    <x-admin.form save-route="admin.quartos.planos-preco.save" back-route="admin.quartos.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $planoPreco->id }}">
            <input type="hidden" name="quarto_id" value="{{ $planoPreco->quarto_id }}">
        @else
            <input type="hidden" name="quarto_id" value="{{ $quartoId }}">
        @endif

        <x-admin.field-group>
            <!-- Is Individual -->
            <x-admin.field cols="4">
                <x-admin.label label="Individual"/>
                <input type="radio" name="tipo_quarto" id="is_individual" value="individual" {{ old('tipo_quarto', $planoPreco->is_individual ?? false) ? 'checked' : '' }}>
            </x-admin.field>
            <!-- Is Duplo -->
            <x-admin.field cols="4">
                <x-admin.label label="Duplo"/>
                <input type="radio" name="tipo_quarto" id="is_duplo" value="duplo" {{ old('tipo_quarto', $planoPreco->is_duplo ?? false) ? 'checked' : '' }}>
            </x-admin.field>
            
            <!-- Is Triplo -->
            <x-admin.field cols="4">
                <x-admin.label label="Triplo"/>
                <input type="radio" name="tipo_quarto" id="is_triplo" value="triplo" {{ old('tipo_quarto', $planoPreco->is_triplo ?? false) ? 'checked' : '' }}>
            </x-admin.field>
            

        </x-admin.field-group>
        
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>
            <!-- Data Início -->
            <x-admin.field cols="6">
                <x-admin.label label="Data Início" required/>
                <x-admin.datepicker name="data_inicio" id="data_inicio" :value="old('data_inicio', $planoPreco->data_inicio ? \Carbon\Carbon::parse($planoPreco->data_inicio)->format('d/m/Y') : '')" required/>
            </x-admin.field>

            <!-- Data Fim -->
            <x-admin.field cols="6">
                <x-admin.label label="Data Fim" required/>
                <x-admin.datepicker name="data_fim" id="data_fim" :value="old('data_fim', $planoPreco->data_fim ? \Carbon\Carbon::parse($planoPreco->data_fim)->format('d/m/Y') : '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Preço Segunda -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Segunda" required/>
                <x-admin.number name="preco_segunda" id="preco_segunda" :value="old('preco_segunda', $planoPreco->preco_segunda ?? '')" required/>
            </x-admin.field>

            <!-- Preço Terça -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Terça" required/>
                <x-admin.number name="preco_terca" id="preco_terca" :value="old('preco_terca', $planoPreco->preco_terca ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Preço Quarta -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Quarta" required/>
                <x-admin.number name="preco_quarta" id="preco_quarta" :value="old('preco_quarta', $planoPreco->preco_quarta ?? '')" required/>
            </x-admin.field>

            <!-- Preço Quinta -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Quinta" required/>
                <x-admin.number name="preco_quinta" id="preco_quinta" :value="old('preco_quinta', $planoPreco->preco_quinta ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Preço Sexta -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Sexta" required/>
                <x-admin.number name="preco_sexta" id="preco_sexta" :value="old('preco_sexta', $planoPreco->preco_sexta ?? '')" required/>
            </x-admin.field>

            <!-- Preço Sábado -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Sábado" required/>
                <x-admin.number name="preco_sabado" id="preco_sabado" :value="old('preco_sabado', $planoPreco->preco_sabado ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Preço Domingo -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço Domingo" required/>
                <x-admin.number name="preco_domingo" id="preco_domingo" :value="old('preco_domingo', $planoPreco->preco_domingo ?? '')" required/>
            </x-admin.field>

            <!-- Plano Padrão -->
            <x-admin.field cols="6">
                <x-admin.label label="Plano Padrão"/>
                <select name="is_default" id="is_default" class="form-control">
                    <option value="0" {{ old('is_default', $planoPreco->is_default ?? '') == '0' ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ old('is_default', $planoPreco->is_default ?? '') == '1' ? 'selected' : '' }}>Sim</option>
                </select>
            </x-admin.field>
        </x-admin.field-group>


    </x-admin.form>
</div>
@endsection