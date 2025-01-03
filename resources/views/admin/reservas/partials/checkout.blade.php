@php 

    use App\Models\Reserva; 
    use Carbon\Carbon;

@endphp

<div class="tab-pane fade" id="checkout" role="tabpanel" aria-labelledby="checkout-tab">
    @if($reserva->situacao_reserva != 'FINALIZADO')

        <h3>Realizar Checkout</h3>
    @else
        <div class="alert alert-info" style="background: #00a65a; color: white;">
            O status da reserva já foi atualizado para: <strong>{{ $reserva->situacao_reserva }}.</strong>
                <br/><br/>

                <strong>Data da operação: </strong> {{$reserva->checkOut ? timestamp_br($reserva->checkOut->checkout_at) : '' }}</strong>
            <input class="form-check-input" type="hidden" name="situacao_reserva" id="confirmarCheckin" value= {{ $reserva->situacao_reserva }}>
        </div>
    @endif

    <div class="row">
      
        <div class="col-md-6">
            <div id="valor-recebido-div" class="{{$edit ? '' : 'd-none'}}">
                <h5><i class="fas fa-receipt"></i> Recebimentos</h5>
                <!-- Lista de Valores Recebidos -->
                <x-admin.field cols="12">
                    <x-admin.label label="Valores Recebidos"/>
                    
                    @php
                        // Acessa o primeiro pagamento da coleção
                        $pagamento = $reserva->pagamentos->first();

                        // Recupera os valores antigos, se existirem
                        $valoresRecebidosOld = old('valores_recebidos');
                        $metodosPagamentoOld = old('metodos_pagamento');
                        $submetodosPagamentoOld = old('submetodos_pagamento');

                        if ($valoresRecebidosOld && $metodosPagamentoOld && $submetodosPagamentoOld) {
                            // Reconstroi os valores recebidos a partir dos dados antigos
                            $valoresRecebidos = [];
                            foreach ($valoresRecebidosOld as $index => $valor) {
                                $metodo = $metodosPagamentoOld[$index];
                                $submetodo = $submetodosPagamentoOld[$index] ?? '';
                                $key = $submetodo ? "{$metodo}-{$submetodo}" : $metodo;
                                if (isset($valoresRecebidos[$key])) {
                                    $valoresRecebidos[$key] += $valor;
                                } else {
                                    $valoresRecebidos[$key] = $valor;
                                }
                            }
                        } else {
                            // Utiliza os valores do banco de dados
                            $valoresRecebidos = json_decode($pagamento->valores_recebidos ?? '{}', true);
                        }

                        // Determina o método de pagamento selecionado
                        $selectedMetodo = old('metodo_pagamento', $pagamento->metodo_pagamento ?? '');
                    @endphp
                </x-admin.field>
                    <table id="valores-recebidos-table" class="table table-striped" style="border-left: 1px solid #b4b4b4;">
                        <thead>
                            <tr>
                                <th>Valor</th>
                                <th>Método de Pagamento</th>
                                <th>Quarto</th>
                                <TH>Observações</TH>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($valoresRecebidos as $metodo => $valor)
                                @php
                                    // Verifica se o método possui um submétodo
                                    if (preg_match('/^([^-]+)-([^-]+)-(.+)$/', $metodo, $matches)) {
                                        $metodoPrincipal = $matches[1];
                                        $submetodo = $matches[2];
                                        $observacao = $matches[3];
                                    } elseif (preg_match('/^([^-]+)--(.+)$/', $metodo, $matches)) {
                                        $metodoPrincipal = $matches[1];
                                        $observacao = $matches[2];
                                        $submetodo = '';
                                    } elseif (preg_match('/^([^-]+)-(.+)$/', $metodo, $matches)) {
                                        $metodoPrincipal = $matches[1];
                                        $observacao = $matches[2];
                                        $submetodo = '';
                                    } else {
                                        $metodoPrincipal = $metodo;
                                        $submetodo = '';
                                        $observacao = '';
                                    }
                                @endphp
                                <tr>
                                    <td>R$ {{ number_format($valor, 2, ',', '.') }}</td>
                                    <td>{{ $metodoPrincipal }}{{ $submetodo ? ' - ' . $submetodo : '' }}</td>
                                    <td>Quarto {{ $reserva->quarto->numero .' - '.  $reserva->quarto->classificacao}}
                                    <td>{{ $observacao }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>
                                        <input type="hidden" class="valores_recebidos" name="quartos[{{$reserva->quarto_id}}][valores_recebidos][]" value="{{ $valor }}">
                                        <input type="hidden" name="quartos[{{$reserva->quarto_id}}][metodos_pagamento][]" value="{{ $metodoPrincipal }}">
                                        <input type="hidden" name="quartos[{{$reserva->quarto_id}}][observacoes_pagamento][]" value="{{ $submetodo }}">

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <x-admin.field-group>
                        @if($reserva->situacao_reserva != 'FINALIZADO')
                            <!-- Método de Pagamento -->
                            <x-admin.field cols="12">
                                <h5><i class="fa-solid fa-1"></i> Método de Pagamento</h5>
                                <div class="d-flex justify-content-around" id="metodos-pagamento-tabs">
                                    @php
                                        $selectedMetodo = old('metodo_pagamento', $reserva->pagamento->metodo_pagamento ?? '');
                                    @endphp
                                
                                    @foreach($metodosPagamento as $key => $metodo)
                                        <div class="form-check metodo-pagamento">
                                            <input class="form-check-input metodo-principal" type="radio" name="metodo_pagamento" id="metodo_pagamento_{{ $key }}" value="{{ $key }}"
                                                {{ $key == $selectedMetodo ? 'checked' : '' }}>
                                            <label class="form-check-label" for="metodo_pagamento_{{ $key }}">
                                                <i class="{{ $metodo['icon'] ?? '' }}"></i> {{ $metodo['label'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @foreach($metodosPagamento as $key => $metodo)
                                    @if (!empty($metodo['submetodos']))
                                        <div class="submetodos-container" id="submetodos_container_{{ $key }}" style="display: none;">
                                            <div class="submetodos" id="submetodos_{{ $key }}">
                                                <h5><i class="fa-solid fa-2"></i> Modalidade</h5>
                                                <select class="form-control" name="metodo_pagamento_{{ $key }}" id="metodo_pagamento_{{ $key }}">
                                                    @foreach($metodo['submetodos'] as $subkey => $submetodo)
                                                        <option value="{{ $subkey }}" {{ $subkey == $selectedMetodo ? 'selected' : '' }}>
                                                            {{ $submetodo }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                
                                @push('scripts')
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const metodoPrincipalInputs = document.querySelectorAll('.metodo-principal');
                                            metodoPrincipalInputs.forEach(input => {
                                                input.addEventListener('change', function() {
                                                    const selectedMetodo = this.value;
                                                    document.querySelectorAll('.submetodos-container').forEach(submetodosContainer => {
                                                        submetodosContainer.style.display = 'none';
                                                    });
                                                    const selectedSubmetodosContainer = document.getElementById('submetodos_container_' + selectedMetodo);
                                                    if (selectedSubmetodosContainer) {
                                                        selectedSubmetodosContainer.style.display = 'block';
                                                    }
                                                });
                                
                                                // Trigger change event on page load if the input is checked
                                                if (input.checked) {
                                                    input.dispatchEvent(new Event('change'));
                                                }
                                            });
                                        });
                                    </script>
                                @endpush
                            </x-admin.field>
                        @endif
                    </x-admin.field-group>
                
                    @if($reserva->situacao_reserva != 'FINALIZADO')

                        <x-admin.field-group class="d-flex justify-content-center">
                            <div class="row">
                                <!-- Valor Sinal -->
                                <x-admin.field cols="6">
                                    <x-admin.label label="Valor Recebido"/>
                                    <div class="input-group mb-3 mt-2">
                                        <x-admin.text id="valor_recebido" name="valor_recebido" class="form-control"
                                            value="{{ old('valor_recebido', 0) }}" placeholder="Valor Recebido"/>
                                        <div class="input-group-append w-100">
                                            <button class="btn btn-primary" type="button" id="add-valor-recebido">Incluir</button>
                                        </div>
                                    </div>
                                </x-admin.field>
                                <x-admin.field cols="6">
                                    <x-admin.label label="Observações"/>
                                    <div class="input-group mb-3 mt-2">
                                        <x-admin.text id="observacoes_pagamento" name="observacoes_pagamento" class="form-control"
                                            value="" placeholder="Observações"/>
                                    </div>
                                </x-admin.field>
                                
                                <x-admin.field cols="12" style="display: none;">
                                    <x-admin.label label="Selecionar Quarto"/>
                                    <div class="input-group mb-3 mt-2">
                                        <select id="quarto-select" class="form-control">
                                            <option value="{{$reserva->quarto->id}}"> {{$reserva->quarto->numero}} </option>
                                        </select>
                                    </div>
                                </x-admin.field>
                            </div>
                        </x-admin.field-group>
                    
                        <x-admin.field-group>
                            <!-- Campos específicos de acordo com o método de pagamento -->
                            <div id="pixDetails" class="d-none">
                                <x-admin.field cols="12">
                                    <x-admin.label label="Chave PIX"/>
                                    <x-admin.text id="pix_key" name="pix_key" class="form-control"
                                        value="{{ old('pix_key') }}" placeholder="Informe a chave PIX"/>
                                </x-admin.field>
                            </div>
                    
                            <div id="cartaoCreditoDetails" class="d-none">
                                <x-admin.field cols="6">
                                    <x-admin.label label="Número do Cartão"/>
                                    <x-admin.text id="numero_cartao" name="numero_cartao" class="form-control"
                                        value="{{ old('numero_cartao') }}" placeholder="Informe o número do cartão de crédito"/>
                                </x-admin.field>
                    
                                <x-admin.field cols="6">
                                    <x-admin.label label="Nome no Cartão"/>
                                    <x-admin.text id="nome_cartao" name="nome_cartao" class="form-control"
                                        value="{{ old('nome_cartao') }}" placeholder="Informe o nome impresso no cartão"/>
                                </x-admin.field>
                            </div>
                        </x-admin.field-group>
                    @else
                        <button class="btn btn-primary" type="button" id="add-valor-recebido" style="display: none;">Incluir</button>
                    @endif
       
                </div>
        </div>
        <div class="col-md-6">
            <h5><i class="fas fa-receipt"></i> Valores</h5>

            <x-admin.field-group id="legenda-pagamento">
                <x-admin.field cols="12" id="valor-reserva">
                    <table id="total-reserva-table" class="table table-striped" style="border-left: 1px solid #b4b4b4;">
                        <thead>
                            <tr>
                                <th>Origem</th>
                                <th></th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="d-flex justify-content-between">
                                <td>Reserva - Quarto {{ $reserva->quarto->numero .' - '.  $reserva->quarto->classificacao}}</td>
                                <td></td>
                                <td>R$ {{ number_format($reserva->total, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Consumo</td>
                                <td></td>
                                <td>R$ {{ number_format($totalConsumo, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Consumo - Taxa de Serviço</td>
                                <td>
                                    @if ($reserva->remover_taxa_servico == 1)
                                       <strong> Cliente optou por não pagar a taxa de serviço</strong>
                                    @else
                                        <a href="{{ route('admin.reserva.removerTaxaServico', ['id' => $reserva->id]) }}" style="display:inline;" id="removerTaxaServico">
                                            Remover Taxa de Serviço
                                        </a>
                                    @endif
                                </td>         
                               <td>

                                        R$ {{ number_format($totalTaxaServicoConsumo, 2, ',', '.') }}</td>
                            </tr>
                            
                        </tbody>
                    </table>

                </x-admin.field>
            
                <!-- Valor Pago -->
                <x-admin.field cols="3" id="total-pago">
                    <x-admin.label label=' <i class="fas fa-handshake"></i> Valor Pago' />
                    <x-admin.text id="valor_pago" name="valor_pago" class="form-control"
                        readonly/>
                </x-admin.field>

                <!-- Valor Total -->
                <x-admin.field cols="3" id="valor-total">
                    <x-admin.label label='<i class="fas fa-cube"></i> Valor Total' />
                    <div class="input-group">
                        <x-admin.text id="total" name="total" class="form-control"
                            value="{{ old('total', ($totalCheckout ?? ($reserva->total ?? 0) ) ) }}" placeholder="Valor total da reserva" readonly/>
                    </div>
                </x-admin.field>

            
                <!-- Valor Pendente -->
                <x-admin.field cols="3" id="valor-pendente">
                    <x-admin.label label='<i class="fas fa-exclamation-circle"></i> Valor Pendente' />
                    <x-admin.text id="valor_pendente" name="valor_pendente" class="form-control"
                        readonly/>        
                </x-admin.field>
            
                <!-- Status do Pagamento -->
                <x-admin.field cols="12">
                    <x-admin.label label="Status do Pagamento"/>
                    <x-admin.select name="status_pagamento" id="status_pagamento" class="form-control"
                        :items="['PAGO' => 'Pago', 'PARCIAL' => 'Parcialmente Pago', 'PENDENTE' => 'Pendente']"
                        selectedItem="{{ old('status_pagamento', $reserva->pagamento->status_pagamento ?? 'PENDENTE') }}" />
                </x-admin.field>
            </x-admin.field-group>

            <input type="hidden" name="confirmCheckout" id="confirmCheckout" value="">

            @if($reserva->situacao_reserva != 'FINALIZADO')

                <div class="d-flex justify-content-center mt-3 w-100">
                    <button id="confirmButton" class="btn btn-success w-100" disabled data-toggle="tooltip" data-placement="top" title="O botão será habilitado quando o valor pendente estiver zerado">
                        Confirmar Checkout
                    </button>
                </div>
            @endif

        </div>


    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const metodoPrincipalInputs = document.querySelectorAll('.metodo-principal');
        metodoPrincipalInputs.forEach(input => {
            input.addEventListener('change', function() {
                const selectedMetodo = this.value;
                document.querySelectorAll('.submetodos-container').forEach(submetodosContainer => {
                    submetodosContainer.style.display = 'none';
                });
                const selectedSubmetodosContainer = document.getElementById('submetodos_container_' + selectedMetodo);
                if (selectedSubmetodosContainer) {
                    selectedSubmetodosContainer.style.display = 'block';
                }
            });

            // Trigger change event on page load if the input is checked
            if (input.checked) {
                input.dispatchEvent(new Event('change'));
            }
        });

           // Initialize Bootstrap tooltips
           $('[data-toggle="tooltip"]').tooltip();
            
            const confirmButton = document.getElementById('confirmButton');
    
            function checkPendingAmount() {
                const valorPendente = parseFloat(valorPendenteInput.value.replace(',', '.')) || 0;
                
                if (valorPendente === 0) {
                    confirmButton.removeAttribute('disabled');
                    confirmButton.setAttribute('title', 'Clique para confirmar o checkout');
                } else {
                    confirmButton.setAttribute('disabled', 'disabled');
                    confirmButton.setAttribute('title', 'O botão será habilitado quando o valor pendente estiver zerado');
                }
                // Refresh tooltip
                $('[data-toggle="tooltip"]').tooltip('dispose').tooltip();
            }
    

    

            const addValorRecebidoButton = document.getElementById('add-valor-recebido');
            const valorRecebidoInput = document.getElementById('valor_recebido');
            const valoresRecebidosTable = document.getElementById('valores-recebidos-table').querySelector('tbody');
            const valorPagoInput = document.getElementById('valor_pago');
            const valorTotalInput = document.getElementById('total');
            const valorPendenteInput = document.getElementById('valor_pendente');
            const statusPagamentoSelect = document.querySelector('select[name="status_pagamento"]');
        
function atualizarValores() {
    let totalPago = 0;

    document.querySelectorAll('input.valores_recebidos').forEach(function (input) {
        const rawValue = input.value.trim();
        console.log('Valor bruto:', rawValue);

        // Corrigir separadores:
        // 1. Remover separadores de milhar (pontos não precedidos por vírgulas ou decimais)
        // 2. Substituir vírgula por ponto para transformar no formato numérico
        const valor = parseFloat(
            rawValue
                .replace(/\.(?=\d{3}(,|$))/g, '') // Remove separadores de milhar
                .replace(',', '.') // Substitui vírgula por ponto
        );
        console.log('Valor convertido:', valor);

        if (!isNaN(valor)) {
            totalPago += valor;
        }
    });

    console.log('Total Pago:', totalPago);

    // Atualizar o campo de valor pago
    valorPagoInput.value = totalPago.toFixed(2).replace('.', ',');

    // Corrigir a conversão do valor total
    const valorTotal = parseFloat(
        valorTotalInput.value.replace(/\.(?=\d{3}(,|$))/g, '').replace(',', '.')
    ) || 0;
    console.log('Valor Total:', valorTotal);

    // Calcular e atualizar valor pendente
    const valorPendente = Math.max(0, valorTotal - totalPago); // Evitar valores negativos
    valorPendenteInput.value = valorPendente.toFixed(2).replace('.', ',');

    console.log('Valor Pendente:', valorPendente);

    // Atualizar status de pagamento
    if (totalPago >= valorTotal) {
        statusPagamentoSelect.value = 'PAGO';
    } else if (totalPago > 0) {
        statusPagamentoSelect.value = 'PARCIAL';
    } else {
        statusPagamentoSelect.value = 'PENDENTE';
    }

    // Atualizar botão de confirmação
    checkPendingAmount();
}

        
            addValorRecebidoButton.addEventListener('click', function () {
                const valor = parseFloat(valorRecebidoInput.value.replace(',', '.'));
                if (!document.querySelector('input[name="metodo_pagamento"]:checked')) {
                    alert('Selecione um método de pagamento');
                    return;
                }
                const metodoPagamento = document.querySelector('input[name="metodo_pagamento"]:checked').value;
                const submetodoPagamentoSelect = document.querySelector(`#submetodos_container_${metodoPagamento} select`);
                const submetodoPagamento = submetodoPagamentoSelect ? submetodoPagamentoSelect.value : '';
                const submetodoPagamentoLabel = submetodoPagamentoSelect ? submetodoPagamentoSelect.options[submetodoPagamentoSelect.selectedIndex].text : '';
                const observacoes_pagamento = document.getElementById('observacoes_pagamento').value;
                const quartoSelect = document.getElementById('quarto-select');
                const quartoId = quartoSelect.value;
                const quartoLabel = quartoSelect.options[quartoSelect.selectedIndex].text;
            
                if (!quartoId) {
                    alert('Selecione um quarto');
                    return;
                }
            
                if (!isNaN(valor) && valor > 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>R$ ${valor.toFixed(2).replace('.', ',')}</td>
                        <td>${metodoPagamento} ${submetodoPagamento ? ' - ' + submetodoPagamentoLabel : ''}</td>
                        <td>${quartoLabel}</td>
                        <td>${observacoes_pagamento}</td>
                        <td>
                            <button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>
                            <input type="hidden" class="valores_recebidos" name="quartos[${quartoId}][valores_recebidos][]" value="${valor.toFixed(2)}">
                            <input type="hidden" name="quartos[${quartoId}][metodos_pagamento][]" value="${metodoPagamento}">
                            <input type="hidden" name="quartos[${quartoId}][submetodos_pagamento][]" value="${submetodoPagamento}">
                            <input type="hidden" name="quartos[${quartoId}][observacoes_pagamento][]" value="${observacoes_pagamento}">
                        </td>
                    `;
                    valoresRecebidosTable.appendChild(row);
                    valorRecebidoInput.value = '';
            
                    atualizarValores();
            
                    row.querySelector('.remove-valor-recebido').addEventListener('click', function () {
                        row.remove();
                        atualizarValores();
                    });
                }
            });
        
            document.querySelectorAll('.remove-valor-recebido').forEach(function (button) {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');
                    row.remove();
                    atualizarValores();
                });
            });
        
            atualizarValores();

        document.querySelectorAll('.remove-valor-recebido').forEach(function (button) {
            button.addEventListener('click', function () {
                const row = this.closest('tr');
                row.remove();
                atualizarValores();
            });
        });

        atualizarValores();

        const confirmCheckoutInput = document.getElementById('confirmCheckout');
        const checkoutForm = document.getElementById('checkoutForm');

        // Set hidden input value and submit form when confirm button is clicked
        confirmButton.addEventListener('click', function () {
            confirmCheckoutInput.value = 'true';
        });
    });
</script>

