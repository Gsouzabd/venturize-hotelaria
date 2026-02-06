
<div class="tab-pane fade" id="disponibilidade" role="tabpanel" aria-labelledby="disponibilidade-tab">
    {{-- Bloco Day Use: apenas data + adultos/crianças, sem quartos. Names são atribuídos via JS quando tipo = DAY_USE. --}}
    <div id="disponibilidade-dayuse" style="display: none;">
        <h3>Day Use</h3>
        <p>Informe a data e a quantidade de pessoas.</p>
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Data" required/>
                <x-admin.datepicker
                    name=""
                    id="data_entrada_dayuse"
                    class="form-control datepicker dayuse-field"
                    placeholder="Selecione a data"
                    :value="old('data_entrada', isset($reserva->data_checkin) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d-m-Y') : '')"/>
            </x-admin.field>
            <x-admin.field cols="6">
                <x-admin.label label="Data saída (mesmo dia)"/>
                <x-admin.datepicker
                    name=""
                    id="data_saida_dayuse"
                    class="form-control datepicker dayuse-field"
                    placeholder="Mesmo dia"
                    :value="old('data_saida', isset($reserva->data_checkout) ? \Carbon\Carbon::parse($reserva->data_checkout)->format('d-m-Y') : '')"/>
            </x-admin.field>
        </x-admin.field-group>
        <x-admin.field-group>
            <x-admin.field cols="3">
                <x-admin.label label="Adultos"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('adultos_dayuse')">-</button>
                    <input type="number" id="adultos_dayuse" class="form-control text-center dayuse-field" data-name="adultos" value="{{ old('adultos', $reserva->adultos ?? 1) }}" max="20" min="1">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('adultos_dayuse')">+</button>
                </div>
            </x-admin.field>
            <x-admin.field cols="3">
                <x-admin.label label="Crianças (Até 7 anos)"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_ate_7_dayuse')">-</button>
                    <input type="number" id="criancas_ate_7_dayuse" class="form-control text-center dayuse-field" data-name="criancas_ate_7" value="{{ old('criancas_ate_7', $reserva->criancas_ate_7 ?? 0) }}" min="0">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_ate_7_dayuse')">+</button>
                </div>
            </x-admin.field>
            <x-admin.field cols="3">
                <x-admin.label label="Crianças (4 à 12 anos)" id="label-criancas-mais-7-dayuse"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_mais_7_dayuse')">-</button>
                    <input type="number" id="criancas_mais_7_dayuse" class="form-control text-center dayuse-field" data-name="criancas_mais_7" value="{{ old('criancas_mais_7', $reserva->criancas_mais_7 ?? 0) }}" min="0">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_mais_7_dayuse')">+</button>
                </div>
            </x-admin.field>
        </x-admin.field-group>
        <div class="row mt-3">
            <button type="button" class="btn btn-success" id="avancarPagamentoDayUse">Avançar para Pagamento</button>
        </div>
    </div>

    {{-- Bloco reservas com quartos: disponibilidade e carrinho --}}
    <div id="disponibilidade-quartos">
        <h3>Disponibilidade de Quartos</h3>
        <p>Após preencher as informações gerais, verifique a disponibilidade de quartos.</p>

        <x-admin.field-group style="display: flex; align-items:end;">
            <x-admin.field cols="3">
                <x-admin.label label="Quantidade de Apartamentos"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('apartamentos')">-</button>
                    <input type="text" id="apartamentos" name="apartamentos" class="form-control text-center" value="1" min="1">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('apartamentos')">+</button>
                </div>
            </x-admin.field>
            <x-admin.field cols="3">
                <a type="button" class="btn btn-primary" id="save-qty-apartamentos">Avançar</a>
            </x-admin.field>
        </x-admin.field-group>

        <div id="after-qty-apartamentos" style="display: none;">
            <h3>Informações do <span id="numero-do-quarto">1</span>º apartamento</h3>
            <x-admin.field-group>
                <x-admin.field cols="6">
                    <x-admin.label label="Data de Entrada"/>
                    <x-admin.datepicker
                        id="data_entrada"
                        name="data_entrada"
                        class="form-control datepicker"
                        placeholder="Selecione a data de entrada"
                        :value="old('data_entrada', isset($reserva->data_checkin) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d-m-Y') : '')"/>
                </x-admin.field>
                <x-admin.field cols="6">
                    <x-admin.label label="Data de Saída"/>
                    <x-admin.datepicker
                        id="data_saida"
                        name="data_saida"
                        class="form-control datepicker"
                        placeholder="Selecione a data de saída"
                        :value="old('data_saida', isset($reserva->data_checkout) ? \Carbon\Carbon::parse($reserva->data_checkout)->format('d-m-Y') : '')"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="4">
                    <x-admin.label label="Tipo do Quarto"/>
                    <x-admin.select name="tipo_quarto" id="tipo_quarto" class="form-control"
                        :items="['Embaúba' => 'Embaúba', 'Camará' => 'Camará']"
                        selectedItem="{{ old('tipo_quarto', $reserva->tipo_quarto ?? '') }}"/>
                </x-admin.field>
                <x-admin.field cols="4">
                    <x-admin.label label="Individual, Duplo ou Triplo"/>
                    <x-admin.select name="composicao_quarto" id="composicao_quarto" class="form-control"
                        :items="['Individual' => 'Individual', 'Duplo' => 'Duplo', 'Triplo' => 'Triplo']"
                        selectedItem="{{ old('composicao_quarto', $reserva->composicao_quarto ?? 'Individual') }}"/>
                </x-admin.field>
                <x-admin.field cols="4">
                    <x-admin.label label="Tipo de Acomodação"/>
                    <x-admin.select name="tipo_acomodacao" id="tipo_acomodacao" class="form-control"
                        :items="['Solteiro' => 'Solteiro', 'Casal' => 'Casal']"
                        selectedItem="{{ old('tipo_acomodacao', $reserva->tipo_acomodacao ?? '') }}"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="3">
                    <x-admin.label label="Adultos"/>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('adultos')">-</button>
                        <input type="text" id="adultos" name="adultos" class="form-control text-center" :value="old('adultos', $reserva->adultos ?? 1)" max="3" min="1">
                        <button type="button" class="btn btn-outline-primary" onclick="incrementValue('adultos')">+</button>
                    </div>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Crianças (Até 7 anos)"/>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_ate_7')">-</button>
                        <input type="text" id="criancas_ate_7" name="criancas_ate_7" class="form-control text-center" :value="old('criancas_ate_7', $reserva->criancas ?? 0)">
                        <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_ate_7')">+</button>
                    </div>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Crianças (8 à 12 anos)" id="label-criancas-mais-7"/>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_mais_7')">-</button>
                        <input type="text" id="criancas_mais_7" name="criancas_mais_7" class="form-control text-center" :value="old('criancas_mais_7', $reserva->criancas ?? 0)">
                        <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_mais_7')">+</button>
                    </div>
                </x-admin.field>
            </x-admin.field-group>

            <div class="row d-flex justify-content-between">
                <a type="button" class="btn btn-primary" id="verificarDisponibilidade">Verificar Disponibilidade</a>
                <a type="button" class="btn btn-success disabled" id="avancarPagamento" disabled>Avançar para Pagamento</a>
            </div>
            <div id="resultadoDisponibilidade" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
