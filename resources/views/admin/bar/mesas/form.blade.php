@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Mesa')
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

    <!-- Formulário para cadastro de mesa -->
    <x-admin.form save-route="admin.bar.mesas.save" back-route="admin.bar.mesas.index">
        @csrf

        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>

            <!-- Número -->
            <x-admin.field cols="6">
                <x-admin.label label="Número" required/>
                <x-admin.text name="numero" id="numero" :value="old('numero', $mesa->numero)" required/>
            </x-admin.field>

            <!-- Status -->
            <x-admin.field cols="6">
                <x-admin.label label="Status" required/>
                <x-admin.select name="status" id="status" :value="old('status', $mesa->status)"
                    :items="['Disponível' => 'Disponível', 'Ocupada' => 'Ocupada', 'Reservada' => 'Reservada']"
                    selectedItem="{{ old('status', $mesa->status) }}" required/>
            </x-admin.field>

        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Ativa -->
            <x-admin.field cols="6">
                <x-admin.label label="Ativa"/>
                <select name="ativa" id="ativa" class="form-control">
                    <option value="1" {{ old('ativa', $mesa->ativa) == '1' ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ old('ativa', $mesa->ativa) == '0' ? 'selected' : '' }}>Não</option>
                </select>
            </x-admin.field>
        </x-admin.field-group>

    </x-admin.form>
</div>
@endsection