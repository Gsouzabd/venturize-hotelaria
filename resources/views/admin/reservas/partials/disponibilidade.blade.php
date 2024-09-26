<div class="tab-pane fade" id="disponibilidade" role="tabpanel" aria-labelledby="disponibilidade-tab">
    <h3>Disponibilidade de Quartos</h3>
    <p>Após preencher as informações gerais, verifique a disponibilidade de quartos.</p>


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
        <x-admin.field cols="6">
            <x-admin.label label="Tipo do Quarto"/>
            <x-admin.select name="tipo_quarto" id="tipo_quarto" class="form-control"
                :items="['Embaúba' => 'Embaúba', 'Camará' => 'Camará']"
                selectedItem="{{ old('tipo_quarto', $reserva->tipo_quarto ?? '') }}"/>
        </x-admin.field>

        <x-admin.field cols="6">
            <x-admin.label label="Tipo de Acomodação"/>
            <x-admin.select name="tipo_acomodacao" id="tipo_acomodacao" class="form-control"
                :items="['Solteiro' => 'Solteiro', 'Casal' => 'Casal']"
                selectedItem="{{ old('tipo_acomodacao', $reserva->tipo_acomodacao ?? '') }}"/>
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Apartamentos -->
        <x-admin.field cols="4">
            <x-admin.label label="Apartamentos"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('apartamentos')">-</button>
                <input type="text" id="apartamentos" name="apartamentos" class="form-control text-center" :value="old('apartamentos', $reserva->apartamentos ?? 1)">
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('apartamentos')">+</button>
            </div>
        </x-admin.field>

        <!-- Adultos -->
        <x-admin.field cols="4">
            <x-admin.label label="Adultos"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('adultos')">-</button>
                <input type="text" id="adultos" name="adultos" class="form-control text-center" :value="old('adultos', $reserva->adultos ?? 1)">
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('adultos')">+</button>
            </div>
        </x-admin.field>

        <!-- Crianças -->
        <x-admin.field cols="4">
            <x-admin.label label="Crianças"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas')">-</button>
                <input type="text" id="criancas" name="criancas" class="form-control text-center" :value="old('criancas', $reserva->criancas ?? 0)">
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas')">+</button>
            </div>
        </x-admin.field>
    </x-admin.field-group>

    <!-- Botão para verificar disponibilidade -->
    <a type="button" class="btn btn-primary" id="verificarDisponibilidade">Verificar Disponibilidade</a>
    <div id="resultadoDisponibilidade" class="mt-4"></div>
</div>