(function() {
    function isDayUse() {
        var sel = document.querySelector('select[name="tipo_reserva"]');
        return sel && sel.value === 'DAY_USE';
    }
    function toggleDisponibilidadeBlocks() {
        var dayuse = document.getElementById('disponibilidade-dayuse');
        var quartos = document.getElementById('disponibilidade-quartos');
        if (!dayuse || !quartos) return;
        if (isDayUse()) {
            dayuse.style.display = 'block';
            quartos.style.display = 'none';
            document.querySelectorAll('.dayuse-field').forEach(function(el) {
                var n = el.getAttribute('data-name');
                if (n) el.setAttribute('name', n);
            });
            var duDatepickers = dayuse.querySelectorAll('input.form-datepicker');
            if (duDatepickers[0]) duDatepickers[0].setAttribute('name', 'data_entrada');
            if (duDatepickers[1]) duDatepickers[1].setAttribute('name', 'data_saida');
            ['data_entrada', 'data_saida', 'adultos', 'criancas_ate_7', 'criancas_mais_7'].forEach(function(n) {
                var q = quartos.querySelector('input[name="' + n + '"]');
                if (q) q.removeAttribute('name');
            });
        } else {
            dayuse.style.display = 'none';
            quartos.style.display = 'block';
            document.querySelectorAll('.dayuse-field').forEach(function(el) { el.removeAttribute('name'); });
            dayuse.querySelectorAll('input.form-datepicker').forEach(function(el) { el.removeAttribute('name'); });
            var qEntrada = document.getElementById('data_entrada');
            var qSaida = document.getElementById('data_saida');
            var qAdultos = document.getElementById('adultos');
            var qC7 = document.getElementById('criancas_ate_7');
            var qC12 = document.getElementById('criancas_mais_7');
            if (qEntrada) qEntrada.setAttribute('name', 'data_entrada');
            if (qSaida) qSaida.setAttribute('name', 'data_saida');
            if (qAdultos) qAdultos.setAttribute('name', 'adultos');
            if (qC7) qC7.setAttribute('name', 'criancas_ate_7');
            if (qC12) qC12.setAttribute('name', 'criancas_mais_7');
        }
    }

    function syncDayUseDataToMain() {
        var de = document.getElementById('data_entrada_dayuse');
        var ds = document.getElementById('data_saida_dayuse');
        if (de && de.value) {
            var q = document.querySelector('input[name="data_entrada"]');
            if (q) q.value = de.value;
        }
        if (ds && ds.value) {
            var q = document.querySelector('input[name="data_saida"]');
            if (q) q.value = ds.value;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var tipoReservaSelect = document.querySelector('select[name="tipo_reserva"]');
        var labelCriancasMais7 = document.getElementById('label-criancas-mais-7');
        var labelCriancasMais7Dayuse = document.getElementById('label-criancas-mais-7-dayuse');

        toggleDisponibilidadeBlocks();
        if (tipoReservaSelect) tipoReservaSelect.addEventListener('change', toggleDisponibilidadeBlocks);

        function atualizarLabelsCriancas() {
            var label = tipoReservaSelect.value === 'DAY_USE' ? 'Crianças (4 à 12 anos)' : 'Crianças (8 à 12 anos)';
            if (labelCriancasMais7) labelCriancasMais7.textContent = label;
            if (labelCriancasMais7Dayuse) labelCriancasMais7Dayuse.textContent = 'Crianças (4 à 12 anos)';
        }
        atualizarLabelsCriancas();
        if (tipoReservaSelect) tipoReservaSelect.addEventListener('change', atualizarLabelsCriancas);

        var saveQty = document.getElementById('save-qty-apartamentos');
        if (saveQty) {
            saveQty.addEventListener('click', function() {
                var apartamentosInput = document.querySelector('input[name="apartamentos"]');
                var apartamentosValue = parseInt(apartamentosInput.value, 10);
                if (apartamentosValue > 0) {
                    document.getElementById('after-qty-apartamentos').style.display = 'block';
                    apartamentosInput.setAttribute('disabled', 'disabled');
                    this.setAttribute('disabled', 'disabled');
                    this.classList.add('disabled');
                } else {
                    alert('A quantidade de apartamentos deve ser maior que 0.');
                }
            });
        }

        var avancarPagamento = document.getElementById('avancarPagamento');
        if (avancarPagamento) {
            avancarPagamento.addEventListener('click', function() {
                var pagamentoTab = document.querySelector('#pagamento-tab');
                var disponibilidadeTab = document.querySelector('#disponibilidade-tab');
                if (!pagamentoTab || !disponibilidadeTab) return;
                disponibilidadeTab.classList.remove('active');
                pagamentoTab.classList.remove('disabled');
                pagamentoTab.classList.add('active');
                document.querySelector('#disponibilidade').classList.remove('show', 'active');
                document.querySelector('#pagamento').classList.add('show', 'active');
                pagamentoTab.click();
            });
        }

        var avancarDayUse = document.getElementById('avancarPagamentoDayUse');
        if (avancarDayUse) {
            avancarDayUse.addEventListener('click', function() {
                var dayusePane = document.getElementById('disponibilidade-dayuse');
                var dataEntradaEl = dayusePane ? dayusePane.querySelector('input.form-datepicker') : null;
                var dataEntrada = dataEntradaEl ? dataEntradaEl.value : '';
                if (!dataEntrada) {
                    alert('Informe a data.');
                    return;
                }
                var dataSaidaEl = dayusePane ? dayusePane.querySelectorAll('input.form-datepicker')[1] : null;
                if (dataSaidaEl && !dataSaidaEl.value) dataSaidaEl.value = dataEntrada;
                syncDayUseDataToMain();
                var adultos = parseInt(document.getElementById('adultos_dayuse').value, 10) || 1;
                var criancasAte7 = parseInt(document.getElementById('criancas_ate_7_dayuse').value, 10) || 0;
                var criancasMais7 = parseInt(document.getElementById('criancas_mais_7_dayuse').value, 10) || 0;
                var comCafe = (document.getElementById('com_cafe') && document.getElementById('com_cafe').checked) ? 1 : 0;
                var urlCalcular = '{{ route("admin.reservas.calcular-day-use") }}?data_entrada=' + encodeURIComponent(dataEntrada) + '&adultos=' + adultos + '&criancas_ate_7=' + criancasAte7 + '&criancas_mais_7=' + criancasMais7 + '&com_cafe=' + comCafe;
                avancarDayUse.disabled = true;
                fetch(urlCalcular)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var totalEl = document.getElementById('valor_total');
                        if (totalEl && data.total !== undefined) totalEl.value = Number(data.total).toFixed(2);
                    })
                    .catch(function() {})
                    .finally(function() { avancarDayUse.disabled = false; });
                var pagamentoTab = document.querySelector('#pagamento-tab');
                var disponibilidadeTab = document.querySelector('#disponibilidade-tab');
                if (!pagamentoTab || !disponibilidadeTab) return;
                disponibilidadeTab.classList.remove('active');
                pagamentoTab.classList.remove('disabled');
                pagamentoTab.classList.add('active');
                document.querySelector('#disponibilidade').classList.remove('show', 'active');
                document.querySelector('#pagamento').classList.add('show', 'active');
                if (typeof $ !== 'undefined' && $('#pagamento-tab').length) $('#pagamento-tab').tab('show'); else pagamentoTab.click();
            });
        }
    });

    function incrementValue(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var v = parseInt(el.value, 10) || 0;
        var max = parseInt(el.getAttribute('max'), 10);
        if (isNaN(max)) max = 99;
        el.value = Math.min(v + 1, max);
    }
    function decrementValue(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var v = parseInt(el.value, 10) || 0;
        var min = parseInt(el.getAttribute('min'), 10);
        if (isNaN(min)) min = 0;
        el.value = Math.max(v - 1, min);
    }
    if (typeof window.decrementValue === 'undefined') window.decrementValue = decrementValue;
    if (typeof window.incrementValue === 'undefined') window.incrementValue = incrementValue;
})();
</script>
