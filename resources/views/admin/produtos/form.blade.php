@extends('layouts.admin.master')
@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Produto')
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

    <!-- Formulário para cadastro de produto -->
    <x-admin.form save-route="admin.produtos.save" back-route="admin.produtos.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $produto->id }}">
        @endif
        
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>
            <!-- Descrição -->
            <x-admin.field cols="6">
                <x-admin.label label="Descrição (nome)" required/>
                <x-admin.text name="descricao" id="descricao" :value="old('descricao', $produto->descricao ?? '')" required/>
            </x-admin.field>

            <!-- Valor Unitário -->
            {{-- <x-admin.field cols="6">
                <x-admin.label label="Valor Unitário" required/>
                <x-admin.number name="valor_unitario" id="valor_unitario" :value="old('valor_unitario', $produto->valor_unitario ?? '')" required/>
            </x-admin.field> --}}
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Preço de Custo -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço de Custo" required/>
                <x-admin.number name="preco_custo" id="preco_custo" :value="old('preco_custo', $produto->preco_custo ?? '0.00')" step="0.01" min="0" required/>
            </x-admin.field>
        
            <!-- Preço de Venda -->
            <x-admin.field cols="6">
                <x-admin.label label="Preço de Venda" required/>
                <x-admin.number name="preco_venda" id="preco_venda" :value="old('preco_venda', $produto->preco_venda ?? '0.00')" step="0.01" min="0" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Estoque Mínimo -->
            <x-admin.field cols="6">
                <x-admin.label label="Estoque Mínimo" required/>
                <x-admin.number name="estoque_minimo" id="estoque_minimo" :value="old('estoque_minimo', $produto->estoque_minimo ?? '')" required/>
            </x-admin.field>

            <!-- Estoque Máximo -->
            <x-admin.field cols="6">
                <x-admin.label label="Estoque Máximo" required/>
                <x-admin.number name="estoque_maximo" id="estoque_maximo" :value="old('estoque_maximo', $produto->estoque_maximo ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Categoria Produto -->
            <x-admin.field cols="6">
                <x-admin.label label="Categoria Produto" required/>
                <x-admin.select name="categoria_produto" id="categoria_produto" :items="\App\Models\Produto::CATEGORIAS" :selected-item="old('categoria_produto', $produto->categoria_produto ?? '')" required/>
            </x-admin.field>

            <!-- Código de Barras -->
            <x-admin.field cols="6">
                <x-admin.label label="Código de Barras" />
                <x-admin.text name="codigo_barras_produto" id="codigo_barras_produto" :value="old('codigo_barras_produto', $produto->codigo_barras_produto ?? '')" />
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Código Interno -->
            <x-admin.field cols="6">
                <x-admin.label label="Código Interno" />
                <x-admin.text name="codigo_interno" id="codigo_interno" :value="old('codigo_interno', $produto->codigo_interno ?? $produto->id)"/>
            </x-admin.field>

            <!-- Impressora -->
            <x-admin.field cols="6">
                <x-admin.label label="Impressora" />
                <x-admin.select name="impressora" id="impressora" :items="\App\Models\Produto::IMPRESSORA" :selected-item="old('impressora', $produto->impressora ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Unidade -->
            <x-admin.field cols="6">
                <x-admin.label label="Unidade" required/>
                <x-admin.select name="unidade" id="unidade" :items="\App\Models\Produto::UNIDADES" :selected-item="old('unidade', $produto->unidade ?? '')" required/>
            </x-admin.field>

            <!-- Ativo -->
            <x-admin.field cols="6">
                <x-admin.label label="Ativo" required/>
                <x-admin.select name="ativo" id="ativo" :items="['1' => 'Sim', '0' => 'Não']" :selected-item="old('ativo', $produto->ativo ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Criado Por -->
            <x-admin.field cols="6">
                <x-admin.label label="Criado Por" />
                <x-admin.text name="criado_por" id="criado_por" :value="old('criado_por', $produto->usuarioCriador->nome ?? auth()->user()->name)" readonly/>
            </x-admin.field>

            <!-- Complemento -->
            <x-admin.field cols="6">
                <x-admin.label label="Complemento"/>
                <x-admin.text name="complemento" id="complemento" :value="old('complemento', $produto->complemento ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group>
            <!-- Produto/Serviço -->
            <x-admin.field cols="6">
                <x-admin.label label="Produto/Serviço" required/>
                <x-admin.select name="produto_servico" id="produto_servico" :items="['produto' => 'Produto', 'servico' => 'Serviço']" :selected-item="old('produto_servico', $produto->produto_servico ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>
    </x-admin.form>
</div>
@endsection