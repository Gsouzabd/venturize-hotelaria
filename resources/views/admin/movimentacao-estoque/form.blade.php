@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Movimentação')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
<div class="container has-sidebar" >    
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
    <!-- Informações do Produto -->
    <style>
        .form-panel {
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .table-panel {
            margin-top: 20px;
            padding: 20px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .panel-header {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .btn-add {
            background-color: #28a745;
            color: white;
            width: 100%;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .checkbox-group label {
            margin-left: 5px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .quantity-input {
            width: 80px;
        }
    </style>
</head>
<div style="border: 1px solid gray; border-radius: 10px; padding:20px">

    <h6 class="mb-3">Clique em salvar para finalizar o processo.</h6>

    <!-- Linha principal com duas colunas: uma para o formulário e outra para a tabela -->
    <div class="row">
        <!-- Coluna de Tabela -->
        <div class="col-md-12">
            <div class="table-panel">
                <div class="alert alert-warning">
                    <strong>Nota:</strong> Nos casos de justificativas diferentes para cada produto, os registros ficarão separados.
                </div>
                <x-admin.form method="POST" save-route="admin.movimentacoes-estoque.save" back-route="admin.movimentacoes-estoque.index">
                    @csrf
                    <input type="hidden" name="transferencia" value="{{ $transferencia }}">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Produto</th>
                                @if (!$transferencia)
                                    <th>Local Estoque</th>
                                @endif
                                @if ($transferencia)
                                    <th>Estoque Origem</th>
                                    <th>Estoque Destino</th>
                                @endif
                                <th>Quantidade</th>
                                <th>Tipo Movimentação</th>
                                @if (!$transferencia)
                                    <th>Valor Unitário</th>
                                @endif
                                <th>Justificativa</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="produto_descricao" id="codigo_produto" class="form-control" autocomplete="off" placeholder="Nome">
                                        <input type="hidden" name="produto_id" id="produto_id">
                                        <div id="produto_suggestions" class="dropdown-menu"></div>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </td>
                                @if (!$transferencia)
                                    <td>
                                        <select name="local_estoque_id" id="local_estoque_id" class="form-control" required>
                                            @foreach($locaisEstoque as $local)
                                                <option value="{{ $local->id }}" {{ old('local_estoque_id', $estoque->local_estoque_id ?? '') == $local->id ? 'selected' : '' }}>
                                                    {{ $local->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endif
                                @if ($transferencia)
                                    <td>
                                        <select name="estoque_origem_id" id="estoque_origem_id" class="form-control" required>
                                            @foreach($locaisEstoque as $local)
                                                <option value="{{ $local->id }}" {{ old('estoque_origem_id', $estoque->estoque_origem_id ?? '') == $local->id ? 'selected' : '' }}>
                                                    {{ $local->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="estoque_destino_id" id="estoque_destino_id" class="form-control" required>
                                            @foreach($locaisEstoque as $local)
                                                <option value="{{ $local->id }}" {{ old('estoque_destino_id', $estoque->estoque_destino_id ?? '') == $local->id ? 'selected' : '' }}>
                                                    {{ $local->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endif
                                <td><input type="number" class="form-control quantity-input" id="quantidade" name="quantidade" placeholder="Quantidade" value="1.00"></td>
                                <td>
                                    @if ($transferencia)
                                        <input type="text" class="form-control" id="tipo_movimento" name="tipo_movimento" value="Transferência" readonly>
                                    @else
                                        <select class="form-control" id="tipo_movimento" name="tipo_movimento">
                                            <option value="entrada" selected>Entrada</option>
                                            <option value="saida">Saída</option>
                                            <option value="perda">Perda</option>
                                        </select>
                                    @endif
                                </td>
                                @if (!$transferencia)
                                    <td><input type="text" class="form-control" id="valor_unitario" name="valor_unitario" placeholder="Valor Unitário" value=""></td>
                                @endif
                                <td><input type="text" class="form-control" id="justificativa" name="justificativa" placeholder="Justificativa"></td>
                                <td><button type="button" class="btn btn-add mt-2" id="add-product">+ Adicionar</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Salvar Movimentações</button>
                </x-admin.form>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Script de busca de produtos com autocomplete -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM fully loaded and parsed');

        const produtoDescricaoInput = document.getElementById('codigo_produto');
        const produtoIdInput = document.getElementById('produto_id');
        const suggestionsBox = document.getElementById('produto_suggestions');
        const addProductButton = document.getElementById('add-product');
        const productTableBody = document.getElementById('product-table-body');
        const transferencia = document.querySelector('input[name="transferencia"]').value == '1';

        if (!produtoDescricaoInput || !produtoIdInput || !suggestionsBox || !addProductButton || !productTableBody) {
            console.error('One or more elements not found:', {
                produtoDescricaoInput,
                produtoIdInput,
                suggestionsBox,
                addProductButton,
                productTableBody
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
                        suggestionItem.textContent = `ID: ${produto.id} - ${produto.descricao}`;
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

        let movimentacaoIndex = 0;

        addProductButton.addEventListener('click', function () {
            const produtoDescricaoInput = document.querySelector('[name="produto_descricao"]');
            const produtoIdInput = document.querySelector('[name="produto_id"]');
            const localEstoqueInput = document.querySelector('[name="local_estoque_id"]');
            const estoqueOrigemInput = document.querySelector('[name="estoque_origem_id"]');
            const estoqueDestinoInput = document.querySelector('[name="estoque_destino_id"]');
            const quantidadeInput = document.querySelector('[name="quantidade"]');
            const tipoMovimentoInput = document.querySelector('[name="tipo_movimento"]');
            const valorUnitarioInput = document.querySelector('[name="valor_unitario"]');
            const justificativaInput = document.querySelector('[name="justificativa"]');

            const messages = [];
            let firstInvalidField = null;
        
            if (!produtoDescricaoInput.value.trim()) {
                messages.push('Código do Produto é obrigatório.');
                produtoDescricaoInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = produtoDescricaoInput;
            }
        
            if (!localEstoqueInput && !estoqueOrigemInput.value.trim()) {
                messages.push('Local de Estoque ou Estoque Origem é obrigatório.');
                if (localEstoqueInput) localEstoqueInput.classList.add('missing-to-checkin', 'zoom-in');
                if (estoqueOrigemInput) estoqueOrigemInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = localEstoqueInput || estoqueOrigemInput;
            }
        
            if (!quantidadeInput.value.trim() || parseFloat(quantidadeInput.value) <= 0) {
                messages.push('Quantidade deve ser maior que zero.');
                quantidadeInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = quantidadeInput;
            }
        
            if (!tipoMovimentoInput.value.trim()) {
                messages.push('Tipo de Movimento é obrigatório.');
                tipoMovimentoInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = tipoMovimentoInput;
            }
        
            if (!transferencia){
                if (!valorUnitarioInput.value.trim() || parseFloat(valorUnitarioInput.value) <= 0) {
                    messages.push('Valor Unitário deve ser maior que zero.');
                    valorUnitarioInput.classList.add('missing-to-checkin', 'zoom-in');
                    if (!firstInvalidField) firstInvalidField = valorUnitarioInput;
                }
            }

        
            if (messages.length > 0) {
                alert(messages.join('\n'));
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                }
        
                // Remove the zoom-in class after the animation completes
                document.querySelectorAll('.zoom-in').forEach(field => {
                    field.addEventListener('animationend', function () {
                        field.classList.remove('zoom-in');
                    }, { once: true });
                });
        
                return false;
            }
        
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][produto_id]" value="${produtoIdInput.value}">${produtoDescricaoInput.value}</td>
                ${localEstoqueInput ? `
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][local_estoque_id]" value="${localEstoqueInput.value}">${localEstoqueInput.options[localEstoqueInput.selectedIndex].text}</td>
                ` : `
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][estoque_origem_id]" value="${estoqueOrigemInput.value}">${estoqueOrigemInput.options[estoqueOrigemInput.selectedIndex].text}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][estoque_destino_id]" value="${estoqueDestinoInput.value}">${estoqueDestinoInput.options[estoqueDestinoInput.selectedIndex].text}</td>
                `}
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][quantidade]" value="${quantidadeInput.value}">${quantidadeInput.value}</td>
                <td>
                    <input type="hidden" name="movimentacoes[${movimentacaoIndex}][tipo_movimento]" value="${transferencia ? 'transferencia' : tipoMovimentoInput.value}">
                    ${transferencia ? 'Transferência' : tipoMovimentoInput.options[tipoMovimentoInput.selectedIndex].text}
                </td>     
                           
                @if (!$transferencia)
                    <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][valor_unitario]" value="${valorUnitarioInput.value}">${valorUnitarioInput.value}</td>
                @endif                
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][justificativa]" value="${justificativaInput.value}">${justificativaInput.value}</td>
                <td><button type="button" class="btn btn-danger btn-remove">Remover</button></td>
            `;
            productTableBody.appendChild(newRow);
        
            // Clear inputs
            produtoDescricaoInput.value = '';
            produtoIdInput.value = '';
            if (localEstoqueInput) localEstoqueInput.value = '';
            if (estoqueOrigemInput) estoqueOrigemInput.value = '';
            if (estoqueDestinoInput) estoqueDestinoInput.value = '';
            quantidadeInput.value = '1.00';
            if(!transferencia) {
                valorUnitarioInput.value = '';

            }
            justificativaInput.value = '';
        
            // Add event listener to remove button
            newRow.querySelector('.btn-remove').addEventListener('click', function () {
                newRow.remove();
            });

            movimentacaoIndex++;
        });
        
        // Remove the missing-to-checkin class on input
        function removeMissingClass(event) {
            event.target.classList.remove('missing-to-checkin');
        }
        
        document.querySelectorAll('[name="produto_descricao"], [name="local_estoque_id"], [name="estoque_origem_id"], [name="estoque_destino_id"], [name="quantidade"], [name="tipo_movimento"], [name="valor_unitario"], [name="justificativa"]').forEach(input => {
            input.addEventListener('input', removeMissingClass);
        });
    });
</script>