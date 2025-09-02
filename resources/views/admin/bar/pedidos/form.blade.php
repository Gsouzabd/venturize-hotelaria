@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Pedido')
@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>

    @if($pedido->pedido_apartamento)
        <div class="d-flex justify-content-end">

            <x-admin.edit-btn route="admin.reservas.edit" :noTarget="true" :route-params="['id' => $pedido->reserva->id]" :label="html_entity_decode('<i class=\'fas fa-arrow-left\'></i> Voltar para Reserva')"/>        </div>
            
        <style>
            div#layout-sidenav {
                display: none;
            }
            li.nav-item.dropdown.show {
                display: none;
            }
        </style>
    @endif
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

    @if(request()->query('success') === 'pedido-fechado')
        <div class="alert alert-success">
            Mesa fechada com sucesso!
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
                    <h2 class="card-title text-white">{{ $pedido->pedido_apartamento ? 'Consumo do apartamento'.' - Reserva '.$pedido->reserva->id  : 'Mesa '.$pedido->mesa->numero }} </h2>
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
                                    <input type="number" class="form-control item-quantidade" name="itens_cart[{{ $item->produto_id }}][quantidade]" value="{{ $item->quantidade }}" min="1" readonly>
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
                    <x-admin.form save-route="admin.bar.pedidos.save" submitTitle='<i class="fas fa-note"></i> Salvar Observação'>
                        @csrf
                        @if($edit)
                            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                            <input type="hidden" name="action" value="add-obs">


                        @endif
                        <x-admin.field-group>
                            <x-admin.field cols="12">
                                <x-admin.label label="Observações"/>
                                <x-admin.textarea name="observacoes" id="observacoes" :value="old('observacoes', $pedido->observacoes)" rows="3"/>
                            </x-admin.field>
                        </x-admin.field-group>
                    </x-admin.form>
                    <br/>
                    <x-admin.form save-route="admin.bar.pedidos.save" back-route="admin.bar.pedidos.index" submitTitle='<i class="fas fa-check"></i> Confirmar Itens' id="add-items-form">
                        @csrf
                        @if($edit)
                            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                            <input type="hidden" name="action" value="add-itens">

                        @endif
                        <!-- Seção de Adição de Itens -->
                        <x-admin.field-group>
                            <x-admin.field cols="2">
                                <x-admin.label label="Mesa" required/>
                                <x-admin.select name="mesa_id" id="mesa_id" :value="old('mesa_id', $pedido->mesa_id)"
                                    :items="$mesas->pluck('numero', 'id')" selectedItem="{{ old('mesa_id', $pedido->mesa_id) }}" required disabled/>
                            </x-admin.field>
                            <x-admin.field cols="2">
                                <x-admin.label label="N° Reserva" required/>
                                <x-admin.text name="reserva_id" id="reserva_id" :value="old('reserva_id', $pedido->reserva_id)" readonly/>
                            </x-admin.field>

                            <x-admin.field cols="4">
                                <x-admin.label label="Cliente" required/>
                                <x-admin.select name="cliente_id" id="cliente_id" :value="old('cliente_id', $pedido->cliente_id)"
                                    :items="$clientes->pluck('nome', 'id')" selectedItem="{{ old('cliente_id', $pedido->cliente_id) }}" disabled/>
                                <input type="hidden" name="cliente_id" value="{{ old('cliente_id', $pedido->cliente_id) }}">
                            </x-admin.field>
                            

                            <x-admin.field cols="4">
                                <x-admin.label label="Status" required/>
                                <x-admin.select name="status" id="status" :value="old('status', $pedido->status)"
                                    :items="['aberto' => 'Aberto', 'fechado' => 'Fechado', 'pago' => 'Pago']"
                                    selectedItem="{{ old('status', $pedido->status) }}" required/>
                            </x-admin.field>
                        </x-admin.field-group>




                        <x-admin.field-group>
                            <x-admin.field cols="4">
                                <x-admin.label label="Total" required/>
                                <x-admin.text name="total" id="total" :value="old('total', $pedido->total ?? '0.00')" required/>
                            </x-admin.field>
                            
                            <x-admin.field cols="4">
                                <x-admin.label label="Taxa de Serviço (10%)" required/>
                                <x-admin.text name="service_fee" id="service_fee" :value="old('service_fee', '0.00')" readonly/>
                            </x-admin.field>
                            
                            <x-admin.field cols="4">
                                <x-admin.label label="Total com Taxa de Serviço" required/>
                                <x-admin.text name="total_with_service_fee" id="total_with_service_fee" :value="old('total_with_service_fee', '0.00')" readonly/>
                            </x-admin.field>
                        </x-admin.field-group>



                        <!-- Botão para gerar o cupom parcial -->
                        <div style="display: grid;width: 100%;justify-content: end;">
                            <button type="button" class="btn btn-primary" id="gerarParcialBtn" style="float: right; margin-bottom: 2%">Gerar Cupom Parcial</button>
                        
                            <button type="button" class="btn btn-primary" id="gerarExtratoBtn" style="float: right; margin-bottom: 2%">Gerar Extrato Interno Parcial</button>
    
                        </div>

                        @if($pedido->pedido_apartamento)
                            <br/><br/><br/>
                            <div class="d-flex justify-content-end">
                                <x-admin.edit-btn route="admin.reservas.edit" :route-params="['id' => $pedido->reserva->id]" :label="html_entity_decode('<i class=\'fas fa-arrow-left\'></i> Voltar para Reserva')"/>        
                            </div>
                        @endif
                        <!-- Seção para Adicionar Itens ao Pedido -->
                        <x-admin.field-group style="margin-top: 2%">
                            <x-admin.field cols="12" style="text-align: center;">
                                <x-admin.label label="Itens Temporários (para confirmar)" />
                                <div id="itens-container"></div>
                            </x-admin.field>
                        </x-admin.field-group>

                        @if($pedido->itens->isEmpty())
                            <div class="alert alert-warning mt-3">
                                Atenção! Não existem produtos lançados para essa mesa/pedido!
                            </div>
                        @endif
                    </x-admin.form>
                    <!-- Formulário para fechar o pedido -->
                    <form action="{{ route('admin.bar.pedidos.save') }}" method="POST" class="d-inline" id="fecharPedidoForm">
                        @csrf
                        <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                        <input type="hidden" name="action" value="fechar-pedido">
                        @if( $pedido->status === 'aberto' && $pedido->pedido_apartamento == false)
                            <button type="button" class="btn btn-primary" id="fecharPedidoBtn" style="width: 100%; margin-top: 5%; padding: 1%">Fechar Mesa</button>
                        @else
                            @if($pedido->pedido_apartamento == false)

                                <div class="alert alert-info" style="background: #326dd7; color: white">
                                    A mesa foi <strong>FECHADA</strong> e o pedido atrelado à reserva <strong>{{ $pedido->reserva_id }}</strong>.
                                    <br/><br/>            
                                    <strong>Data da operação: </strong> {{$pedido->updated_at ? timestamp_br($pedido->updated_at) : '' }}
                                </div> 
                            @endif
                      
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal HTML -->
<div id="confirmCloseModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Fechamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Você tem certeza que deseja fechar esta mesa e enviar o pedido para reserva?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="removeServiceFee" name="removeServiceFee">
                    <label class="form-check-label" for="removeServiceFee">
                       <strong> O cliente deseja remover taxa de serviço - 10%? </strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmCloseBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>
