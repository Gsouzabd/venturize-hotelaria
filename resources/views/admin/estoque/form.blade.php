@extends('layouts.admin.master')
@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Estoque')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

{{-- @php dd($estoque->produto); @endphp --}}

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

    <!-- Formulário para cadastro de estoque -->
    <x-admin.form save-route="admin.estoque.save" back-route="admin.estoque.index">
        @csrf
        @if($edit)
            <input type="hidden" name="id" value="{{ $estoque->id }}">
        @endif
        
        <!-- Agrupamento de campos em linhas de 2 colunas -->
        <x-admin.field-group>
            <!-- Produto -->
            <x-admin.field cols="6">
                <x-admin.label label="Produto Descrição" required/>
                <input type="text" name="produto_descricao" id="produto_descricao" class="form-control" autocomplete="off" value="{{ old('produto_descricao', $estoque->produto->descricao ?? '') }}" @if($edit) readonly @endif required/>
                <input type="hidden" name="produto_id" id="produto_id" value="{{ old('produto_id', $estoque->produto->id ?? '') }}">
                <div id="produto_suggestions" class="dropdown-menu"></div>
            </x-admin.field>
            <!-- Local de Estoque -->
            <x-admin.field cols="6">
                <x-admin.label label="Local de Estoque" required/>
                @if($edit)
                    <input type="text" name="local_estoque_nome" id="local_estoque_nome" class="form-control" value="{{ $locaisEstoque->firstWhere('id', $estoque->local_estoque_id)->nome ?? '' }}" readonly/>
                    <input type="hidden" name="local_estoque_id" id="local_estoque_id" value="{{ $estoque->local_estoque_id }}">
                @else
                    <x-admin.select name="local_estoque_id" id="local_estoque_id" :items="$locaisEstoque->pluck('nome', 'id')" :selected-item="old('local_estoque_id', $estoque->local_estoque_id ?? '')" required/>
                @endif
            </x-admin.field>
            <!-- Quantidade -->
            <x-admin.field cols="6">
                <x-admin.label label="Quantidade" required/>
                <x-admin.number name="quantidade" id="quantidade" :value="old('quantidade', $estoque->quantidade ?? '')" required/>
            </x-admin.field>
        </x-admin.field-group>
    </x-admin.form>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM fully loaded and parsed');

        const produtoDescricaoInput = document.getElementById('produto_descricao');
        const produtoIdInput = document.getElementById('produto_id');
        const suggestionsBox = document.getElementById('produto_suggestions');

        if (!produtoDescricaoInput || !produtoIdInput || !suggestionsBox) {
            console.error('One or more elements not found:', {
                produtoDescricaoInput,
                produtoIdInput,
                suggestionsBox
            });
            return;
        }

        produtoDescricaoInput.addEventListener('input', function () {
            const query = this.value;

            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }

            console.log(`Fetching products with query: ${query}`);

            fetch(`/admin/produtos/search?query=${query}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    suggestionsBox.innerHTML = '';
                    data.forEach(produto => {
                        const suggestionItem = document.createElement('a');
                        suggestionItem.classList.add('dropdown-item');
                        suggestionItem.textContent = `ID: ${produto.id} ${produto.descricao}`;
                        suggestionItem.dataset.id = produto.id;
                        suggestionsBox.appendChild(suggestionItem);
                    });
                    suggestionsBox.style.display = 'block';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        });

        suggestionsBox.addEventListener('click', function (event) {
            if (event.target.classList.contains('dropdown-item')) {
                produtoDescricaoInput.value = event.target.textContent;
                produtoIdInput.value = event.target.dataset.id;
                suggestionsBox.style.display = 'none';
            }
        });

        document.addEventListener('click', function (event) {
            if (!suggestionsBox.contains(event.target) && event.target !== produtoDescricaoInput) {
                suggestionsBox.style.display = 'none';
            }
        });
    });
</script>
