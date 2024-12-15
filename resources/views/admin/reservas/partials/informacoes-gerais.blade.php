<div class="tab-pane fade show active" id="informacoes-gerais" role="tabpanel" aria-labelledby="informacoes-gerais-tab">
    <div id="informacoesGeraisForm">
        <h5><i class="fa-solid fa-1"></i>Sobre </h5>
        <x-admin.field-group>
            <!-- Campo de Tipo -->
            {{-- <x-admin.field cols="3">
                <x-admin.label label="Tipo de Reserva" required/>
                <x-admin.select name="tipo_reserva" id="tipo" class="form-control" required
                                :items="['INDIVIDUAL' => 'Individual', 'GRUPO' => 'Grupo']"
                                selectedItem="{{ old('tipo_reserva', $reserva->tipo_reserva ?? '') }}">
                </x-admin.select>
            </x-admin.field> --}}

            <!-- Campo de Situação -->
            <x-admin.field cols="3">
                <x-admin.label label="Situação da Reserva" required/>
                <x-admin.select name="situacao_reserva" id="situacao" class="form-control"
                                :items="['RESERVADO' => 'Reservado', 'CANCELADA' => 'Cancelada', 'PRÉ RESERVA' => 'Pré Reserva']"
                                
                                selectedItem="{{ old('situacao_reserva', $reserva->situacao_reserva ?? '') }}">
                </x-admin.select>
            </x-admin.field>

            <x-admin.field cols="3">
                <x-admin.label label="Tipo de Solicitante" required/>
                <x-admin.select name="tipo_solicitante" id="tipo_solicitante" label="Tipo de Solicitante" required 
                                :items="['PF' => 'Pessoa Física (PF)', 'PJ' => 'Pessoa Jurídica (PJ)']"
                                selectedItem="{{ old('tipo_solicitante', $reserva->tipo_solicitante ?? '') }}"/>   
            </x-admin.field>
        </x-admin.field-group>
            
        <h5><i class="fa-solid fa-2"></i>Dados do Solicitante </h5>
            
        <!-- Campos Comuns -->
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Nome do Solicitante" required/>
                <x-admin.text name="nome" id="nomeSolicitante" :value="old('nome', $reserva->clienteSolicitante->nome ?? '')" required/>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="solicitanteHospedeCheckbox">
                    <label class="form-check-label" for="solicitanteHospedeCheckbox">
                        Solicitante será o hóspede?
                    </label>
                </div>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="CPF" required/>
                <x-admin.text name="cpf" id="cpf" :value="old('cpf', $reserva->clienteSolicitante->cpf ?? '')" required placeholder="Digite o CPF"/>
                <div class="d-flex">
                    <div class="col">
                        <button type="button" id="buscarCpfButton" class="btn btn-secondary mt-2">Buscar</button>
                        <small>Buscar na base de clientes.</small>
                        <div id="cpfError" class="text-danger mt-2" style="display: none;">Nenhum cliente encontrado com o CPF informado.</div> 
                    </div>
                    <div class="col">
                        {{-- <button type="button" id="validarCpfButton" class="btn btn-secondary mt-2">Validar</button>
                        <small>Validar CPF.</small> --}}
                        <div id="cpfValidateError" class="text-danger mt-2" style="display: none;">CPF Inválido.</div>
                        <div id="cpfvalidateRight" class="text-success mt-2" style="display: none;">CPF Válido</div>
                    </div>
                </div>

            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group>
            <x-admin.field cols="4">
                <x-admin.label label="Data de Nascimento" required/>
                <x-admin.datepicker name="data_nascimento" id="data_nascimento" :value="old('data_nascimento', isset($reserva->clienteSolicitante->data_nascimento) ? \Carbon\Carbon::parse($reserva->clienteSolicitante->data_nascimento)->format('d-m-Y') : '')" required/>                        
            </x-admin.field>

            <x-admin.field cols="4">
                <x-admin.label label="Email" required/>
                <x-admin.text name="email" id="modal_email" :value="old('email', $reserva->clienteSolicitante->email ?? '')" required/>
            </x-admin.field>

            <x-admin.field cols="4">
                <x-admin.label label="Celular" required/>
                <x-admin.text name="telefone" id="modal_telefone" :value="old('telefone', $reserva->clienteSolicitante->telefone ?? '')" required class="phone-mask"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group>
            <x-admin.field cols="2">
                <x-admin.label label="CEP"/>
                <x-admin.text name="cep" id="cep" :value="old('cep', $reserva->clienteSolicitante->cep ?? '')"/>
                <button type="button" id="buscarCepButton" class="btn btn-secondary mt-2">Buscar</button>
                <small>Buscar endereço.</small>
                <div id="cepError" class="text-danger mt-2" style="display: none;">Nenhum endereço encontrado com o CEP informado.</div>
            </x-admin.field>
        
            <x-admin.field cols="4">
                <x-admin.label label="Endereço"/>
                <x-admin.text name="endereco" id="endereco" :value="old('endereco', $reserva->clienteSolicitante->endereco ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="2">
                <x-admin.label label="Número"/>
                <x-admin.text name="numero" id="numero" :value="old('numero', $reserva->clienteSolicitante->numero ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="4">
                <x-admin.label label="Bairro"/>
                <x-admin.text name="bairro" id="bairro" :value="old('bairro', $reserva->clienteSolicitante->bairro ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group>
            <x-admin.field cols="4">
                <x-admin.label label="Cidade"/>
                <x-admin.text name="cidade" id="cidade" :value="old('cidade', $reserva->clienteSolicitante->cidade ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="4">
                <x-admin.label label="Estado"/>
                <x-admin.text name="estado" id="estado" :value="old('estado', $reserva->clienteSolicitante->estado ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="4">
                <x-admin.label label="País"/>
                <x-admin.text name="pais" id="pais" :value="old('pais', $reserva->clienteSolicitante->pais ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pj-hide">
            <x-admin.field cols="6">
                <x-admin.label label="RG"/>
                <x-admin.text name="rg" id="rg" :value="old('rg', $reserva->clienteSolicitante->rg ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="celular" id="celular" :value="old('celular', $reserva->clienteSolicitante->celular ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>

        
        <h5 class="pf-hide"><i class="fa-solid fa-3"></i>Dados da Empresa Solicitante</h5>
        
        <x-admin.field-group class="pf-hide on-copy-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Empresa Solicitante (<small>Nome Fantasia</small>)"/> 
                <x-admin.text name="nome_fantasia_solicitante" id="nome_fantasia_solicitante" :value="old('nome_fantasia_solicitante', $reserva->empresaSolicitante->nome_fantasia ?? '')"/>
                <input type="hidden" name="empresa_solicitante_id" id="empresa_solicitante_id" :value="old('empresa_solicitante_id', $reserva->empresa_solicitante_id ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="CNPJ Solicitante"/>
                <div class="input-group">
                    <x-admin.text name="cnpj_solicitante" id="cnpj_solicitante" :value="old('cnpj_solicitante', $reserva->empresaSolicitante->cnpj ?? '')" />
                    <div class="input-group-append">
                        <button type="button" id="verificarCnpjSolicitante" class="btn btn-secondary">Buscar</button>
                    </div>
                </div>
                <div id="cnpjSolicitanteError" class="text-danger mt-2" style="display: none;">Nenhuma empresa encontrada com o CNPJ informado.</div>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide ">
            <x-admin.field cols="6">
                <x-admin.label label="Razão Social"/>
                <x-admin.text name="razao_social_solicitante" id="razao_social_solicitante" :value="old('razao_social_solicitante', $reserva->empresaSolicitante->razao_social ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Inscrição Estadual"/>
                <x-admin.text name="inscricao_estadual_solicitante" id="inscricao_estadual_solicitante" :value="old('inscricao_estadual_solicitante', $reserva->empresaSolicitante->inscricao_estadual ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Email"/>
                <x-admin.text name="email_solicitante" id="email_solicitante" :value="old('email_solicitante', $reserva->empresaSolicitante->email ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="telefone_solicitante" id="telefone_solicitante" :value="old('telefone_solicitante', $reserva->empresaSolicitante->telefone ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="CEP"/>
                <x-admin.text name="cep_solicitante" id="cep_solicitante" :value="old('cep_solicitante', $reserva->empresaSolicitante->cep ?? '')"/>
            </x-admin.field>
            <x-admin.field cols="6"  style="display: flex; align-items:end;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="copy_faturamento_to_solicitante">
                    <label class="form-check-label" for="copy_faturamento_to_solicitante">
                        Empresa Solicitante será a Faturadora?
                    </label>
                </div>
            </x-admin.field>
        </x-admin.field-group>
        


        <h5 class="pf-hide"><i class="fa-solid fa-4"></i>Dados da Empresa de Faturamento</h5>
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Empresa de Faturamento (<small>Nome Fantasia</small>)"/> 
                <x-admin.text name="nome_fantasia_faturamento" id="nome_fantasia_faturamento" :value="old('nome_fantasia_faturamento', $reserva->empresaFaturamento->nome_fantasia ?? '')"/>
                <input type="hidden" name="empresa_faturamento_id" id="empresa_faturamento_id" :value="old('empresa_faturamento_id', $reserva->empresa_faturamento_id ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="CNPJ Faturamento"/>
                <div class="input-group">
                    <x-admin.text name="cnpj_faturamento" id="cnpj_faturamento" :value="old('cnpj_faturamento', $reserva->empresaFaturamento->cnpj ?? '')" />
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
                <x-admin.text name="razao_social_faturamento" id="razao_social_faturamento" :value="old('razao_social_faturamento', $reserva->empresaFaturamento->razao_social ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Inscrição Estadual"/>
                <x-admin.text name="inscricao_estadual_faturamento" id="inscricao_estadual_faturamento" :value="old('inscricao_estadual_faturamento', $reserva->empresaFaturamento->inscricao_estadual ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="Email"/>
                <x-admin.text name="email_empresa_faturamento" id="email_empresa_faturamento" :value="old('email_empresa_faturamento', $reserva->empresaFaturamento->email ?? '')"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Telefone"/>
                <x-admin.text name="telefone_faturamento" id="telefone_faturamento" :value="old('telefone_faturamento', $reserva->empresaFaturamento->telefone ?? '')"/>
            </x-admin.field>
        </x-admin.field-group>
        
        <x-admin.field-group class="pf-hide">
            <x-admin.field cols="6">
                <x-admin.label label="CEP"/>
                <x-admin.text name="cep_faturamento" id="cep_faturamento" :value="old('cep_faturamento', $reserva->empresaFaturamento->cep ?? '')"/>
            </x-admin.field>
            
        </x-admin.field-group>
        <!-- Observações -->
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Observações"/>
                <x-admin.textarea name="observacoes" id="modal_observacoes" :value="old('observacoes', $reserva->observacoes ?? '')" rows="3"/>
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="Observações Internas"/>
                <x-admin.textarea name="observacoes_internas" id="modal_observacoes_internas" :value="old('observacoes_internas', $reserva->observacoes_internas ?? '')" rows="3"/>
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
  

        const buscarCpfButton = document.getElementById('buscarCpfButton');
        const cpfInput = document.getElementById('cpf');
        const clienteInfo = document.getElementById('clienteInfo');
        const modalElement = document.getElementById('criarClienteModal');
        const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
        $('#cpf').mask('000.000.000-00', {reverse: true});
        $('#responsavelCpf').mask('000.000.000-00', {reverse: true});
        $('#responsavelReservaCpf').mask('000.000.000-00', {reverse: true});
        $('#modal_telefone').mask('00 00000-0000', {reverse: true});



        buscarCpfButton.addEventListener('click', function () {
            const cpf = cpfInput.value;

            // Faz uma requisição AJAX para buscar o cliente pelo CPF
            fetch(`/admin/clientes/cpf/${cpf}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                         // Verificar e formatar as datas se necessário
                         if( data.data_nascimento !== null){
                            var formattedDataNascimento= isFormattedDate(data.data_nascimento) ? data.data_nascimento : formatDate(data.data_nascimento);
                         }

                        // Preenche os campos do cliente se encontrado
                        document.getElementById('nomeSolicitante').value = data.nome ?? '';
                        document.getElementById('cpf').value = data.cpf ?? '';
                        document.querySelector('input[name="data_nascimento"]').value = formattedDataNascimento ?? '';
                        document.getElementById('rg').value = data.rg ?? '';
                        document.getElementById('modal_email').value = data.email ?? '';
                        document.getElementById('modal_telefone').value = data.telefone ?? '';
                    }
                })
                .catch(error => {
                    // console.error('Erro ao buscar o cliente:', error);
                    cpfError.style.display = 'block'; // Mostra a mensagem de erro
                    console.log(error);
                    

                    // Esconde a mensagem de erro após 5 segundos
                    setTimeout(() => {
                        cpfError.style.display = 'none';
                    }, 2000);
                });
        });

        const verificarCnpjFaturamentoButton = document.getElementById('verificarCnpjFaturamento');
        const verificarCnpjSolicitanteButton = document.getElementById('verificarCnpjSolicitante');
        const cnpjFaturamentoError = document.getElementById('cnpjFaturamentoError');

        const cnpjSolicitanteError = document.getElementById('cnpjSolicitanteError');

        verificarCnpjFaturamentoButton.addEventListener('click', function () {
            let cnpj = document.getElementById('cnpj_faturamento').value;
            cnpj = cnpj.replace(/[^\d]+/g, '');

            fetch(`/admin/buscar-empresa/${cnpj}`)
                .then(response => response.json())
                .then(data => {
                    if (data.id) {
                        document.getElementById('nome_fantasia_faturamento').value = data.nome_fantasia || data.razao_social;
                        document.getElementById('empresa_faturamento_id').value = data.id;
                        document.getElementById('razao_social_faturamento').value = data.razao_social;
                        document.getElementById('inscricao_estadual_faturamento').value = data.inscricao_estadual;
                        document.getElementById('email_empresa_faturamento').value = data.email;
                        document.getElementById('telefone_faturamento').value = data.telefone;
                        document.getElementById('cep_faturamento').value = data.cep;      
                        
                        cnpjFaturamentoError.style.display = 'none'; // Esconde a mensagem de erro
                    } else {
                        cnpjFaturamentoError.style.display = 'block'; // Mostra a mensagem de erro
                        setTimeout(() => {
                            cnpjFaturamentoError.style.display = 'none';
                        }, 5000); // Esconde a mensagem de erro após 5 segundos
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar a empresa:', error);
                    cnpjFaturamentoError.style.display = 'block'; // Mostra a mensagem de erro
                    setTimeout(() => {
                        cnpjFaturamentoError.style.display = 'none';
                    }, 5000); // Esconde a mensagem de erro após 5 segundos
                });
        });

        verificarCnpjSolicitanteButton.addEventListener('click', function () {
            let cnpj = document.getElementById('cnpj_solicitante').value;
            console.log(cnpj);
            
            cnpj = cnpj.replace(/[^\d]+/g, '');

            fetch(`/admin/buscar-empresa/${cnpj}`)
                .then(response => response.json())
                .then(data => {
                    if(data.id){
                        document.getElementById('nome_fantasia_solicitante').value = data.nome_fantasia || data.razao_social;
                        document.getElementById('empresa_solicitante_id').value = data.id;
                        document.getElementById('razao_social_solicitante').value = data.razao_social;
                        document.getElementById('inscricao_estadual_solicitante').value = data.inscricao_estadual;
                        document.getElementById('email_solicitante').value = data.email;
                        document.getElementById('telefone_solicitante').value = data.telefone;
                        document.getElementById('cep_solicitante').value = data.cep;
                       
                    
                        
                        cnpjSolicitanteError.style.display = 'none'; // Esconde a mensagem de erro
                    } else {
                        cnpjSolicitanteError.style.display = 'block'; // Mostra a mensagem de erro
                        setTimeout(() => {
                            cnpjSolicitanteError.style.display = 'none';
                        }, 5000); // Esconde a mensagem de erro após 5 segundos
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar a empresa:', error);
                    cnpjSolicitanteError.style.display = 'block'; // Mostra a mensagem de erro
                    setTimeout(() => {
                        cnpjSolicitanteError.style.display = 'none';
                    }, 5000); // Esconde a mensagem de erro após 5 segundos
                });
            
        });


        function validateCPF(cpf) {
            cpf = cpf.replace(/[^\d]+/g, ''); // Remove all non-numeric characters

            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false; // Invalid CPF length or all digits are the same
            }

            let sum = 0;
            let remainder;

            for (let i = 1; i <= 9; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }

            remainder = (sum * 10) % 11;

            if (remainder === 10 || remainder === 11) {
                remainder = 0;
            }

            if (remainder !== parseInt(cpf.substring(9, 10))) {
                return false;
            }

            sum = 0;

            for (let i = 1; i <= 10; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }

            remainder = (sum * 10) % 11;

            if (remainder === 10 || remainder === 11) {
                remainder = 0;
            }

            if (remainder !== parseInt(cpf.substring(10, 11))) {
                return false;
            }

            return true;
        }


        document.getElementById('copy_faturamento_to_solicitante').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('nome_fantasia_faturamento').value = document.getElementById('nome_fantasia_solicitante').value;
                document.getElementById('cnpj_faturamento').value = document.getElementById('cnpj_solicitante').value;
                document.getElementById('razao_social_faturamento').value = document.getElementById('razao_social_solicitante').value;
                document.getElementById('inscricao_estadual_faturamento').value = document.getElementById('inscricao_estadual_solicitante').value;
                document.getElementById('email_empresa_faturamento').value = document.getElementById('email_solicitante').value;
                document.getElementById('telefone_faturamento').value = document.getElementById('telefone_solicitante').value;
                document.getElementById('cep_faturamento').value = document.getElementById('cep_solicitante').value;
            }
        });


        const isFormattedDate = (dateStr) => {
                const regex = /^\d{2}-\d{2}-\d{4}$/;
                return regex.test(dateStr);
            };

        // Função para formatar a data de yyyy-mm-dd hh:mm:ss para dd-mm-yyyy
        const formatDate = (dateStr) => {
            if (dateStr.includes('-')) {
                const [datePart] = dateStr.split(' ');
                const [year, month, day] = datePart.split('-');
                return `${day}/${month}/${year}`;
            } else if (dateStr.includes('/')) {
                const [day, month, year] = dateStr.split('/');
                return `${day}-${month}-${year}`;
            }
            return dateStr; // Retorna a string original se não corresponder a nenhum formato esperado
        };
    });
</script>