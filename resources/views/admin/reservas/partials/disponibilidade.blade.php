
<div class="tab-pane fade" id="disponibilidade" role="tabpanel" aria-labelledby="disponibilidade-tab">
    <h3>Disponibilidade de Quartos</h3>
    <p>Após preencher as informações gerais, verifique a disponibilidade de quartos.</p>

    <!-- Apartamentos -->
    <x-admin.field-group style="display: flex; align-items:end;">
        <x-admin.field cols="3">
            <x-admin.label label="Quantidade de Apartamentos"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('apartamentos')">-</button>
                <input type="text" id="apartamentos" name="apartamentos" class="form-control text-center" value="1" min="1">
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('apartamentos')">+</button>
            </div>
        </x-admin.field>

        <!-- Botão para verificar disponibilidade -->
        <x-admin.field cols="3">
            <a type="button" class="btn btn-primary" id="save-qty-apartamentos">Avançar</a>
        </x-admin.field>
        
    </x-admin.field-group>

    <div id="after-qty-apartamentos" style="display: none;">
        <h3>Informações do <span id="numero-do-quarto">1</span>º apartamento</h3>
        <x-admin.field-group>
            <!-- Data de Entrada -->
            <x-admin.field cols="6">
                <x-admin.label label="Data de Entrada"/>
                <x-admin.datepicker 
                    id="data_entrada" 
                    name="data_entrada" 
                    class="form-control datepicker" 
                    placeholder="Selecione a data de entrada" 
                    :value="old('data_entrada', isset($reserva->data_checkin) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d-m-Y') : '')"/>
            </x-admin.field>

            <!-- Data de Saída -->
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
            <!-- Tipo de Quarto -->
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

            <!-- Adultos -->
            <x-admin.field cols="3">
                <x-admin.label label="Adultos"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('adultos')">-</button>
                    <input type="text" id="adultos" name="adultos" class="form-control text-center" :value="old('adultos', $reserva->adultos ?? 1)" max="3" min="1">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('adultos')">+</button>
                </div>
            </x-admin.field>

            <!-- Crianças (Até 7 anos) -->
            <x-admin.field cols="3">
                <x-admin.label label="Crianças (Até 7 anos)"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_ate_7')">-</button>
                    <input type="text" id="criancas_ate_7" name="criancas_ate_7" class="form-control text-center" :value="old('criancas_ate_7', $reserva->criancas ?? 0)">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_ate_7')">+</button>
                </div>
            </x-admin.field>

            <!-- Crianças (8 à 12 anos) -->
            <x-admin.field cols="3">
                <x-admin.label label="Crianças (8 à 12 anos)"/>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas_mais_7')">-</button>
                    <input type="text" id="criancas_mais_7" name="criancas_mais_7" class="form-control text-center" :value="old('criancas_mais_7', $reserva->criancas ?? 0)">
                    <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas_mais_7')">+</button>
                </div>
            </x-admin.field>
        </x-admin.field-group>

        <!-- Botão para verificar disponibilidade -->
        <div class="row d-flex justify-content-between">
            <a type="button" class="btn btn-primary" id="verificarDisponibilidade">Verificar Disponibilidade</a>
            <!-- Botão para avançar para a aba Pagamento -->
            <a type="button" class="btn btn-success disabled" id="avancarPagamento" disabled>Avançar para Pagamento</a>
        </div>

        <div id="resultadoDisponibilidade" class="mt-4"></div>
               
    </div>

</div>

<!-- Script para avançar para a aba Pagamento -->
<script>
    document.getElementById('save-qty-apartamentos').addEventListener('click', function() {
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
    document.getElementById('avancarPagamento').addEventListener('click', function() {
        var pagamentoTab = document.querySelector('#pagamento-tab');
        var disponibilidadeTab = document.querySelector('#disponibilidade-tab');
        
        // Remove a classe ativa da aba atual e ativa a aba pagamento
        disponibilidadeTab.classList.remove('active');
        pagamentoTab.classList.remove('disabled');
        pagamentoTab.classList.add('active');
        
        // Ativa o conteúdo da aba de pagamento
        var disponibilidadePane = document.querySelector('#disponibilidade');
        var pagamentoPane = document.querySelector('#pagamento');
        disponibilidadePane.classList.remove('show', 'active');
        pagamentoPane.classList.add('show', 'active');

        // Alterna para a aba pagamento
        pagamentoTab.click();
    });
</script>
