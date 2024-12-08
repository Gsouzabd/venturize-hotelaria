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
                <x-admin.label label="Posição do Quarto" required/>
                <select name="posicao_quarto" id="posicao_quarto" class="form-control" required>
                    <option value="Frente" {{ old('posicao_quarto', $quarto->posicao_quarto ?? '') == 'Frente' ? 'selected' : '' }}>Frente</option>
                    <option value="Fundos" {{ old('posicao_quarto', $quarto->posicao_quarto ?? '') == 'Fundos' ? 'selected' : '' }}>Fundos</option>
                    <option value="Lateral" {{ old('posicao_quarto', $quarto->posicao_quarto ?? '') == 'Lateral' ? 'selected' : '' }}>Lateral</option>
                </select>
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
                    <option value="0" {{ old('inativo', $quarto->inativo) == '0' ? 'selected' : '' }}>Não</option>

                    <option value="1" {{ old('inativo', $quarto->inativo) == '1' ? 'selected' : '' }}>Sim</option>
                </select>
            </x-admin.field>
        </x-admin.field-group>

        <!-- Bloco para exibir planos de preços -->
        <hr>
        @if($edit)
            <x-admin.field-group id="planos-precos-block">
                <x-admin.field cols="12">
                    <h4 style="text-align: center">
                        Planos de Preços
                    </h4> 
                    @if($quarto->planosPrecos == null || $quarto->planosPrecos->isEmpty())
                        <div class="alert alert-info">
                            Não há planos de preços cadastrados para este quarto.
                        </div>
                        <x-admin.edit-btn route="admin.quartos.planos-preco.edit" :route-params="['quartoId' => $quarto->id, 'id' => null]" label="Criar Plano de Preço"/>
                    @else
                        <!-- Plano de Preço Padrão -->
                        <h6>
                            <i class="fas fa-star"></i> Plano de Preço
                        </h6>
                    
                        <table class="table table-bordered" class="planos-precos-periodo">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Segunda</th>
                                    <th>Terça</th>
                                    <th>Quarta</th>
                                    <th>Quinta</th>
                                    <th>Sexta</th>
                                    <th>Sábado</th>
                                    <th>Domingo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quarto->planosPrecos->where('is_default', true) as $planoPreco)
                                    <tr>
                                        <td>
                                            @if($planoPreco->is_individual)
                                                Individual
                                            @elseif($planoPreco->is_duplo)
                                                Duplo
                                            @elseif($planoPreco->is_triplo)
                                                Triplo
                                            @else
                                                N/A
                                            @endif
                                        </td>          
                                        <td>{{ 'R$ ' . $planoPreco->preco_segunda }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_terca }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_quarta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_quinta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_sexta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_sabado }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_domingo }}</td>
                                        <td>
                                            <a href="{{ route('admin.quartos.planos-preco.edit', ['quartoId' => $quarto->id, 'id' => $planoPreco->id]) }}" class="btn btn-sm btn-primary">Editar</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
            
                        <!-- Planos de Preço por Período -->
                        <h6>
                            <i class="fas fa-calendar-alt"></i> Por Período
                        </h6>  
                        <table class="table table-bordered" class="planos-precos-periodo">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Segunda</th>
                                    <th>Terça</th>
                                    <th>Quarta</th>
                                    <th>Quinta</th>
                                    <th>Sexta</th>
                                    <th>Sábado</th>
                                    <th>Domingo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quarto->planosPrecos->where('is_default', 0) as $planoPreco)
                                    <tr>
                                        <td>
                                            @if($planoPreco->is_individual)
                                                Individual
                                            @elseif($planoPreco->is_duplo)
                                                Duplo
                                            @elseif($planoPreco->is_triplo)
                                                Triplo
                                            @else
                                                N/A
                                            @endif
                                        </td>                                         <td>{{ $planoPreco->data_inicio ? \Carbon\Carbon::parse($planoPreco->data_inicio)->format('d/m/Y') : '' }}</td>
                                        <td>{{ $planoPreco->data_fim ? \Carbon\Carbon::parse($planoPreco->data_fim)->format('d/m/Y') : '' }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_segunda }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_terca }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_quarta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_quinta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_sexta }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_sabado }}</td>
                                        <td>{{ 'R$ ' . $planoPreco->preco_domingo }}</td>
                                        <td>
                                            <a href="{{ route('admin.quartos.planos-preco.edit', ['quartoId' => $quarto->id, 'id' => $planoPreco->id]) }}" class="btn btn-sm btn-primary">Editar</a>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
            
                        <x-admin.edit-btn route="admin.quartos.planos-preco.edit" :route-params="['quartoId' => $quarto->id, 'id' => null]" label="Adicionar Plano de Preço"/>
                    @endif
                </x-admin.field>
            </x-admin.field-group>
        @endif

    </x-admin.form>
</div>
@endsection