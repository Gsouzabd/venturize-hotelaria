<div class="tab-pane fade show active" id="informacoes-gerais" role="tabpanel" aria-labelledby="informacoes-gerais-tab">
    <div id="informacoesGeraisForm">
        <h5><i class="fa-solid fa-1"></i>Sobre </h5>
        <x-admin.field-group>
            <!-- Campo de Tipo -->
            <x-admin.field cols="3">
                <x-admin.label label="Tipo de Reserva" required/>
                <x-admin.select name="tipo_reserva" id="tipo" class="form-control" required
                                :items="['Individual' => 'Individual', 'Grupo' => 'Grupo']"
                                selectedItem="{{ old('tipo_reserva') }}">
                </x-admin.select>
            </x-admin.field>

            <!-- Campo de Situação -->
            <x-admin.field cols="3">
                <x-admin.label label="Situação da Reserva" required/>
                <x-admin.select name="situacao_reserva" id="situacao" class="form-control"
                                :items="['PRÉ RESERVA' => 'Pré Reserva', 'CONFIRMADA' => 'Confirmada', 'CANCELADA' => 'Cancelada']"
                                selectedItem="{{ old('situacao_reserva') }}">
                </x-admin.select>
            </x-admin.field>

            <x-admin.field cols="3">
                <x-admin.label label="Tipo de Solicitante" required/>
                <x-admin.select name="tipo_solicitante" id="tipo_solicitante" label="Tipo de Solicitante" required 
                                :items="['PF' => 'Pessoa Física (PF)', 'PJ' => 'Pessoa Jurídica (PJ)']"/>   
            </x-admin.field>
        </x-admin.field-group>
            
            <h5><i class="fa-solid fa-2"></i>Dados do Solicitante </h5>
            
            <!-- Campos Comuns -->
            <x-admin.field-group>
                <x-admin.field cols="6">
                    <x-admin.label label="Solicitante" required/>
                    <x-admin.text name="nome" id="nomeSolicitante" :value="old('nome')" required/>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="solicitanteHospedeCheckbox">
                        <label class="form-check-label" for="solicitanteHospedeCheckbox">
                            Solicitante será o hóspede?
                        </label>
                    </div>
                </x-admin.field>
            
                <x-admin.field cols="6">
                    <x-admin.label label="CPF" required/>
                    <x-admin.text name="cpf" id="cpf" :value="old('cpf')" required placeholder="Digite o CPF"/>
                    <button type="button" id="buscarCpfButton" class="btn btn-secondary mt-2">Buscar</button>
                    <small>Buscar CPF na base de clientes.</small>
                    <div id="cpfError" class="text-danger mt-2" style="display: none;">Nenhum cliente encontrado com o CPF informado.</div>
                </x-admin.field>
            </x-admin.field-group>
            
            <x-admin.field-group>
                <x-admin.field cols="6">
                    <x-admin.label label="Data de Nascimento" required/>
                    <x-admin.datepicker name="data_nascimento" id="data_nascimento" :value="old('data_nascimento')" required/>
                </x-admin.field>

                <x-admin.field cols="6">
                    <x-admin.label label="Telefone" required/>
                    <x-admin.text name="telefone" id="modal_telefone" :value="old('telefone')" required/>
                </x-admin.field>
            </x-admin.field-group>
            
            <x-admin.field-group>
                <x-admin.field cols="6">
                    <x-admin.label label="Email" required/>
                    <x-admin.text name="email" id="modal_email" :value="old('email')" required/>
                </x-admin.field>
            
                <x-admin.field cols="6">
                    <x-admin.label label="Email de Faturamento"/>
                    <x-admin.text name="email_faturamento" id="email_faturamento" :value="old('email_faturamento')"/>
                </x-admin.field>
            </x-admin.field-group>
            
            <x-admin.field-group class="pj-hide">
                <x-admin.field cols="6">
                    <x-admin.label label="RG"/>
                    <x-admin.text name="rg" id="rg" :value="old('rg')"/>
                </x-admin.field>
            
                <x-admin.field cols="6">
                    <x-admin.label label="Celular"/>
                    <x-admin.text name="celular" id="celular" :value="old('celular')"/>
                </x-admin.field>
            </x-admin.field-group>


        <h5 class="pf-hide"><i class="fa-solid fa-3"></i>Dados da Empresa de Faturamento</h5>
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Empresa de Faturamento (<small>Nome Fantasia</small>)"/> 
                <x-admin.text name="nome_fantasia_faturamento" id="nome_fantasia_faturamento" :value="old('nome_fantasia_faturamento')"/>
                <input type="hidden" name="empresa_faturamento_id" id="empresa_faturamento_id" :value="old('empresa_faturamento_id')"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="CNPJ Faturamento"/>
                <div class="input-group">
                    <x-admin.text name="cnpj_faturamento" id="cnpj_faturamento" :value="old('cnpj_faturamento')" />
                    <div class="input-group-append">
                        <button type="button" id="verificarCnpjFaturamento" class="btn btn-secondary">Buscar</button>
                    </div>
                </div>
                <div id="cnpjFaturamentoError" class="text-danger mt-2" style="display: none;">Nenhuma empresa encontrada com o CNPJ informado.</div>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Razão Social"/>
                <x-admin.text name="razao_social" id="razao_social" :value="old('razao_social')"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="Inscrição Estadual"/>
                <x-admin.text name="inscricao_estadual" id="inscricao_estadual" :value="old('inscricao_estadual')"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Email"/>
                <x-admin.text name="email_empresa_faturamento" id="email_empresa_faturamento" :value="old('email_empresa_faturamento')"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="telefone_faturamento" id="telefone_faturamento" :value="old('telefone_faturamento')"/>
            </x-admin.field>
        </x-admin.field-group>

        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="CEP"/>
                <x-admin.text name="CEP_faturamento" id="CEP_faturamento" :value="old('CEP_faturamento')"/>
            </x-admin.field>

        </x-admin.field-group>
                

        <h5 class="pf-hide"><i class="fa-solid fa-4"></i>Dados da Empresa Solicitante</h5>

          <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Empresa Solicitante (<small>Nome Fantasia</small>)"/> 
                <x-admin.text name="nome_fantasia_solicitante" id="nome_fantasia_solicitante" :value="old('nome_fantasia_solicitante')"/>
                <input type="hidden" name="empresa_solicitante_id" id="empresa_solicitante_id" :value="old('empresa_solicitante_id')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="CNPJ Solicitante"/>
                <div class="input-group">
                    <x-admin.text name="cnpj_solicitante" id="cnpj_solicitante" :value="old('cnpj_solicitante')" />
                    <div class="input-group-append">
                        <button type="button" id="verificarCnpjSolicitante" class="btn btn-secondary">Buscar</button>
                    </div>
                </div>
                <div id="cnpjSolicitanteError" class="text-danger mt-2" style="display: none;">Nenhuma empresa encontrada com o CNPJ informado.</div>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Razão Social"/>
                <x-admin.text name="razao_social" id="razao_social" :value="old('razao_social')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Inscrição Estadual"/>
                <x-admin.text name="inscricao_estadual" id="inscricao_estadual" :value="old('inscricao_estadual')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Email"/>
                <x-admin.text name="email_solicitante" id="email_solicitante" :value="old('email_solicitante')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="telefone_solicitante" id="telefone_solicitante" :value="old('telefone_solicitante')"/>
            </x-admin.field>
        </x-admin.field-group>

        <!-- Observações -->
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Observações"/>
                <x-admin.textarea name="observacoes" id="modal_observacoes" :value="old('observacoes')" rows="3"/>
            </x-admin.field>

            <x-admin.field cols="6">
                <x-admin.label label="Observações Internas"/>
                <x-admin.textarea name="observacoes_internas" id="modal_observacoes_internas" :value="old('observacoes_internas')" rows="3"/>
            </x-admin.field>
        </x-admin.field-group>

        <button type="button" id="saveInfoButton" class="btn btn-primary">Salvar Informações Gerais</button>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSolicitanteSelect = document.querySelector('select[name="tipo_solicitante"]');
        const pjHideFields = document.querySelectorAll('.pj-hide');
        const pfHideFields = document.querySelectorAll('.pf-hide');
    
       
    
        // Função para atualizar a visibilidade dos campos
        function atualizarCampos() {
            if (tipoSolicitanteSelect.value === 'PJ') {
                pjHideFields.forEach(field => field.style.display = 'none');
                pfHideFields.forEach(field => field.style.display = 'flex');
            
            } else {
                pjHideFields.forEach(field => field.style.display = 'flex');
                pfHideFields.forEach(field => field.style.display = 'none');
            }
        }
    
        // Atualiza os campos ao carregar a página
        atualizarCampos();
    
        // Adiciona um evento de mudança ao select
        tipoSolicitanteSelect.addEventListener('change', atualizarCampos);
    });
</script>