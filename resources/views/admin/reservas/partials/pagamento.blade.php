<div class="tab-pane fade" id="pagamento" role="tabpanel" aria-labelledby="pagamento-tab">
    <h3>Detalhes do Pagamento</h3>
    <p>Selecione o método de pagamento e preencha os detalhes abaixo.</p>
    <x-admin.field-group>
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

    <div id="valor-recebido-div" class="d-none">
        <h5><i class="fas fa-receipt"></i> Recebimentos</h5>

        <x-admin.field-group class="d-flex justify-content-center">
            <div class="row">
                <!-- Valor Sinal -->
                <x-admin.field cols="4">
                    <x-admin.label label="Incluir Valor Recebido"/>
                    <div class="input-group mb-3 mt-2">
                        <x-admin.text id="valor_recebido" name="valor_recebido" class="form-control"
                            value="{{ old('valor_recebido', 0) }}" placeholder="Valor Recebido"/>
                        <div class="input-group-append w-100">
                            <button class="btn btn-primary "  type="button" id="add-valor-recebido">Incluir</button>
                        </div>
                    </div>
                </x-admin.field>

                <!-- Lista de Valores Recebidos -->
                <x-admin.field cols="8">
                    <x-admin.label label="Valores Recebidos"/>
                    <table id="valores-recebidos-table" class="table table-striped" style="border-left: 1px solid #b4b4b4;">
                        <thead>
                            <tr>
                                <th>Valor</th>
                                <th>Método de Pagamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('valores_recebidos', $reserva->pagamento->valores_recebidos ?? []))
                                @foreach(old('valores_recebidos', $reserva->pagamento->valores_recebidos ?? []) as $index => $valor)
                                    <tr>
                                        <td>R$ {{ number_format($valor, 2, ',', '.') }}</td>
                                        <td>{{ old('metodos_pagamento.' . $index, $reserva->pagamento->metodos_pagamento[$index] ?? '') }}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>
                                            <input type="hidden" name="valores_recebidos[]" value="{{ $valor }}">
                                            <input type="hidden" name="metodos_pagamento[]" value="{{ old('metodos_pagamento.' . $index, $reserva->pagamento->metodos_pagamento[$index] ?? '') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </x-admin.field>
            </div>
        
        </x-admin.field-group>

    </div>

    <x-admin.field-group id="legenda-pagamento">
        <!-- Valor Total -->
        <x-admin.field cols="3" id="valor-reserva">
            <x-admin.label label='<i class="fas fa-cube"></i> Valor Total' />
            <div class="input-group">
                <x-admin.text id="valor_total" name="valor_total" class="form-control"
                    value="{{ old('valor_total', $reserva->pagamento->valor_total ?? 0) }}" placeholder="Valor total da reserva" readonly/>
            </div>
        </x-admin.field>
    
        <!-- Valor Pago -->
        <x-admin.field cols="3" id="total-pago">
            <x-admin.label label=' <i class="fas fa-handshake"></i> Valor Pago' />
            <x-admin.text id="valor_pago" name="valor_pago" class="form-control"
                value="{{ old('valor_pago', $reserva->pagamento->valor_pago ?? 0) }}" placeholder="Valor já pago" readonly/>
        </x-admin.field>
    
        <!-- Valor Pendente -->
        <x-admin.field cols="3" id="valor-pendente">
            <x-admin.label label='<i class="fas fa-exclamation-circle"></i> Valor Pendente' />
            <x-admin.text id="valor_pendente" name="valor_pendente" class="form-control"
                value="{{ old('valor_pendente', $reserva->pagamento ? ($reserva->pagamento->valor_total - $reserva->pagamento->valor_pago) : 0) }}" placeholder="Valor pendente" readonly/>        </x-admin.field>
    
        <!-- Status do Pagamento -->
        <x-admin.field cols="3">
            <x-admin.label label="Status do Pagamento"/>
            <x-admin.select name="status_pagamento" id="status_pagamento" class="form-control"
                :items="['PAGO' => 'Pago', 'PARCIAL' => 'Parcialmente Pago', 'PENDENTE' => 'Pendente']"
                selectedItem="{{ old('status_pagamento', $reserva->pagamento->status_pagamento ?? 'PENDENTE') }}" />
        </x-admin.field>
    </x-admin.field-group>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addValorRecebidoButton = document.getElementById('add-valor-recebido');
        const valorRecebidoInput = document.getElementById('valor_recebido');
        const valoresRecebidosTable = document.getElementById('valores-recebidos-table').querySelector('tbody');
        const valorPagoInput = document.getElementById('valor_pago');
        const valorTotalInput = document.getElementById('valor_total');
        const valorPendenteInput = document.getElementById('valor_pendente');
        const statusPagamentoSelect = document.querySelector('select[name="status_pagamento"]');

        function atualizarValores() {
            let totalPago = 0;
            document.querySelectorAll('input[name="valores_recebidos[]"]').forEach(function (input) {
                totalPago += parseFloat(input.value);
            });
            valorPagoInput.value = totalPago.toFixed(2).replace('.', ',');

            const valorTotal = parseFloat(valorTotalInput.value.replace(',', '.')) || 0;
            const valorPendente = valorTotal - totalPago;
            valorPendenteInput.value = valorPendente.toFixed(2).replace('.', ',');

            // Atualizar status de pagamento
            if (totalPago >= valorTotal) {
                statusPagamentoSelect.value = 'PAGO';
            } else if (totalPago > 0) {
                statusPagamentoSelect.value = 'PARCIAL';
            } else {
                statusPagamentoSelect.value = 'PENDENTE';
            }
        }

        addValorRecebidoButton.addEventListener('click', function () {
            const valor = parseFloat(valorRecebidoInput.value);
            const metodoPagamento = document.querySelector('input[name="metodo_pagamento"]:checked').value;
            const submetodoPagamentoSelect = document.querySelector(`#submetodos_container_${metodoPagamento} select`);
            const submetodoPagamento = submetodoPagamentoSelect ? submetodoPagamentoSelect.value : '';
            const submetodoPagamentoLabel = submetodoPagamentoSelect ? submetodoPagamentoSelect.options[submetodoPagamentoSelect.selectedIndex].text : '';
        
            if (!isNaN(valor) && valor > 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>R$ ${valor.toFixed(2).replace('.', ',')}</td>
                    <td>${metodoPagamento} ${submetodoPagamento ? ' - ' + submetodoPagamentoLabel : ''}</td>
                    <td>
                        <button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>
                        <input type="hidden" name="valores_recebidos[]" value="${valor}">
                        <input type="hidden" name="metodos_pagamento[]" value="${metodoPagamento}">
                        <input type="hidden" name="submetodos_pagamento[]" value="${submetodoPagamento}">
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
    });
    document.querySelectorAll('input[name="metodo_pagamento"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var metodo = this.value;
            // document.getElementById('pixDetails').classList.add('d-none');
            // document.getElementById('cartaoCreditoDetails').classList.add('d-none');
            document.getElementById('valor-recebido-div').classList.remove('d-none');

            // if (metodo === 'PIX') {
            //     document.getElementById('pixDetails').classList.remove('d-none');
            // } else if (metodo === 'CARTAO_CREDITO') {
            //     document.getElementById('cartaoCreditoDetails').classList.remove('d-none');
            // }
        });
    });

    // Função para observar mudanças na classe do elemento
    function observeClassChanges(element, className, callback) {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'class') {
                    const hasClass = mutation.target.classList.contains(className);
                    if (hasClass) {
                        callback();
                    }
                }
            });
        });

        observer.observe(element, { attributes: true });
    }

    // Observar mudanças na classe do elemento a#pagamento-tab
    const pagamentoTab = document.querySelector('a#pagamento-tab');
    if (pagamentoTab) {
        observeClassChanges(pagamentoTab, 'active', atualizarTotal);
    }

    function atualizarTotal() {
        // Extrair o valor numérico do texto dentro do span
        var valorTotalText = document.getElementById('total-cart-value').innerText;
        var valorTotal = parseFloat(valorTotalText.replace('R$', '').replace('.', '').replace(',', '.'));

        // Atualizar o campo de valor total
        document.getElementById('valor_total').value = valorTotal.toFixed(2);

         // Remover o atributo disabled do botão de envio
         if(document.querySelector('form.edit-form button[type="submit"]:disabled')){
            document.querySelector('form.edit-form button[type="submit"]:disabled').removeAttribute('disabled');

         }
    }
</script>


<script>
    function formatarValoresRecebidos() {
        const valoresRecebidos = document.getElementsByName('valores_recebidos[]');
        const metodosPagamento = document.getElementsByName('metodos_pagamento[]');
        const submetodosPagamento = document.getElementsByName('submetodos_pagamento[]');

        const valoresFormatados = {};

        for (let i = 0; i < valoresRecebidos.length; i++) {
            const chave = `${metodosPagamento[i].value}-${submetodosPagamento[i].value}`;
            const valor = parseFloat(valoresRecebidos[i].value);

            if (!isNaN(valor)) {
                valoresFormatados[chave] = valor;
            }
        }

        const valoresRecebidosInput = document.createElement('input');
        valoresRecebidosInput.type = 'hidden';
        valoresRecebidosInput.name = 'valores_recebidos_formatados';
        valoresRecebidosInput.value = JSON.stringify(valoresFormatados);

        document.forms[0].appendChild(valoresRecebidosInput);
    }

    // Chame a função antes de enviar o formulário
    document.forms[0].addEventListener('submit', formatarValoresRecebidos);
</script>
