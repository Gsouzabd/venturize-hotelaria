<div class="tab-pane fade" id="disponibilidade" role="tabpanel" aria-labelledby="disponibilidade-tab">
    <h3>Disponibilidade de Quartos</h3>
    <p>Após preencher as informações gerais, verifique a disponibilidade de quartos.</p>

    <x-admin.field-group>
        <!-- Data de Entrada -->
        <x-admin.field cols="6">
            <x-admin.label label="Data de Entrada"/>
            <x-admin.datepicker  id="data_entrada" name="data_entrada" class="form-control datepicker" placeholder="Selecione a data de entrada" />
        </x-admin.field>

        <!-- Data de Saída -->
        <x-admin.field cols="6">
            <x-admin.label label="Data de Saída"/>
            <x-admin.datepicker id="data_saida" name="data_saida" class="form-control datepicker" placeholder="Selecione a data de saída" />
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Tipo de Quarto -->
        <x-admin.field cols="6">
            <x-admin.label label="Tipo"/>
            <x-admin.select name="tipo_quarto" id="tipo_quarto" class="form-control"
                :items="['Embaúba' => 'Embaúba', 'Camará' => 'Camará']"
                selectedItem="{{ old('tipo_quarto') }}"/>
        </x-admin.field>
    </x-admin.field-group>

    <x-admin.field-group>
        <!-- Apartamentos -->
        <x-admin.field cols="4">
            <x-admin.label label="Apartamentos"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('apartamentos')">-</button>
                <input type="text" id="apartamentos" name="apartamentos" class="form-control text-center" value="1" >
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('apartamentos')">+</button>
            </div>
        </x-admin.field>

        <!-- Adultos -->
        <x-admin.field cols="4">
            <x-admin.label label="Adultos"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('adultos')">-</button>
                <input type="text" id="adultos" name="adultos" class="form-control text-center" value="1" >
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('adultos')">+</button>
            </div>
        </x-admin.field>

        <!-- Crianças -->
        <x-admin.field cols="4">
            <x-admin.label label="Crianças"/>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decrementValue('criancas')">-</button>
                <input type="text" id="criancas" name="criancas" class="form-control text-center" value="0" >
                <button type="button" class="btn btn-outline-primary" onclick="incrementValue('criancas')">+</button>
            </div>
        </x-admin.field>
    </x-admin.field-group>

    <!-- Botão para verificar disponibilidade -->
    <a type="button" class="btn btn-primary" id="verificarDisponibilidade">Verificar Disponibilidade</a>
    <div id="resultadoDisponibilidade" class="mt-4"></div>
        <!-- Modal -->
    <div class="modal fade" id="responsavelModal" tabindex="-1" role="dialog" aria-labelledby="responsavelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responsavelModalLabel">Informações do Responsável pelo Quarto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="responsavelNome">Nome</label>
                        <input type="text" class="form-control" id="responsavelNome" name="responsavelNome" required>
                    </div>
                    <div class="form-group">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelCpf" name="responsavelCpf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="saveResponsavel">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</div>