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
            background-color: transparent;
            color: #28a745;
            border: 1px solid #28a745;
            width: 100%;
            white-space: nowrap;
        }
        .btn-add:hover {
            background-color: #28a745;
            color: white;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .quantity-input {
            width: 80px;
        }
        .codigo-input {
            width: 90px;
        }
        .select-local + .select-local {
            margin-top: 4px;
        }
    </style>

<div style="border: 1px solid gray; border-radius: 10px; padding:20px">

    <h6 class="mb-3">Clique em salvar para finalizar o processo.</h6>

    <div class="row">
        <div class="col-md-12">
            <div class="table-panel">
                <div class="alert alert-warning">
                    <strong>Nota:</strong> Nos casos de justificativas diferentes para cada produto, os registros ficarão separados.
                </div>
                <x-admin.form method="POST" save-route="admin.movimentacoes-estoque.save" back-route="admin.movimentacoes-estoque.index" submit-title="Salvar Movimentações">
                    @csrf
                    <input type="hidden" name="transferencia" value="{{ $transferencia }}">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Movimentação</th>
                                @if (!$transferencia)
                                    <th>Local Estoque</th>
                                    <th>Categoria</th>
                                @else
                                    <th>Estoque Origem</th>
                                    <th>Estoque Destino</th>
                                @endif
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Justificativa</th>
                                @if (!$transferencia)
                                    <th>Valor Unitário</th>
                                @endif
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            <tr>
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
                                    <td>
                                        <select id="local_categoria" class="form-control select-categoria" data-sub="#local_estoque_id">
                                            <option value="">Selecione...</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="local_estoque_id" id="local_estoque_id" class="form-control" disabled>
                                            <option value="">Selecione a categoria</option>
                                        </select>
                                    </td>
                                @else
                                    <td>
                                        <select id="origem_categoria" class="form-control select-categoria select-local" data-sub="#estoque_origem_id">
                                            <option value="">Local Estoque...</option>
                                        </select>
                                        <select name="estoque_origem_id" id="estoque_origem_id" class="form-control select-local" disabled>
                                            <option value="">Categoria...</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select id="destino_categoria" class="form-control select-categoria select-local" data-sub="#estoque_destino_id">
                                            <option value="">Local Estoque...</option>
                                        </select>
                                        <select name="estoque_destino_id" id="estoque_destino_id" class="form-control select-local" disabled>
                                            <option value="">Categoria...</option>
                                        </select>
                                    </td>
                                @endif
                                <td>
                                    <input type="text" id="codigo_interno_busca" class="form-control codigo-input" autocomplete="off" placeholder="Código" inputmode="numeric">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="text" name="produto_descricao" id="produto_descricao_input" class="form-control" autocomplete="off" placeholder="Nome">
                                        <input type="hidden" name="produto_id" id="produto_id">
                                        <div id="produto_suggestions" class="dropdown-menu"></div>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </td>
                                <td><input type="text" inputmode="decimal" class="form-control quantity-input" id="quantidade" name="quantidade" placeholder="Quantidade" value="1" data-fracionaria="0"></td>
                                <td><input type="text" class="form-control" id="justificativa" name="justificativa" placeholder="Justificativa"></td>
                                @if (!$transferencia)
                                    <td><input type="text" class="form-control" id="valor_unitario" name="valor_unitario" placeholder="Automático" inputmode="decimal" style="min-width: 90px;"></td>
                                @endif
                                <td><button type="button" class="btn btn-add mt-2" id="add-product">+ Adicionar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </x-admin.form>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Script de busca de produtos com autocomplete -->
@php
    // Hierarquia de locais (categoria pai -> sub-locais folha) para os selects encadeados.
    // Montado aqui pois @json() não aceita vírgulas na expressão (interpreta como flags do json_encode).
    $locaisEstoqueJson = $locaisEstoque->map(fn ($l) => [
        'id' => $l->id,
        'nome' => $l->nome,
        'children' => $l->children->map(fn ($c) => ['id' => $c->id, 'nome' => $c->nome])->values(),
    ])->values();
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const LOCAIS = @json($locaisEstoqueJson);

        const produtoDescricaoInput = document.getElementById('produto_descricao_input');
        const produtoIdInput = document.getElementById('produto_id');
        const codigoInput = document.getElementById('codigo_interno_busca');
        const suggestionsBox = document.getElementById('produto_suggestions');
        const addProductButton = document.getElementById('add-product');
        const productTableBody = document.getElementById('product-table-body');
        const tipoMovimentoInput = document.getElementById('tipo_movimento');
        const transferencia = document.querySelector('input[name="transferencia"]').value == '1';

        if (!produtoDescricaoInput || !produtoIdInput || !suggestionsBox || !addProductButton || !productTableBody) {
            return;
        }

        // ----- Selects encadeados (categoria -> sub-local) -----
        document.querySelectorAll('.select-categoria').forEach(catSelect => {
            LOCAIS.forEach(l => catSelect.add(new Option(l.nome, l.id)));

            catSelect.addEventListener('change', function () {
                const subSelect = document.querySelector(this.dataset.sub);
                subSelect.innerHTML = '';
                const categoria = LOCAIS.find(l => l.id == this.value);

                if (!categoria) {
                    subSelect.add(new Option('Selecione a categoria', ''));
                    subSelect.disabled = true;
                    return;
                }

                // Categoria sem filhos: ela própria é o local selecionável
                const folhas = categoria.children.length ? categoria.children : [categoria];
                folhas.forEach(f => subSelect.add(new Option(f.nome, f.id)));
                subSelect.disabled = false;
            });
        });

        function textoLocalSelecionado(subSelectId) {
            const subSelect = document.getElementById(subSelectId);
            const catSelect = document.querySelector(`.select-categoria[data-sub="#${subSelectId}"]`);
            const catNome = catSelect && catSelect.value ? catSelect.options[catSelect.selectedIndex].text : '';
            const subNome = subSelect && subSelect.value ? subSelect.options[subSelect.selectedIndex].text : '';
            return catNome && subNome !== catNome ? `${catNome} › ${subNome}` : subNome;
        }

        // ----- Valor unitário automático (preço do produto conforme o tipo) -----
        function preencherValorAutomatico() {
            const valorInput = document.getElementById('valor_unitario');
            if (!valorInput || transferencia) return;

            const tipo = tipoMovimentoInput.value;
            const preco = tipo === 'saida' ? produtoIdInput.dataset.precoVenda : produtoIdInput.dataset.precoCusto;
            valorInput.value = preco ? parseFloat(preco).toFixed(2).replace('.', ',') : '';
        }

        if (!transferencia) {
            tipoMovimentoInput.addEventListener('change', preencherValorAutomatico);
        }

        // ----- Quantidade inteira/fracionada conforme a unidade do produto -----
        const quantidadeInputEl = document.getElementById('quantidade');

        function aplicarRestricaoQuantidade(fracionaria) {
            quantidadeInputEl.dataset.fracionaria = fracionaria ? '1' : '0';
            quantidadeInputEl.step = fracionaria ? '0.01' : '1';
            if (!fracionaria) {
                const valorInteiro = parseInt(quantidadeInputEl.value, 10);
                quantidadeInputEl.value = isNaN(valorInteiro) ? '1' : String(valorInteiro);
            }
        }

        quantidadeInputEl.addEventListener('input', function () {
            const fracionaria = quantidadeInputEl.dataset.fracionaria === '1';
            let valor = quantidadeInputEl.value;
            // Remove qualquer caractere que não seja dígito (e um único ponto decimal se fracionária)
            valor = fracionaria
                ? valor.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1')
                : valor.replace(/[^\d]/g, '');
            if (valor !== quantidadeInputEl.value) quantidadeInputEl.value = valor;
        });

        // ----- Auto-preenchimento do local quando o produto tem estoque em 1 único local -----
        function autoPreencherLocal(estoquesLocais) {
            if (transferencia || !estoquesLocais || estoquesLocais.length !== 1) return;

            const unico = estoquesLocais[0];
            const catSelect = document.getElementById('local_categoria');
            const subSelect = document.getElementById('local_estoque_id');
            if (!catSelect || !subSelect) return;

            const parentId = unico.local_estoque_parent_id ?? unico.local_estoque_id;
            catSelect.value = parentId;
            catSelect.dispatchEvent(new Event('change'));
            subSelect.value = unico.local_estoque_id;
        }

        function selecionarProduto(produto) {
            produtoDescricaoInput.value = produto.descricao;
            produtoIdInput.value = produto.id;
            produtoIdInput.dataset.precoCusto = produto.preco_custo ?? '';
            produtoIdInput.dataset.precoVenda = produto.preco_venda ?? '';
            codigoInput.value = produto.codigo_interno ?? '';
            suggestionsBox.style.display = 'none';
            aplicarRestricaoQuantidade(!!produto.unidade_fracionaria);
            autoPreencherLocal(produto.estoques_locais);
            preencherValorAutomatico();
        }

        // ----- Busca de produtos -----
        function localParaFiltro() {
            // Entrada busca em todos os produtos; saída/perda filtram pelo sub-local,
            // transferência pelo sub-local de origem
            if (transferencia) {
                const origem = document.getElementById('estoque_origem_id');
                return origem ? origem.value : '';
            }
            if (tipoMovimentoInput.value === 'entrada') return '';
            const local = document.getElementById('local_estoque_id');
            return local ? local.value : '';
        }

        function buscarProdutos(query, callback) {
            const params = new URLSearchParams({ query: query });
            const localId = localParaFiltro();
            if (localId) params.append('local_estoque_id', localId);

            fetch(`/admin/produtos/search?${params.toString()}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(callback)
                .catch(error => console.error('Fetch error:', error));
        }

        produtoDescricaoInput.addEventListener('input', function () {
            const query = this.value;

            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }

            buscarProdutos(query, data => {
                suggestionsBox.innerHTML = '';
                if (!data.length) {
                    const vazio = document.createElement('span');
                    vazio.classList.add('dropdown-item', 'text-muted');
                    vazio.textContent = 'Nenhum produto encontrado neste local.';
                    suggestionsBox.appendChild(vazio);
                } else {
                    data.forEach(produto => {
                        const suggestionItem = document.createElement('a');
                        suggestionItem.classList.add('dropdown-item');
                        suggestionItem.textContent = (produto.codigo_interno ? `${produto.codigo_interno} — ` : '') + produto.descricao;
                        suggestionItem.dataset.produto = JSON.stringify(produto);
                        suggestionsBox.appendChild(suggestionItem);
                    });
                }
                suggestionsBox.style.display = 'block';
            });
        });

        // Busca por código interno exato (Enter ou ao sair do campo)
        function buscarPorCodigo() {
            const codigo = codigoInput.value.trim();
            if (!codigo) return;

            buscarProdutos(codigo, data => {
                const exato = data.find(p => String(p.codigo_interno) === codigo);
                if (exato) {
                    selecionarProduto(exato);
                } else {
                    produtoIdInput.value = '';
                    codigoInput.classList.add('missing-to-checkin');
                }
            });
        }

        codigoInput.addEventListener('change', buscarPorCodigo);
        codigoInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
                buscarPorCodigo();
            }
        });

        suggestionsBox.addEventListener('click', function (event) {
            if (event.target.classList.contains('dropdown-item') && event.target.dataset.produto) {
                selecionarProduto(JSON.parse(event.target.dataset.produto));
            }
        });

        document.addEventListener('click', function (event) {
            if (!suggestionsBox.contains(event.target) && event.target !== produtoDescricaoInput) {
                suggestionsBox.style.display = 'none';
            }
        });

        let movimentacaoIndex = 0;

        // Submit só habilita depois de adicionar ao menos uma linha
        const submitButton = document.querySelector('form.edit-form button[type="submit"]');
        const editForm = document.querySelector('form.edit-form');

        // Enter em qualquer campo da linha tenta adicionar o produto em vez de
        // submeter o formulário (e perder a linha em digitação)
        if (editForm) {
            editForm.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && event.target !== submitButton) {
                    event.preventDefault();
                    addProductButton.click();
                }
            });
        }
        function atualizarEstadoSubmit() {
            const temLinhas = productTableBody.querySelectorAll('input[name^="movimentacoes["]').length > 0;
            if (submitButton) {
                submitButton.disabled = !temLinhas;
                submitButton.title = temLinhas ? '' : 'Adicione produtos à lista primeiro';
            }
        }
        atualizarEstadoSubmit();

        addProductButton.addEventListener('click', function () {
            const localEstoqueInput = document.querySelector('[name="local_estoque_id"]');
            const estoqueOrigemInput = document.querySelector('[name="estoque_origem_id"]');
            const estoqueDestinoInput = document.querySelector('[name="estoque_destino_id"]');
            const quantidadeInput = document.querySelector('[name="quantidade"]');
            const valorUnitarioInput = document.querySelector('[name="valor_unitario"]');
            const justificativaInput = document.querySelector('[name="justificativa"]');

            const messages = [];
            let firstInvalidField = null;

            if (!produtoIdInput.value.trim()) {
                messages.push('Selecione o produto na lista de sugestões (por nome ou código).');
                produtoDescricaoInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = produtoDescricaoInput;
            }

            if (!transferencia && !localEstoqueInput.value.trim()) {
                messages.push('Selecione a categoria e o sub-local de estoque.');
                localEstoqueInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = localEstoqueInput;
            }

            if (transferencia) {
                if (!estoqueOrigemInput.value.trim() || !estoqueDestinoInput.value.trim()) {
                    messages.push('Selecione origem e destino da transferência.');
                    if (!firstInvalidField) firstInvalidField = estoqueOrigemInput;
                } else if (estoqueOrigemInput.value === estoqueDestinoInput.value) {
                    messages.push('Origem e destino da transferência devem ser diferentes.');
                    if (!firstInvalidField) firstInvalidField = estoqueDestinoInput;
                }
            }

            const fracionaria = quantidadeInput.dataset.fracionaria === '1';
            const formatoValido = fracionaria
                ? /^\d+(\.\d{1,2})?$/.test(quantidadeInput.value.trim())
                : /^\d+$/.test(quantidadeInput.value.trim());

            if (!formatoValido || parseFloat(quantidadeInput.value) <= 0) {
                messages.push(fracionaria
                    ? 'Quantidade inválida — use um número maior que zero (ex.: 1.5).'
                    : 'Quantidade inválida — este produto só aceita números inteiros maiores que zero.');
                quantidadeInput.classList.add('missing-to-checkin', 'zoom-in');
                if (!firstInvalidField) firstInvalidField = quantidadeInput;
            }

            if (messages.length > 0) {
                alert(messages.join('\n'));
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                }

                document.querySelectorAll('.zoom-in').forEach(field => {
                    field.addEventListener('animationend', function () {
                        field.classList.remove('zoom-in');
                    }, { once: true });
                });

                return false;
            }

            const codigoTexto = codigoInput.value.trim() || '—';
            const valorTexto = valorUnitarioInput ? valorUnitarioInput.value : '';

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="movimentacoes[${movimentacaoIndex}][tipo_movimento]" value="${transferencia ? 'transferencia' : tipoMovimentoInput.value}">
                    ${transferencia ? 'Transferência' : tipoMovimentoInput.options[tipoMovimentoInput.selectedIndex].text}
                </td>
                ${!transferencia ? `
                <td>${document.getElementById('local_categoria').options[document.getElementById('local_categoria').selectedIndex].text}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][local_estoque_id]" value="${localEstoqueInput.value}">${localEstoqueInput.options[localEstoqueInput.selectedIndex].text}</td>
                ` : `
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][estoque_origem_id]" value="${estoqueOrigemInput.value}">${textoLocalSelecionado('estoque_origem_id')}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][estoque_destino_id]" value="${estoqueDestinoInput.value}">${textoLocalSelecionado('estoque_destino_id')}</td>
                `}
                <td>${codigoTexto}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][produto_id]" value="${produtoIdInput.value}">${produtoDescricaoInput.value}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][quantidade]" value="${quantidadeInput.value}">${quantidadeInput.value}</td>
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][justificativa]" value="${justificativaInput.value}">${justificativaInput.value}</td>
                ${!transferencia ? `
                <td><input type="hidden" name="movimentacoes[${movimentacaoIndex}][valor_unitario]" value="${valorTexto}">${valorTexto}</td>
                ` : ''}
                <td><button type="button" class="btn btn-danger btn-remove">Remover</button></td>
            `;
            productTableBody.appendChild(newRow);

            // Limpa os campos do produto; mantém local e tipo para agilizar lançamentos em sequência
            produtoDescricaoInput.value = '';
            produtoIdInput.value = '';
            delete produtoIdInput.dataset.precoCusto;
            delete produtoIdInput.dataset.precoVenda;
            codigoInput.value = '';
            quantidadeInput.value = '1';
            quantidadeInput.dataset.fracionaria = '0';
            if (valorUnitarioInput) valorUnitarioInput.value = '';
            justificativaInput.value = '';

            newRow.querySelector('.btn-remove').addEventListener('click', function () {
                newRow.remove();
                atualizarEstadoSubmit();
            });

            movimentacaoIndex++;
            atualizarEstadoSubmit();
        });

        // Remove the missing-to-checkin class on input
        function removeMissingClass(event) {
            event.target.classList.remove('missing-to-checkin');
        }

        document.querySelectorAll('[name="produto_descricao"], #codigo_interno_busca, [name="local_estoque_id"], [name="estoque_origem_id"], [name="estoque_destino_id"], [name="quantidade"], [name="tipo_movimento"], [name="valor_unitario"], [name="justificativa"]').forEach(input => {
            input.addEventListener('input', removeMissingClass);
        });
    });
</script>
