{{-- @php dd($reserva->pagamentos) @endphp --}}

<div class="tab-pane fade" id="pagamento" role="tabpanel" aria-labelledby="pagamento-tab">
    <h3>Detalhes do Pagamento</h3>
    <p>Selecione o método de pagamento e preencha os detalhes abaixo.</p>
    <x-admin.field-group>
        <!-- Método de Pagamento -->
        <x-admin.field cols="12">
            <h5><i class="fa-solid fa-1"></i> Método de Pagamento</h5>
            <div class="d-flex justify-content-around" id="metodos-pagamento-tabs">
                @php
                    $selectedMetodo = old('metodo_pagamento', optional($reserva->pagamentos->first())->metodo_pagamento ?? '');
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

    <div id="valor-recebido-div" class="{{$edit ? '' : 'd-none'}}">
        <h5><i class="fas fa-receipt"></i> Recebimentos</h5>

        <x-admin.field-group class="d-flex justify-content-center">
            <div class="row">
                <!-- Valor Sinal -->
                <x-admin.field cols="3">
                    <x-admin.label label="Valor Recebido"/>
                    <div class="input-group mb-3 mt-2">
                        <x-admin.text id="valor_recebido" name="valor_recebido" class="form-control"
                            value="{{ old('valor_recebido', 0) }}" placeholder="Valor Recebido"/>
                        <div class="input-group-append w-100">
                            <button class="btn btn-primary" type="button" id="add-valor-recebido">Incluir</button>
                        </div>
                    </div>
                </x-admin.field>
                
                <x-admin.field cols="3" id="field-quarto-select">
                    <x-admin.label label="Selecionar Quarto"/>
                    <div class="input-group mb-3 mt-2">
                        <select id="quarto-select" class="form-control">
                            <option value="">Selecione um quarto</option>
                        </select>
                    </div>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Observações"/>
                    <div class="input-group mb-3 mt-2">
                        <x-admin.text id="observacoes_pagamento" name="observacoes_pagamento" class="form-control"
                            value="" placeholder="Observações"/>
                    </div>
                </x-admin.field>

                <!-- Lista de Valores Recebidos -->
              <!-- Lista de Valores Recebidos -->