</div>
@if (!$pedido->pedido_apartamento) {
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fecharPedidoBtn = document.getElementById('fecharPedidoBtn');

            if (fecharPedidoBtn) {
                fecharPedidoBtn.addEventListener('click', function () {
                    $('#confirmCloseModal').modal('show');
                });
            }
        });
    </script>
}
@endif

<!--! Script para fechar o pedido !-->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const confirmCloseBtn = document.getElementById('confirmCloseBtn');
        const fecharPedidoForm = document.getElementById('fecharPedidoForm');
        const removeServiceFeeCheckbox = document.getElementById('removeServiceFee');



        confirmCloseBtn.addEventListener('click', function () {
            const formData = new FormData(fecharPedidoForm);
            formData.append('removeServiceFee', removeServiceFeeCheckbox.checked);

            fetch('/admin/bar/pedidos/', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.pdf_url) {
                    const printWindow = window.open(data.pdf_url, '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                }
                if (data.success) {
                    window.location.href = window.location.pathname + '?success=pedido-fechado';
                }
            })
            .catch(error => console.error('Error:', error));

            $('#confirmCloseModal').modal('hide');
        });

        const gerarParcialBtn = document.getElementById('gerarParcialBtn');

        gerarParcialBtn.addEventListener('click', function () {
            const pedidoId = {{ $pedido->id }};
            fetch(`/admin/bar/pedidos/${pedidoId}/cupom-parcial`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
                window.URL.revokeObjectURL(url);
            })
            .catch(error => console.error('Error:', error));
        });

        const gerarExtratoBtn = document.getElementById('gerarExtratoBtn');
        gerarExtratoBtn.addEventListener('click', function () {
            const pedidoId = {{ $pedido->id }};
            fetch(`/admin/bar/pedidos/${pedidoId}/extrato-parcial`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank');
                window.URL.revokeObjectURL(url);
            })
            .catch(error => console.error('Error:', error));
        });
    });
