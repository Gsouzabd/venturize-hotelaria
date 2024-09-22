@extends('layouts.admin.master')
@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Quarto')
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

    <!-- Formulário para cadastro de quarto -->
    <x-admin.form save-route="admin.quartos.save" back-route="admin.quartos.index">
        @csrf

        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>

            <!-- Número -->
            <x-admin.field cols="6">
                <x-admin.label label="Número" required/>
                <x-admin.text name="numero" id="numero" :value="old('numero', $quarto->numero)" required/>
            </x-admin.field>
            <!-- Andar -->
            <x-admin.field cols="6">
                <x-admin.label label="Andar" required/>
                <x-admin.select name="andar" id="andar" :value="old('andar', $quarto->andar)"
                    :items="['Terréo' => 'Terréo', '1o Andar' => '1o Andar']"
                    selectedItem="{{ old('andar', $quarto->andar) }}" required/>
            </x-admin.field>


        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Ramal -->
            <x-admin.field cols="6">
                <x-admin.label label="Ramal"/>
                <x-admin.text name="ramal" id="ramal" :value="old('ramal', $quarto->ramal)"/>
            </x-admin.field>

            <!-- Posição do Quarto -->
            <x-admin.field cols="6">
                <x-admin.label label="Posição do Quarto"/>
                <x-admin.select name="posicao_quarto" id="posicao_quarto" :value="old('posicao_quarto', $quarto->posicao_quarto)"
                    :items="['frente' => 'Frente', 'fundos' => 'Fundos']"
                    selectedItem="{{ old('posicao_quarto', $quarto->posicao_quarto) }}" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Quantidade de Camas de Casal -->
            <x-admin.field cols="6">
                <x-admin.label label="Quantidade de Camas de Casal"/>
                <x-admin.number name="quantidade_cama_casal" id="quantidade_cama_casal" :value="old('quantidade_cama_casal', $quarto->quantidade_cama_casal)"/>
            </x-admin.field>
            
            <!-- Quantidade de Camas de Solteiro -->
            <x-admin.field cols="6">
                <x-admin.label label="Quantidade de Camas de Solteiro"/>
                <x-admin.number name="quantidade_cama_solteiro" id="quantidade_cama_solteiro" :value="old('quantidade_cama_solteiro', $quarto->quantidade_cama_solteiro)"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Classificação -->
            <x-admin.field cols="6">
                <x-admin.label label="Classificação"/>
                <x-admin.select name="classificacao" id="classificacao" :value="old('classificacao', $quarto->classificacao)"
                    :items="['Embaúba' => 'Embaúba', 'Camará' => 'Camará']"
                    selectedItem="{{ old('classificacao', $quarto->classificacao) }}" required/>
            </x-admin.field>

            <!-- Acessibilidade -->
            <x-admin.field cols="6">
                <x-admin.label label="Acessibilidade"/>
                <select name="acessibilidade" id="acessibilidade" class="form-control">
                    <option value="1" {{ old('acessibilidade', $quarto->acessibilidade) == '1' ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ old('acessibilidade', $quarto->acessibilidade) == '0' ? 'selected' : '' }}>Não</option>
                </select>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Inativo -->
            <x-admin.field cols="6">
                <x-admin.label label="Inativo"/>
                <select name="inativo" id="inativo" class="form-control">
                    <option value="1" {{ old('inativo', $quarto->inativo) == '1' ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ old('inativo', $quarto->inativo) == '0' ? 'selected' : '' }}>Não</option>
                </select>
            </x-admin.field>
        </x-admin.field-group>
    </x-admin.form>
</div>
@endsection