<x-admin.field cols="8">
    <x-admin.label label="Valores Recebidos"/>
    
    @php
        $pagamento = $reserva->pagamentos->first();
        $valoresRecebidosOld = old('valores_recebidos');
        $metodosPagamentoOld = old('metodos_pagamento');
        $submetodosPagamentoOld = old('submetodos_pagamento');
        $observacoesPagamentoOld = old('observacoes_pagamento');

        if ($valoresRecebidosOld && $metodosPagamentoOld && $submetodosPagamentoOld) {
            $valoresRecebidos = [];
            foreach ($valoresRecebidosOld as $index => $valor) {
                $metodo = $metodosPagamentoOld[$index];
                $submetodo = $submetodosPagamentoOld[$index] ?? '';
                $observacao = $observacoesPagamentoOld[$index] ?? '';
                $key = $submetodo ? "{$metodo}-{$submetodo}-observacao:{$observacao}" : $metodo;
                if (isset($valoresRecebidos[$key])) {
                    $valoresRecebidos[$key] += $valor;
                } else {
                    $valoresRecebidos[$key] = $valor;
                }
            }
        } else {
            $valoresRecebidos = $pagamento ? (json_decode($pagamento->valores_recebidos ?? '{}', true) ?: []) : [];
        }
        $selectedMetodo = old('metodo_pagamento', $pagamento->metodo_pagamento ?? '');
    @endphp

    <table id="valores-recebidos-table" class="table table-striped" style="border-left: 1px solid #b4b4b4;">
        <thead>
            <tr>
                <th>Valor</th>
                <th>Método de Pagamento</th>
                <th>Quarto</th>
                <th>Observações</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($valoresRecebidos as $metodo => $valor)  
            @php
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
                    <td>{{ $reserva->tipo_reserva === 'DAY_USE' ? 'Day Use' : ('Quarto ' . ($reserva->quarto->numero ?? '') . ' - ' . ($reserva->quarto->classificacao ?? '')) }}</td>
                    <td>{{ $observacao }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>
                        @if($reserva->tipo_reserva === 'DAY_USE')
                        <input type="hidden" class="valores_recebidos" name="valores_recebidos[]" value="{{ $valor }}">
                        <input type="hidden" name="metodos_pagamento[]" value="{{ $metodoPrincipal }}">
                        <input type="hidden" name="submetodos_pagamento[]" value="{{ $submetodo }}">
                        <input type="hidden" name="observacoes_pagamento[]" value="{{ $observacao }}">
                        @else
                        <input type="hidden" class="valores_recebidos" name="quartos[{{ $reserva->quarto_id }}][valores_recebidos][]" value="{{ $valor }}">
                        <input type="hidden" name="quartos[{{ $reserva->quarto_id }}][metodos_pagamento][]" value="{{ $metodoPrincipal }}">
                        <input type="hidden" name="quartos[{{ $reserva->quarto_id }}][submetodos_pagamento][]" value="{{ $submetodo }}">
                        <input type="hidden" name="quartos[{{ $reserva->quarto_id }}][observacoes_pagamento][]" value="{{ $observacao }}">
                        @endif
                    </td>
                </tr>
            @endforeach
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
                    value="{{ old('valor_total', optional($reserva->pagamentos->first())->valor_total ?? $reserva->total ?? 0) }}" placeholder="Valor total da reserva" readonly/>
            </div>
        </x-admin.field>
    
        <!-- Valor Pago -->
        <x-admin.field cols="3" id="total-pago">
            <x-admin.label label=' <i class="fas fa-handshake"></i> Valor Pago' />
            <x-admin.text id="valor_pago" name="valor_pago" class="form-control"
                 readonly/>
        </x-admin.field>
    
        <!-- Valor Pendente -->
        <x-admin.field cols="3" id="valor-pendente">
            <x-admin.label label='<i class="fas fa-exclamation-circle"></i> Valor Pendente' />
            <x-admin.text id="valor_pendente" name="valor_pendente" class="form-control"
                readonly/>        </x-admin.field>
    
        <!-- Status do Pagamento -->
        <x-admin.field cols="3">
            <x-admin.label label="Status do Pagamento"/>
            <x-admin.select name="status_pagamento" id="status_pagamento" class="form-control"
                :items="['PAGO' => 'Pago', 'PARCIAL' => 'Parcialmente Pago', 'PENDENTE' => 'Pendente']"
                selectedItem="{{ old('status_pagamento', optional($reserva->pagamentos->first())->status_pagamento ?? 'PENDENTE') }}" />
        </x-admin.field>
    </x-admin.field-group>

</div>

<script>
        const addValorRecebidoButton = document.getElementById('add-valor-recebido');
        const valorRecebidoInput = document.getElementById('valor_recebido');
        const valoresRecebidosTable = document.getElementById('valores-recebidos-table').querySelector('tbody');
        const valorPagoInput = document.getElementById('valor_pago');
        const valorTotalInput = document.getElementById('valor_total');
        const valorPendenteInput = document.getElementById('valor_pendente');
        const statusPagamentoSelect = document.querySelector('select[name="status_pagamento"]');
        function atualizarValores() {
            let totalPago = 0;
            document.querySelectorAll('input.valores_recebidos').forEach(function (input) {
                totalPago += parseFloat(input.value);
            });
            valorPagoInput.value = totalPago.toFixed(2).replace('.', ',');

            const valorTotal = parseFloat(valorTotalInput.value.replace(',', '.')) || 0;
            const valorPendente = valorTotal - totalPago;
            console.log(valorPendente, valorTotal, totalPago);
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

        function isDayUse() {
            var sel = document.querySelector('select[name="tipo_reserva"]');
            return sel && sel.value === 'DAY_USE';
        }

        addValorRecebidoButton.addEventListener('click', function () {
            const valor = parseFloat(valorRecebidoInput.value);
            if (!document.querySelector('input[name="metodo_pagamento"]:checked')) {
                alert('Selecione um método de pagamento');
                return;
            }
            const metodoPagamento = document.querySelector('input[name="metodo_pagamento"]:checked').value;
            const submetodoPagamentoSelect = document.querySelector('#submetodos_container_' + metodoPagamento + ' select');
            const submetodoPagamento = submetodoPagamentoSelect ? submetodoPagamentoSelect.value : '';
            const submetodoPagamentoLabel = submetodoPagamentoSelect ? submetodoPagamentoSelect.options[submetodoPagamentoSelect.selectedIndex].text : '';
            const observacoes_pagamento = document.getElementById('observacoes_pagamento').value;

            const dayUse = isDayUse();
            const quartoSelect = document.getElementById('quarto-select');
            let quartoId = '';
            let quartoLabel = 'Day Use';
            if (!dayUse && quartoSelect) {
                quartoId = quartoSelect.value;
                quartoLabel = quartoSelect.options[quartoSelect.selectedIndex].text;
                if (!quartoId) {
                    alert('Selecione um quarto');
                    return;
                }
            }

            if (!isNaN(valor) && valor > 0) {
                const row = document.createElement('tr');
                if (dayUse) {
                    row.innerHTML = '<td>R$ ' + valor.toFixed(2).replace('.', ',') + '</td>' +
                        '<td>' + metodoPagamento + (submetodoPagamento ? ' - ' + submetodoPagamentoLabel : '') + '</td>' +
                        '<td>Day Use</td><td>' + observacoes_pagamento + '</td><td>' +
                        '<button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>' +
                        '<input type="hidden" class="valores_recebidos" name="valores_recebidos[]" value="' + valor + '">' +
                        '<input type="hidden" name="metodos_pagamento[]" value="' + metodoPagamento + '">' +
                        '<input type="hidden" name="submetodos_pagamento[]" value="' + submetodoPagamento + '">' +
                        '<input type="hidden" name="observacoes_pagamento[]" value="' + observacoes_pagamento + '">' +
                        '</td>';
                } else {
                    row.innerHTML = '<td>R$ ' + valor.toFixed(2).replace('.', ',') + '</td>' +
                        '<td>' + metodoPagamento + (submetodoPagamento ? ' - ' + submetodoPagamentoLabel : '') + '</td>' +
                        '<td>' + quartoLabel + '</td><td>' + observacoes_pagamento + '</td><td>' +
                        '<button class="btn btn-danger btn-sm remove-valor-recebido" type="button">Remover</button>' +
                        '<input type="hidden" class="valores_recebidos" name="quartos[' + quartoId + '][valores_recebidos][]" value="' + valor + '">' +
                        '<input type="hidden" name="quartos[' + quartoId + '][metodos_pagamento][]" value="' + metodoPagamento + '">' +
                        '<input type="hidden" name="quartos[' + quartoId + '][submetodos_pagamento][]" value="' + submetodoPagamento + '">' +
                        '<input type="hidden" name="quartos[' + quartoId + '][observacoes_pagamento][]" value="' + observacoes_pagamento + '">' +
                        '</td>';
                }
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

    const pagamentoTab = document.querySelector('a#pagamento-tab');
    function toggleQuartoSelectVisibility() {
        var fieldQuarto = document.getElementById('field-quarto-select');
        if (!fieldQuarto) return;
        var isDayUse = document.querySelector('select[name="tipo_reserva"]') && document.querySelector('select[name="tipo_reserva"]').value === 'DAY_USE';
        fieldQuarto.style.display = isDayUse ? 'none' : '';
    }
    function atualizarTotalDayUse() {
        var dataEntrada = document.querySelector('input[name="data_entrada"]');
        var dataVal = dataEntrada ? dataEntrada.value : '';
        if (!dataVal) return;
        var adultos = parseInt(document.querySelector('input[name="adultos"]').value, 10) || 1;
        var criancasAte7 = parseInt(document.querySelector('input[name="criancas_ate_7"]').value, 10) || 0;
        var criancasMais7 = parseInt(document.querySelector('input[name="criancas_mais_7"]').value, 10) || 0;
        var comCafe = (document.getElementById('com_cafe') && document.getElementById('com_cafe').checked) ? 1 : 0;
        var urlCalcular = '{{ route("admin.reservas.calcular-day-use") }}?data_entrada=' + encodeURIComponent(dataVal) + '&adultos=' + adultos + '&criancas_ate_7=' + criancasAte7 + '&criancas_mais_7=' + criancasMais7 + '&com_cafe=' + comCafe;
        fetch(urlCalcular).then(function(r) { return r.json(); }).then(function(data) {
            var totalEl = document.getElementById('valor_total');
            if (totalEl && data.total !== undefined) totalEl.value = Number(data.total).toFixed(2);
            atualizarValores();
        }).catch(function() {});
    }
    if (pagamentoTab) {
        observeClassChanges(pagamentoTab, 'active', function() {
            toggleQuartoSelectVisibility();
            var tipoSel = document.querySelector('select[name="tipo_reserva"]');
            if (!tipoSel || tipoSel.value !== 'DAY_USE') {
                atualizarTotal();
                gerarQuartosSelect();
            } else {
                var submitBtn = document.querySelector('form.edit-form button[type="submit"]:disabled');
                if (submitBtn) submitBtn.removeAttribute('disabled');
                var valorTotalInput = document.getElementById('valor_total');
                if (valorTotalInput && (valorTotalInput.value === '' || parseFloat(String(valorTotalInput.value).replace(',', '.')) === 0)) {
                    atualizarTotalDayUse();
                }
            }
            atualizarValores();
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleQuartoSelectVisibility();
        var tipoSel = document.querySelector('select[name="tipo_reserva"]');
        if (tipoSel) tipoSel.addEventListener('change', toggleQuartoSelectVisibility);
    });

    function gerarQuartosSelect() {
        if (document.querySelector('select[name="tipo_reserva"]') && document.querySelector('select[name="tipo_reserva"]').value === 'DAY_USE') return;
        var selectElement = document.getElementById('quarto-select');
        if (!selectElement) return;
        var cart = JSON.parse(localStorage.getItem('cart')) || [];
        while (selectElement.firstChild) selectElement.removeChild(selectElement.firstChild);
        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.text = 'Selecione um quarto';
        selectElement.appendChild(defaultOption);
        cart.forEach(function(item) {
            var option = document.createElement('option');
            option.value = item.quartoId;
            option.text = 'Quarto ' + item.quartoNumero + ' - ' + item.quartoClassificacao;
            selectElement.appendChild(option);
        });
    }
    function atualizarTotal() {
        if (document.querySelector('select[name="tipo_reserva"]') && document.querySelector('select[name="tipo_reserva"]').value === 'DAY_USE') return;
        var totalEl = document.getElementById('total-cart-value');
        if (!totalEl) return;
        var valorTotalText = totalEl.innerText;
        var valorTotal = parseFloat(valorTotalText.replace('R$', '').replace(/\./g, '').replace(',', '.'));
        var valorTotalInput = document.getElementById('valor_total');
        if (valorTotalInput) valorTotalInput.value = valorTotal.toFixed(2);
        var submitBtn = document.querySelector('form.edit-form button[type="submit"]:disabled');
        if (submitBtn) submitBtn.removeAttribute('disabled');
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