</script>


<!-- Modal HTML -->
<div id="cancelModal" class="modal fade" tabindex="-1" role="dialog">
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
                    <input type="hidden" name="action" value="remove-item">
                    <div class="form-group">
                        <label for="cancelQuantity">Quantidade a ser cancelada:</label>
                        <input type="number" class="form-control" id="cancelQuantity" name="quantidade" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="cancelJustification">Justificativa para o cancelamento:</label>
                        <textarea class="form-control" id="cancelJustification" name="justificativa" required></textarea>
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

<div id="unsavedItemsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Itens não salvos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>A mesa possui itens para ser adicionado ao pedido. Por favor, adicione-os ou remova-os antes de sair da página.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
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



                   // Display success notice
                   const successNotice = document.createElement('div');
                    successNotice.classList.add('alert', 'alert-success', 'mt-2');
                    successNotice.textContent = 'Item adicionado na lista de inclusão. Clique em "Confirmar Itens" para salvar.';
                    itensContainer.prepend(successNotice);

                    // scroll to itens-container
                    itensContainer.scrollIntoView({ behavior: 'smooth' });

                    itemRow.querySelector('.remove-item').addEventListener('click', function () {
                        itemRow.remove();
                        updateTotal();
                    });

                    itemRow.querySelector('.item-quantidade').addEventListener('change', function () {
                        updateTotal();
                    });
                }

                updateTotal();
                calculateServiceFee();

            });
        });

        // Adicionar evento de mudança de quantidade aos itens existentes no pedido
        document.querySelectorAll('.item-quantidade').forEach(input => {
            input.addEventListener('change', function () {
                updateTotal();
                calculateServiceFee();
            });
        });

        updateTotal();
        calculateServiceFee();


        function calculateServiceFee() {
            const totalInput = document.getElementById('total');
            const serviceFeeInput = document.getElementById('service_fee');
            const totalWithServiceFeeInput = document.getElementById('total_with_service_fee');
            const total = parseFloat(totalInput.value) || 0;
            var serviceFee = total * 0.10;
            @if($pedido->pedido_apartamento)
                serviceFee = 0;
            @endif
            const totalWithServiceFee = total + serviceFee;


            serviceFeeInput.value = serviceFee.toFixed(2);

            totalWithServiceFeeInput.value = totalWithServiceFee.toFixed(2);

        }

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
                    const printWindow = window.open(data.pdf_url, '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
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

        const cancelForms = document.querySelectorAll('.cancel-form');
        const cancelModal = document.getElementById('cancelModal');
        const cancelForm = document.getElementById('cancelForm');
        const cancelQuantityInput = document.getElementById('cancelQuantity');
        const cancelJustificationInput = document.getElementById('cancelJustification');
        const cancelPedidoIdInput = document.getElementById('cancelPedidoId');
        const cancelProdutoIdInput = document.getElementById('cancelProdutoId');
        const confirmCancelButton = document.getElementById('confirmCancel');

        cancelForms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const pedidoId = form.querySelector('input[name="pedido_id"]').value;
                const produtoId = form.querySelector('input[name="itens_cart[0][produto_id]"]').value; // Adjust the selector as needed

                // Set the hidden inputs in the modal form
                cancelPedidoIdInput.value = pedidoId;
                cancelProdutoIdInput.value = produtoId;

                // Show the modal
                $(cancelModal).modal('show');
            });
        });

        confirmCancelButton.addEventListener('click', function () {
            if (cancelForm.checkValidity()) {
                const formData = new FormData(cancelForm);

                // Add the itens_cart data to the formData
                const pedidoId = cancelPedidoIdInput.value;
                const produtoId = cancelProdutoIdInput.value;
                const quantidade = cancelQuantityInput.value;
                const justificativa = cancelJustificationInput.value;

                formData.append('itens_cart[0][produto_id]', produtoId);
                formData.append('itens_cart[0][quantidade]', quantidade);
                formData.append('itens_cart[0][justificativa]', justificativa);

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

                // Hide the modal
                $(cancelModal).modal('hide');
            } else {
                cancelForm.reportValidity();
            }
        });
    });
</script>

<!-- Script para impedir que o usuário saia da página com itens no carrinho -->
<script>
    function checkForUnsavedItems(event) {
        const itensContainer = document.getElementById('itens-container');
        if (itensContainer && itensContainer.children.length > 0) {        event.preventDefault();
            event.stopPropagation();

            // Show the modal
            $('#unsavedItemsModal').modal('show');

        }
    }

    // Handle click event on .sidenav-item a elements
    document.querySelectorAll('.sidenav-item a, .nav-link').forEach(function(link) {

        link.addEventListener('click', checkForUnsavedItems);
       
    });
</script>



@endsection