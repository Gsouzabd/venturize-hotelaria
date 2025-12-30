@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Impressora')
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

    <!-- Formulário para cadastro de impressora -->
    <x-admin.form save-route="admin.impressoras.save" back-route="admin.impressoras.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $impressora->id }}">
        @endif
        
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>
            <!-- Nome -->
            <x-admin.field cols="6">
                <x-admin.label label="Nome" required/>
                <x-admin.text name="nome" id="nome" :value="old('nome', $impressora->nome)" required/>
            </x-admin.field>

            <!-- IP -->
            <x-admin.field cols="6">
                <x-admin.label label="IP" required/>
                <x-admin.text name="ip" id="ip" :value="old('ip', $impressora->ip)" 
                    placeholder="192.168.0.120" required/>
                <small class="form-text text-muted">Exemplo: 192.168.0.120</small>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Porta -->
            <x-admin.field cols="4">
                <x-admin.label label="Porta" required/>
                <x-admin.text name="porta" id="porta" type="number" 
                    :value="old('porta', $impressora->porta ?? 9100)" 
                    min="1" max="65535" required/>
                <small class="form-text text-muted">Padrão: 9100</small>
            </x-admin.field>

            <!-- Tipo -->
            <x-admin.field cols="4">
                <x-admin.label label="Tipo" required/>
                <x-admin.select name="tipo" id="tipo" required>
                    <option value="termica" {{ old('tipo', $impressora->tipo ?? 'termica') == 'termica' ? 'selected' : '' }}>
                        Térmica
                    </option>
                    <option value="convencional" {{ old('tipo', $impressora->tipo ?? '') == 'convencional' ? 'selected' : '' }}>
                        Convencional
                    </option>
                </x-admin.select>
            </x-admin.field>

            <!-- Ordem -->
            <x-admin.field cols="4">
                <x-admin.label label="Ordem"/>
                <x-admin.text name="ordem" id="ordem" type="number" 
                    :value="old('ordem', $impressora->ordem ?? 0)" min="0"/>
                <small class="form-text text-muted">Para ordenação na lista</small>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Descrição -->
            <x-admin.field cols="12">
                <x-admin.label label="Descrição"/>
                <x-admin.textarea name="descricao" id="descricao" 
                    :value="old('descricao', $impressora->descricao)" rows="3"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Ativo -->
            <x-admin.field cols="12">
                <div class="form-check">
                    <input type="checkbox" name="ativo" class="form-check-input" id="ativo" 
                        value="1" {{ old('ativo', $impressora->ativo ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="ativo">
                        Impressora Ativa
                    </label>
                    <small class="form-text text-muted d-block">
                        Impressoras inativas não serão utilizadas pelo sistema de impressão.
                    </small>
                </div>
            </x-admin.field>
        </x-admin.field-group>

    </x-admin.form>
</div>
@endsection

