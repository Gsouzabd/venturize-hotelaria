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
    <x-admin.form save-route="admin.produtos.save" back-route="admin.produtos.index" id="produto-form">
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
            <!-- Checkbox para habilitar composição -->
            <x-admin.field cols="6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="possui_composicao" name="possui_composicao" {{ old('possui_composicao', $produto->composicoes()->exists()) ? 'checked' : '' }}>                    <label class="form-check-label" for="possui_composicao">
                        Possui Composição?
                    </label>
                </div>
            </x-admin.field>
        </x-admin.field-group>
        
        <!-- Campos de Composição (escondidos por padrão) -->
        <div id="composicao-section" style="display: none;">
            <x-admin.field-group>
                <x-admin.field cols="12">
                    <x-admin.label label="Composições" />
                    <table class="table" id="composicoes-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($edit && $produto->composicoes()->exists())
                                @foreach($produto->composicoes as $index => $composicao)
                                    <tr>
                                        <td>
                                            <input type="text" name="insumo[{{ $index }}][nome]" class="form-control desc-produto-composicao" value="{{ $composicao->insumo->descricao }}" required>
                                            <input type="hidden" name="insumo[{{ $index }}][produto_id]" class="produto-id" value="{{ $composicao->insumo_id }}">
                                            <input type="hidden" name="insumo[{{ $index }}][unidade]" class="produto-unidade" value="{{ $composicao->insumo->unidade }}">
                                        </td>
                                        <td>
                                            <input type="number" name="insumo[{{ $index }}][quantidade]" class="form-control" value="{{ $composicao->quantidade }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="insumo[{{ $index }}][unidade_display]" class="form-control unidade-display" value="{{ $composicao->insumo->unidade }}" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-composicao-btn">Remover</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div id="produto_suggestions" class="dropdown-menu"></div>
                    <button type="button" id="add-composicao-btn" class="btn btn-primary">Adicionar Composição</button>
                </x-admin.field>
            </x-admin.field-group>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const possuiComposicaoCheckbox = document.getElementById('possui_composicao');
                const composicaoSection = document.getElementById('composicao-section');
        
                // Show or hide the composition section based on the checkbox state
                function toggleComposicaoSection() {
                    if (possuiComposicaoCheckbox.checked) {
                        composicaoSection.style.display = 'block';
                    } else {
                        composicaoSection.style.display = 'none';
                    }
                }
        
                // Initial check
                toggleComposicaoSection();
        
                // Add event listener to the checkbox
                possuiComposicaoCheckbox.addEventListener('change', toggleComposicaoSection);
        
                // JavaScript for adding and removing compositions
                const addComposicaoBtn = document.getElementById('add-composicao-btn');
                const composicoesTableBody = document.querySelector('#composicoes-table tbody');
                const suggestionsBox = document.getElementById('produto_suggestions');
                let composicaoIndex = {{ $edit && $produto->composicoes()->exists() ? $produto->composicoes->count() : 0 }};
                let activeInput = null;
        
                addComposicaoBtn.addEventListener('click', function () {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <input type="text" name="insumo[${composicaoIndex}][nome]" class="form-control desc-produto-composicao" required>
                            <input type="hidden" name="insumo[${composicaoIndex}][produto_id]" class="produto-id">
                            <input type="hidden" name="insumo[${composicaoIndex}][unidade]" class="produto-unidade">
                        </td>
                        <td>
                            <input type="number" name="insumo[${composicaoIndex}][quantidade]" class="form-control" required>
                        </td>
                        <td>
                            <input type="text" name="insumo[${composicaoIndex}][unidade_display]" class="form-control unidade-display" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-composicao-btn">Remover</button>
                        </td>
                    `;
                    composicoesTableBody.appendChild(row);
                    composicaoIndex++;
                });
        
                document.addEventListener('focusin', function (event) {
                    if (event.target.classList.contains('desc-produto-composicao')) {
                        activeInput = event.target;
                    }
                });
        
                document.addEventListener('input', function (event) {
                    if (event.target.classList.contains('desc-produto-composicao')) {
                        const query = event.target.value;
        
                        if (query.length < 2) {
                            suggestionsBox.style.display = 'none';
                            return;
                        }
        
                        console.log(`Fetching produtos with query: ${query}`);
        
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
                                    suggestionItem.dataset.descricao = produto.descricao;
                                    suggestionItem.dataset.unidade = produto.unidade;
                                    suggestionsBox.appendChild(suggestionItem);
                                });
                                suggestionsBox.style.display = 'block';
                                if (data.length === 0) {
                                    suggestionsBox.style.display = 'none';
                                    const descricaoInput = event.target;
                                    const row = descricaoInput.closest('tr'); // Encontra a linha correspondente
                                    const produtoIdInput = row.querySelector('.produto-id'); // Campo hidden produto_id

                                    // Verifica se o campo produto_id está vazio
                                    if (!produtoIdInput.value) {
                                        descricaoInput.value = ''; // Limpa o campo descrição
                                        alert('O produto digitado nao foi encontrado. Por favor, selecione um produto da lista de sugestões.');
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                            });
                    }
                });
        
                suggestionsBox.addEventListener('click', function (event) {
                    if (event.target.classList.contains('dropdown-item')) {
       
                        const produtoId = event.target.dataset.id;
                        const produtoDescricao = event.target.dataset.descricao;
                        const produtoUnidade = event.target.dataset.unidade;
        
                        const activeRow = activeInput.closest('tr');
                        const activeHiddenInput = activeRow.querySelector('.produto-id');
                        const activeUnidadeInput = activeRow.querySelector('.produto-unidade');
                        const activeUnidadeDisplayInput = activeRow.querySelector('.unidade-display');
        
                        activeInput.value = produtoDescricao;
                        activeHiddenInput.value = produtoId;
                        activeUnidadeInput.value = produtoUnidade;
                        activeUnidadeDisplayInput.value = produtoUnidade;
        
                        suggestionsBox.style.display = 'none';
                    }
                });
        
                document.addEventListener('click', function (event) {
                    if (!suggestionsBox.contains(event.target) && !event.target.classList.contains('desc-produto-composicao')) {
                        suggestionsBox.style.display = 'none';
                    }
                });
        

                composicoesTableBody.addEventListener('click', function (event) {
                    if (event.target.classList.contains('remove-composicao-btn')) {
                        const row = event.target.closest('tr');
                        row.remove();
                        // Optionally, you can decrement composicaoIndex or handle re-indexing here
                    }
                });
            });
        </script>
        

        <x-admin.field-group>
            <!-- Categoria Produto -->
            <x-admin.field cols="6">
                <x-admin.label label="Categoria" required/>
                <select name="categoria_produto" id="categoria_produto" class="form-control" required>
                    @foreach(\App\Models\Categoria::all() as $categoria)
                        <option value="{{ $categoria->id }}" {{ old('categoria_produto', $produto->categoria_produto ?? '') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nome }}
                        </option>
                    @endforeach
                </select>
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
                <x-admin.select name="ativo" id="ativo" :items="['1' => 'Sim', '0' => 'Não']" :selected-item="old('ativo', $produto->ativo ?? '1')" required/>
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
                <x-admin.select name="produto_servico" id="produto_servico" :items="['produto' => 'Produto', 'servico' => 'Serviço']" :selected-item="old('produto_servico', $produto->produto_servico)" defaultValue="produto" required/>
            </x-admin.field>
        </x-admin.field-group>
    </x-admin.form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('produto-form');
        form.addEventListener('submit', function (e) {
            const categoriaProduto = document.querySelector('select[name="categoria_produto"]');
            const unidade = document.querySelector('select[name="unidade"]');
            const ativo = document.querySelector('select[name="ativo"]');
            const produtoServico = document.querySelector('select[name="produto_servico"]');

            if (!categoriaProduto.value) {
                e.preventDefault();
                alert('Por favor, selecione uma categoria.');
                return false;
            }

            if (!unidade.value) {
                e.preventDefault();
                alert('Por favor, selecione uma unidade.');
                return false;
            }

            if (!ativo.value) {
                e.preventDefault();
                alert('Por favor, selecione se o produto está ativo.');
                return false;
            }

            if (!produtoServico.value) {
                e.preventDefault();
                alert('Por favor, selecione se é um Produto ou Serviço.');
                return false;
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        const possuiComposicaoCheckbox = document.getElementById('possui_composicao');
        const composicaoSection = document.getElementById('composicao-section');
        
        // Mostrar ou esconder a seção de composição com base no estado do checkbox
        possuiComposicaoCheckbox.addEventListener('change', function () {
            if (this.checked) {
                composicaoSection.style.display = 'block';
            } else {
                composicaoSection.style.display = 'none';
            }
        });

        // Exibir a seção se o checkbox já estiver marcado ao carregar a página
        if (possuiComposicaoCheckbox.checked) {
            composicaoSection.style.display = 'block';
        }
    });

</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addComposicaoBtn = document.getElementById('add-composicao-btn');
        const composicoesTableBody = document.querySelector('#composicoes-table tbody');
        const suggestionsBox = document.getElementById('produto_suggestions');
        let composicaoIndex = 0;
        let activeInput = null;

        addComposicaoBtn.addEventListener('click', function () {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="text" name="insumo[${composicaoIndex}][nome]" class="form-control desc-produto-composicao" required>
                    <input type="hidden" name="insumo[${composicaoIndex}][produto_id]" class="produto-id">
                    <input type="hidden" name="insumo[${composicaoIndex}][unidade]" class="produto-unidade">
                </td>
                <td>
                    <input type="number" name="insumo[${composicaoIndex}][quantidade]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="insumo[${composicaoIndex}][unidade_display]" class="form-control unidade-display" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-composicao-btn">Remover</button>
                </td>
            `;
            composicoesTableBody.appendChild(row);
            composicaoIndex++;
        });

        document.addEventListener('focusin', function (event) {
            if (event.target.classList.contains('desc-produto-composicao')) {
                activeInput = event.target;
            }
        });

        document.addEventListener('input', function (event) {
            if (event.target.classList.contains('desc-produto-composicao')) {
                const query = event.target.value;

                if (query.length < 2) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                console.log(`Fetching produtos with query: ${query}`);

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
                            suggestionItem.dataset.descricao = produto.descricao;
                            suggestionItem.dataset.unidade = produto.unidade + ' - ' + produto.unidade_nome;
                            suggestionsBox.appendChild(suggestionItem);
                        });
                        suggestionsBox.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            }
        });

        suggestionsBox.addEventListener('click', function (event) {
            if (event.target.classList.contains('dropdown-item')) {
                const produtoId = event.target.dataset.id;
                const produtoDescricao = event.target.dataset.descricao;
                const produtoUnidade = event.target.dataset.unidade;

                const activeRow = activeInput.closest('tr');
                const activeHiddenInput = activeRow.querySelector('.produto-id');
                const activeUnidadeInput = activeRow.querySelector('.produto-unidade');
                const activeUnidadeDisplayInput = activeRow.querySelector('.unidade-display');

                activeInput.value = produtoDescricao;
                activeHiddenInput.value = produtoId;
                activeUnidadeInput.value = produtoUnidade;
                activeUnidadeDisplayInput.value = produtoUnidade;

                suggestionsBox.style.display = 'none';
            }
        });

        document.addEventListener('click', function (event) {
            if (!suggestionsBox.contains(event.target) && !event.target.classList.contains('desc-produto-composicao')) {
                suggestionsBox.style.display = 'none';
            }
        });

        composicoesTableBody.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-composicao-btn')) {
                const row = event.target.closest('tr');
                row.remove();
                // Optionally, you can decrement composicaoIndex or handle re-indexing here
            }
        });
    });
</script>

@endsection