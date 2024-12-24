@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' cliente')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.form save-route="admin.clientes.save"
                  back-route="admin.clientes.index"
                  :is-edit="$edit">
        <div class="card">
            <div class="card-body pb-2">
                @if($edit)
                    <x-admin.field-group>
                        <x-admin.field cols="4">
                            <x-admin.label label="ID"/>
                            <x-admin.text name="id" :value="old('id', $cliente->id)" readonly/>
                        </x-admin.field>
                    </x-admin.field-group>
                @endif

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Nome" required/>
                        <x-admin.text name="nome" :value="old('nome', $cliente->nome)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="E-mail" required/>
                        <x-admin.text name="email" :value="old('email', $cliente->email)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="E-mail Alternativo"/>
                        <x-admin.text name="email_alternativo" :value="old('email_alternativo', $cliente->email_alternativo)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Telefone" required/>
                        <x-admin.text name="telefone" :value="old('telefone', $cliente->telefone)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Celular"/>
                        <x-admin.text name="celular" :value="old('celular', $cliente->celular)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Endereço"/>
                        <x-admin.text name="endereco" :value="old('endereco', $cliente->endereco)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Número"/>
                        <x-admin.text name="numero" :value="old('numero', $cliente->numero)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Complemento"/>
                        <x-admin.text name="complemento" :value="old('complemento', $cliente->complemento)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Bairro"/>
                        <x-admin.text name="bairro" :value="old('bairro', $cliente->bairro)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Cidade"/>
                        <x-admin.text name="cidade" :value="old('cidade', $cliente->cidade)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Estado"/>
                        <x-admin.text name="estado" :value="old('estado', $cliente->estado)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="CEP"/>
                        <x-admin.text name="cep" :value="old('cep', $cliente->cep)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="País"/>
                        <x-admin.text name="pais" :value="old('pais', $cliente->pais)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Tipo"/>
                        <x-admin.select name="tipo"
                                        :items="['Pessoa Física' => 'Pessoa Física', 'Pessoa Jurídica' => 'Pessoa Jurídica' ]"
                                        selectedItem="{{ old('tipo', $cliente->tip ?? 'Pessoa Física') }}"/>

                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Estrangeiro"/>
                        <x-admin.switcher name="estrangeiro"
                                          :checked="$errors->any() ? old('estrangeiro') : $cliente->estrangeiro"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Sexo"/>
                        <x-admin.select name="sexo"
                                        :items="['Masculino' => 'Masculino', 'Feminino' => 'Feminino']"
                                        :selected-item="old('sexo', $cliente->sexo)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Data de Nascimento"/>
                        <x-admin.datepicker name="data_nascimento" id="data_nascimento" :value="old('data_nascimento', isset($cliente->data_nascimento) ? \Carbon\Carbon::parse($cliente->data_nascimento)->format('d-m-Y') : '')" required/>                        

                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="CPF"/>
                        <x-admin.text name="cpf" :value="old('cpf', $cliente->cpf)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="RG"/>
                        <x-admin.text name="rg" :value="old('rg', $cliente->rg)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Passaporte"/>
                        <x-admin.text name="passaporte" :value="old('passaporte', $cliente->passaporte)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Órgão Expedidor"/>
                        <x-admin.text name="orgao_expedidor" :value="old('orgao_expedidor', $cliente->orgao_expedidor)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Estado Civil"/>
                        <x-admin.select name="estado_civil"
                                        :items="['Solteiro' => 'Solteiro', 'Casado' => 'Casado', 'Divorciado' => 'Divorciado', 'Viúvo' => 'Viúvo']"
                                        :selected-item="old('estado_civil', $cliente->estado_civil)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Inscrição Estadual PF"/>
                        <x-admin.text name="inscricao_estadual_pf" :value="old('inscricao_estadual_pf', $cliente->inscricao_estadual_pf)"/>
                    </x-admin.field>

                    <x-admin.field cols="4">
                        <x-admin.label label="Profissão"/>
                        <x-admin.text name="profissao" :value="old('profissao', $cliente->profissao)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field-group>
                    <x-admin.field cols="4">
                        <x-admin.label label="Ativo"/>
                        <x-admin.switcher name="fl_ativo"
                                          :checked="$errors->any() ? old('fl_ativo') : $cliente->fl_ativo"/>
                    </x-admin.field>
                </x-admin.field-group>
            </div>
        </div>
    </x-admin.form>
@endsection