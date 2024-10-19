@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Pedido')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
<div class="container-fluid">
    
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

    <div class="row">
        <!-- Sidebar de Categorias -->
        <div class="col-md-4">
            <div class="card bg-blue">
                <div class="card-body">
                    <h4 class="card-title text-center">Categorias</h4>
                    <div role="group" id="categories-group">
                        @foreach($produtosAgrupados as $categoriaId => $produtos)
                            @php
                                $categoriaNome = \App\Models\Categoria::find($categoriaId)->nome;
                            @endphp
                            <button type="button" class="btn btn-danger categoria-btn my-2" data-categoria-id="{{ $categoriaId }}">{{ $categoriaNome }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocos de Produtos -->
        <div class="col-md-8">
            @foreach($produtosAgrupados as $categoriaId => $produtos)
                @php
                    $categoriaNome = \App\Models\Categoria::find($categoriaId)->nome;
                @endphp
                <div class="card produto-bloco" id="categoria-{{ $categoriaId }}" style="display: none;">
                    <div class="card-header bg-danger text-white">
                        {{ $categoriaNome }}
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($produtos as $produto)
                                <li class="list-group-item">
                                    <button class="btn btn-link produto-to-add" data-produto-id="{{ $produto->id }}" data-produto-descricao="{{ $produto->descricao }}" data-produto-preco="{{ $produto->preco_venda }}">
                                        {{ $produto->descricao }} - R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Área de Consumo -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h2 class="card-title text-white">Mesa {{ $pedido->mesa->numero ?? 'N/A' }}</h2>
                </div>
                <div class="card-body">
                    <!-- Exibir itens no carrinho -->
                    <h5>Itens do pedido:</h5>
                    <div id="cart-container" class="mb-4">
                        @foreach($pedido->itens as $item)
                            <div class="cart-item-row row align-items-center mb-2 p-2 border rounded" data-produto-id="{{ $item->produto_id }}" data-produto-preco="{{ $item->produto->preco_venda }}">
                                <div class="col-md-8">
                                    <span class="font-weight-bold">{{ $item->produto->descricao }}</span> - R$ {{ number_format($item->produto->preco_venda, 2, ',', '.') }}
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control item-quantidade" name="itens_cart[{{ $item->produto_id }}][quantidade]" value="{{ $item->quantidade }}" min="1">
                                </div>
                                <div class="col-md-2 text-right">

                                    <form action="{{ route('admin.bar.pedidos.save') }}" method="POST" class="d-inline cancel-form">
                                        @csrf
                                        <input type="hidden" name="action" value="remove-item">
                                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                                        <input type="hidden" name="itens_cart[0][produto_id]" value="{{ $item->produto_id }}">
                                        <button type="submit" class="btn btn-danger btn-sm remove-cart-item">Cancelar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <x-admin.form save-route="admin.bar.pedidos.save" back-route="admin.bar.pedidos.index" submitTitle='<i class="fas fa-check"></i> Confirmar Itens' id="add-items-form">
                        @csrf
                        @if($edit)
                            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                            <input type="hidden" name="action" value="add-itens">

                        @endif
                        <!-- Seção de Adição de Itens -->
                        <x-admin.field-group>
                            <x-admin.field cols="6">
                                <x-admin.label label="Mesa" required/>
                                <x-admin.select name="mesa_id" id="mesa_id" :value="old('mesa_id', $pedido->mesa_id)"
                                    :items="$mesas->pluck('numero', 'id')" selectedItem="{{ old('mesa_id', $pedido->mesa_id) }}" required/>
                            </x-admin.field>

                            <x-admin.field cols="6">
                                <x-admin.label label="Cliente" required/>
                                <x-admin.select name="cliente_id" id="cliente_id" :value="old('cliente_id', $pedido->cliente_id)"
                                    :items="$clientes->pluck('nome', 'id')" selectedItem="{{ old('cliente_id', $pedido->cliente_id) }}" disabled/>
                                <input type="hidden" name="cliente_id" value="{{ old('cliente_id', $pedido->cliente_id) }}">
                            </x-admin.field>
                        </x-admin.field-group>

                        <x-admin.field-group>
                            <x-admin.field cols="6">
                                <x-admin.label label="Status" required/>
                                <x-admin.select name="status" id="status" :value="old('status', $pedido->status)"
                                    :items="['aberto' => 'Aberto', 'fechado' => 'Fechado', 'pago' => 'Pago']"
                                    selectedItem="{{ old('status', $pedido->status) }}" required/>
                            </x-admin.field>

                            <x-admin.field cols="6">
                                <x-admin.label label="Total" required/>
                                <x-admin.text name="total" id="total" :value="old('total', $pedido->total ?? '0.00')" required/>
                            </x-admin.field>
                        </x-admin.field-group>

                        <!-- Seção para Adicionar Itens ao Pedido -->
                        <x-admin.field-group>
                            <x-admin.field cols="12">
                                <x-admin.label label="Itens Temporários (para confirmar)" required/>
                                <div id="itens-container"></div>
                            </x-admin.field>
                        </x-admin.field-group>

                        @if($pedido->itens->isEmpty())
                            <div class="alert alert-warning mt-3">
                                Atenção! Não existem produtos lançados para essa mesa/cupom!
                            </div>
                        @endif
                    </x-admin.form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal HTML -->
<div id="cancelModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelForm">
                    <div class="form-group">
                        <label for="cancelQuantity">Quantidade a ser cancelada:</label>
                        <input type="number" class="form-control" id="cancelQuantity" name="quantidade" min="1" required>
                    </div>
                    <input type="hidden" id="cancelPedidoId" name="pedido_id">
                    <input type="hidden" id="cancelProdutoId" name="produto_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="confirmCancel">Confirmar</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoriaButtons = document.querySelectorAll('.categoria-btn');
        const produtoBlocos = document.querySelectorAll('.produto-bloco');
        const itensContainer = document.getElementById('itens-container');
        const totalInput = document.getElementById('total');

        function updateTotal() {
            let total = 0;

            // Total dos itens existentes no pedido
            document.querySelectorAll('.cart-item-row').forEach(row => {
                const quantidade = parseFloat(row.querySelector('.item-quantidade').value);
                const preco = parseFloat(row.getAttribute('data-produto-preco'));
                total += quantidade * preco;
            });

            // Total dos itens temporários
            document.querySelectorAll('.item-row').forEach(row => {
                const quantidade = parseFloat(row.querySelector('.item-quantidade').value);
                const preco = parseFloat(row.getAttribute('data-produto-preco'));
                total += quantidade * preco;
            });

            totalInput.value = total.toFixed(2);
        }

        categoriaButtons.forEach(button => {
            button.addEventListener('click', function () {
                const categoriaId = this.getAttribute('data-categoria-id');

                produtoBlocos.forEach(bloco => {
                    bloco.style.display = 'none';
                });

                const bloco = document.getElementById(`categoria-${categoriaId}`);
                if (bloco) {
                    bloco.style.display = 'block';
                }
            });
        });

        document.querySelectorAll('.produto-to-add').forEach(button => {
            button.addEventListener('click', function () {
                const produtoId = this.getAttribute('data-produto-id');
                const produtoDescricao = this.getAttribute('data-produto-descricao');
                const produtoPreco = this.getAttribute('data-produto-preco');

                let itemExists = false;
                document.querySelectorAll('.item-row').forEach(row => {
                    if (row.getAttribute('data-produto-id') === produtoId) {
                        itemExists = true;
                        const quantidadeInput = row.querySelector('.item-quantidade');
                        quantidadeInput.value = parseInt(quantidadeInput.value) + 1;
                        updateTotal();
                    }
                });

                if (!itemExists) {
                    const itemRow = document.createElement('div');
                    itemRow.classList.add('item-row', 'd-flex', 'justify-content-between', 'mb-2');
                    itemRow.setAttribute('data-produto-id', produtoId);
                    itemRow.setAttribute('data-produto-preco', produtoPreco);
                    itemRow.innerHTML = `
                        ${produtoDescricao} - R$ ${parseFloat(produtoPreco).toFixed(2).replace('.', ',')}
                        <input type="number" class="item-quantidade" name="itens_temp[${produtoId}][quantidade]" value="1" min="1" style="width: 60px; margin-left: 10px;">
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remover</button>
                        <input type="hidden" name="itens_temp[${produtoId}][produto_id]" value="${produtoId}">
                    `;

                    itensContainer.appendChild(itemRow);

                    itemRow.querySelector('.remove-item').addEventListener('click', function () {
                        itemRow.remove();
                        updateTotal();
                    });

                    itemRow.querySelector('.item-quantidade').addEventListener('change', function () {
                        updateTotal();
                    });
                }

                updateTotal();
            });
        });

        // Adicionar evento de mudança de quantidade aos itens existentes no pedido
        document.querySelectorAll('.item-quantidade').forEach(input => {
            input.addEventListener('change', function () {
                updateTotal();
            });
        });

        updateTotal();

        // Função para enviar o formulário de adicionar itens e abrir o PDF em uma nova aba
        function submitAddItemsForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const pedidoId = form.querySelector('input[name="pedido_id"]').value;

            fetch(`/admin/bar/pedidos/`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': APP_CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.pdf_url) {
                    window.open(data.pdf_url, '_blank');
                }
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Adicionar evento de submit ao formulário de adicionar itens
        const addItemsForm = document.getElementById('add-items-form');
        if (addItemsForm) {
            addItemsForm.addEventListener('submit', submitAddItemsForm);
        }

               // Lógica para o formulário de cancelamento
        const cancelForms = document.querySelectorAll('.cancel-form');
        
        cancelForms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const pedidoId = form.querySelector('input[name="pedido_id"]').value;
                const produtoId = form.querySelector('input[name="itens_cart[0][produto_id]"]').value; // Adjust the selector as needed
        
                // Prompt the user for the quantity to be canceled
                const quantidade = prompt('Digite a quantidade a ser cancelada:');
                if (quantidade === null || quantidade === '' || isNaN(quantidade) || quantidade <= 0) {
                    alert('Quantidade inválida.');
                    return;
                }
        
                // Create a FormData object and append the necessary data
                const formData = new FormData();
                formData.append('action', 'remove-item');
                formData.append('pedido_id', pedidoId);
                formData.append('itens_cart[0][produto_id]', produtoId);
                formData.append('itens_cart[0][quantidade]', quantidade);
        
                fetch('/admin/bar/pedidos/', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': APP_CSRF_TOKEN
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.pdf_url) {
                        window.open(data.pdf_url, '_blank');
                    }
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
</script>



@endsection