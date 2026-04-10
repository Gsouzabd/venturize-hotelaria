@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' cliente')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.form save-route="admin.clientes.save"
                  back-route="admin.clientes.index"
                  :is-edit="$edit">
        @if(!empty($reservaId))
            <input type="hidden" name="reserva_id" value="{{ $reservaId }}">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i>
                Este hóspede será adicionado como acompanhante da reserva <strong>#{{ $reservaId }}</strong>.
                <div class="mt-2">
                    <label class="mr-2 mb-0"><strong>Tipo de Acompanhante:</strong></label>
                    <select name="tipo_acompanhante" class="form-control d-inline-block w-auto">
                        <option value="Adulto">Adulto</option>
                        <option value="Criança 8 a 12 anos">Criança 8 a 12 anos</option>
                        <option value="Criança até 7 anos">Criança até 7 anos</option>
                    </select>
                </div>
            </div>
        @endif
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
                        <x-admin.text name="cep" id="cep_cliente" :value="old('cep', $cliente->cep)">
                            <x-slot name="append">
                                <button type="button" id="buscarCepCliente" class="btn btn-secondary">Buscar</button>
                            </x-slot>
                        </x-admin.text>
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
                                        :items="['M' => 'Masculino', 'F' => 'Feminino']"
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function buscarCep(cep, callback) {
        cep = cep.replace(/\D/g, '');
        if (cep.length !== 8) return;
        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.erro) { alert('CEP não encontrado.'); return; }
                callback(d);
            })
            .catch(function() { alert('Erro ao buscar CEP.'); });
    }

    var btnCep = document.getElementById('buscarCepCliente');
    var cepInput = document.getElementById('cep_cliente');

    function preencherCampos(d) {
        var endereco = document.querySelector('input[name="endereco"]');
        var bairro = document.querySelector('input[name="bairro"]');
        var cidade = document.querySelector('input[name="cidade"]');
        var estado = document.querySelector('input[name="estado"]');
        var pais = document.querySelector('input[name="pais"]');
        if (endereco) endereco.value = d.logradouro || '';
        if (bairro) bairro.value = d.bairro || '';
        if (cidade) cidade.value = d.localidade || '';
        if (estado) estado.value = d.uf || '';
        if (pais && !pais.value) pais.value = 'Brasil';
    }

    if (btnCep && cepInput) {
        btnCep.addEventListener('click', function() {
            buscarCep(cepInput.value, preencherCampos);
        });
        cepInput.addEventListener('blur', function() {
            buscarCep(this.value, preencherCampos);
        });
    }
});
</script>
@endpush
@endsection