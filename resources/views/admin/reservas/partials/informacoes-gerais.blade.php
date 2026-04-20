<div class="tab-pane fade show active" id="informacoes-gerais" role="tabpanel" aria-labelledby="informacoes-gerais-tab">

    {{-- Resumo da Reserva Atual (fixo no topo quando editando) --}}
    @if($edit && $reserva->quarto_id)
        <div id="resumo-reserva-atual" class="card mb-3" style="position: sticky; top: 0; z-index: 10; background: #fff; border: 2px solid #007bff;">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><i class="fas fa-bed"></i> Reserva Atual</strong>
                        @if($reserva->quarto)
                            — UH {{ $reserva->quarto->numero }}
                            ({{ $reserva->quarto->referencia ?? $reserva->quarto->classificacao }})
                        @endif
                    </div>
                    <div>
                        @if($reserva->data_checkin && $reserva->data_checkout)
                            <span class="badge badge-info">
                                {{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}
                                → {{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}
                            </span>
                        @endif
                        <span class="badge badge-{{ $reserva->situacao_reserva == 'HOSPEDADO' ? 'success' : ($reserva->situacao_reserva == 'RESERVADO' ? 'primary' : 'secondary') }}">
                            {{ $reserva->situacao_reserva }}
                        </span>
                    </div>
                </div>
                @if($reserva->clienteResponsavel || $reserva->clienteSolicitante)
                    <small class="text-muted">
                        Hóspede: {{ $reserva->clienteResponsavel->nome ?? $reserva->clienteSolicitante->nome ?? '-' }}
                        @if($reserva->adultos || $reserva->criancas_ate_7 || $reserva->criancas_mais_7)
                            | {{ $reserva->adultos ?? 0 }} adulto(s)
                            @if($reserva->criancas_ate_7), {{ $reserva->criancas_ate_7 }} criança(s) até 7 @endif
                            @if($reserva->criancas_mais_7), {{ $reserva->criancas_mais_7 }} criança(s) 8-12 @endif
                        @endif
                    </small>
                @endif
                <div id="cart-items" class="mt-2"></div>
                <div class="d-flex justify-content-end mt-1">
                    <small><strong>Total da Reserva:</strong> <span id="total-cart-value">R$ 0,00</span></small>
                </div>
            </div>
        </div>
    @endif

    <div id="informacoesGeraisForm">

        @if($edit && in_array($reserva->situacao_reserva, ['HOSPEDADO', 'FINALIZADO']))
            {{-- Modo somente leitura: resumo do hóspede --}}
            <h5><i class="fa-solid fa-1"></i> Dados do Hóspede</h5>
            @php
                $cliente = $reserva->clienteSolicitante ?? $reserva->clienteResponsavel;
            @endphp
            @if($cliente)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Nome:</strong>
                                <a href="{{ route('admin.clientes.edit', ['id' => $cliente->id]) }}" target="_blank">
                                    {{ $cliente->nome }} <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            </div>
                            <div class="col-md-3"><strong>CPF:</strong> {{ $cliente->cpf ?? '-' }}</div>
                            <div class="col-md-3"><strong>Telefone:</strong> {{ $cliente->celular ?? $cliente->telefone ?? '-' }}</div>
                            <div class="col-md-2"><strong>Email:</strong> {{ $cliente->email ?? '-' }}</div>
                        </div>
                        @if($reserva->quarto)
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>Apartamento:</strong> UH {{ $reserva->quarto->numero }}
                                ({{ $reserva->quarto->referencia ?? $reserva->quarto->classificacao }})
                                — {{ $reserva->quarto->composicao ?? '' }}
                            </div>
                            <div class="col-md-3"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }}</div>
                            <div class="col-md-3"><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}</div>
                            <div class="col-md-2">
                                <strong>Hóspedes:</strong> {{ $reserva->adultos ?? 0 }} ad.
                                @if($reserva->criancas_ate_7) + {{ $reserva->criancas_ate_7 }} cr. @endif
                                @if($reserva->criancas_mais_7) + {{ $reserva->criancas_mais_7 }} cr.8-12 @endif
                            </div>
                        </div>
                        @endif
                        @if($reserva->observacoes)
                            <div class="row mt-2">
                                <div class="col-md-12"><strong>Observações:</strong> {{ $reserva->observacoes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Hidden fields necessários para o form submit --}}
            <input type="hidden" name="tipo_reserva" value="{{ $reserva->tipo_reserva }}">
            <input type="hidden" name="situacao_reserva" id="confirmarCheckin" value="{{ $reserva->situacao_reserva }}">
            <input type="hidden" name="tipo_solicitante" value="{{ $reserva->tipo_solicitante }}">
            <input type="hidden" name="nome" value="{{ $cliente->nome ?? '' }}">
            <input type="hidden" name="cpf" value="{{ $cliente->cpf ?? '' }}">
        @else
            {{-- Modo edição: formulário completo --}}
            <h5><i class="fa-solid fa-1"></i>Sobre </h5>
            <x-admin.field-group>
                <!-- Campo de Tipo de Reserva -->
                <x-admin.field cols="3">
                    <x-admin.label label="Tipo de Reserva" required/>
                    <x-admin.select name="tipo_reserva" id="tipo_reserva" class="form-control" required
                                    :items="['INDIVIDUAL' => 'Individual', 'GRUPO' => 'Grupo', 'DAY_USE' => 'Day Use']"
                                    selectedItem="{{ old('tipo_reserva', $reserva->tipo_reserva ?? 'INDIVIDUAL') }}">
                    </x-admin.select>
                </x-admin.field>

                <!-- Campo de Situação -->
                <x-admin.field cols="3">
                    <x-admin.label label="Situação da Reserva" required/>
                    <x-admin.select name="situacao_reserva" id="situacao" class="form-control"
                                    :items="['RESERVADO' => 'Reservado', 'CANCELADA' => 'Cancelada', 'PRÉ RESERVA' => 'Pré Reserva']"
                                    
                                    selectedItem="{{ old('situacao_reserva', $reserva->situacao_reserva ?? 'PRÉ RESERVA') }}">
                    </x-admin.select>
                </x-admin.field>

                <x-admin.field cols="3">
                    <x-admin.label label="Tipo de Solicitante" required/>
                    <x-admin.select name="tipo_solicitante" id="tipo_solicitante" label="Tipo de Solicitante" required 
                                    :items="['PF' => 'Pessoa Física (PF)', 'PJ' => 'Pessoa Jurídica (PJ)']"
                                    selectedItem="{{ old('tipo_solicitante', $reserva->tipo_solicitante ?? 'PF') }}"/>   
                </x-admin.field>
                
                <!-- Campo Com Café da Manhã (apenas para Day Use) -->
                <x-admin.field cols="3" id="field-com-cafe" style="display: none;">
                    <x-admin.label label="Opções Day Use"/>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="com_cafe" name="com_cafe"
                            {{ old('com_cafe', $reserva->com_cafe ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="com_cafe">
                            Com Café da Manhã
                        </label>
                    </div>
                </x-admin.field>
            </x-admin.field-group>
                
            <h5><i class="fa-solid fa-2"></i>Dados do Solicitante </h5>

            <x-admin.field-group>
                <x-admin.field cols="12">
                    <x-admin.label label="Buscar Cliente Cadastrado"/>
                    <x-admin.select2 
                        name="busca_cliente" 
                        id="buscaClienteSelect"
                        remoteUrl="{{ route('admin.clientes.search') }}"
                        minInputLength="2"
                        placeholder="Digite o nome ou CPF para buscar..."
                    />
                    <small class="form-text text-muted">Selecione um cliente já cadastrado para preencher os campos automaticamente.</small>
                </x-admin.field>
            </x-admin.field-group>
                
        <!-- Campos Comuns -->
        @php
            $nomeSolicitanteValue = old('nome', $reserva->clienteSolicitante->nome ?? $reserva->clienteResponsavel->nome ?? '');
            $cpfSolicitanteValue = old('cpf', $reserva->clienteSolicitante->cpf ?? $reserva->clienteResponsavel->cpf ?? '');
            $titularTrocavel = in_array($reserva->situacao_reserva ?? '', ['PRÉ RESERVA', 'RESERVADO'], true);
            $lockSolicitante = $edit && !empty($nomeSolicitanteValue) && !$titularTrocavel;
        @endphp
        <x-admin.field-group>
            <x-admin.field cols="6">
                <x-admin.label label="Nome do Solicitante" required/>
                <x-admin.text
                    name="nome"
                    id="nomeSolicitante"
                    :value="$nomeSolicitanteValue"
                    :readonly="$lockSolicitante"
                    required
                />
                @if($edit && $reserva->clienteSolicitante)
                    <a href="{{ route('admin.clientes.edit', ['id' => $reserva->clienteSolicitante->id]) }}" target="_blank" class="small text-primary mt-1 d-inline-block">
                        <i class="fas fa-external-link-alt"></i> Ver dados completos
                    </a>
                @else
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="solicitanteHospedeCheckbox">
                        <label class="form-check-label" for="solicitanteHospedeCheckbox">
                            Solicitante será o hóspede?
                        </label>
                    </div>
                @endif
            </x-admin.field>
        
            <x-admin.field cols="6">
                <x-admin.label label="CPF" required/>
                <x-admin.text name="cpf" id="cpf" :value="$cpfSolicitanteValue" required placeholder="Digite o CPF"/>
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

        <div id="pre-reserva-hide">
            <x-admin.field-group>
                <x-admin.field cols="6">
                    <x-admin.label label="Email" />
                    <x-admin.text name="email" id="modal_email" :value="old('email', $reserva->clienteSolicitante->email ?? '')" />
                </x-admin.field>

                <x-admin.field cols="3">
                    <x-admin.label label="Celular" />
                    <x-admin.text name="celular" id="celular" :value="old('celular', $reserva->clienteSolicitante->celular ?? '')" class="phone-mask"/>
                </x-admin.field>

                <x-admin.field cols="3">
                    <x-admin.label label="Telefone" />
                    <x-admin.text name="telefone" id="telefone" :value="old('telefone', $reserva->clienteSolicitante->telefone ?? '')" class="phone-mask"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="3">
                    <x-admin.label label="RG" />
                    <x-admin.text name="rg" id="rg" :value="old('rg', $reserva->clienteSolicitante->rg ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Passaporte" />
                    <x-admin.text name="passaporte" id="passaporte" :value="old('passaporte', $reserva->clienteSolicitante->passaporte ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Órgão Expedidor" />
                    <x-admin.text name="orgao_expedidor" id="orgao_expedidor" :value="old('orgao_expedidor', $reserva->clienteSolicitante->orgao_expedidor ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Profissão" />
                    <x-admin.text name="profissao" id="profissao" :value="old('profissao', $reserva->clienteSolicitante->profissao ?? '')"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="3">
                    <x-admin.label label="Data de Nascimento" />
                    <x-admin.datepicker name="data_nascimento" id="data_nascimento"
                        :value="old('data_nascimento', ($reserva->clienteSolicitante && $reserva->clienteSolicitante->data_nascimento) ? \Carbon\Carbon::parse($reserva->clienteSolicitante->data_nascimento)->format('d/m/Y') : '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Sexo" />
                    <x-admin.select name="sexo" id="sexo"
                        :items="['M' => 'Masculino', 'F' => 'Feminino']"
                        :selectedItem="old('sexo', $reserva->clienteSolicitante->sexo ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Estado Civil" />
                    <x-admin.select name="estado_civil" id="estado_civil"
                        :items="['Solteiro' => 'Solteiro', 'Casado' => 'Casado', 'Divorciado' => 'Divorciado', 'Viúvo' => 'Viúvo']"
                        :selectedItem="old('estado_civil', $reserva->clienteSolicitante->estado_civil ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="3">
                    <x-admin.label label="Nacionalidade" />
                    <x-admin.text name="nacionalidade" id="nacionalidade" :value="old('nacionalidade', $reserva->clienteSolicitante->nacionalidade ?? '')"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="3">
                    <x-admin.label label="CEP" />
                    <x-admin.text name="cep" id="cep_pf" :value="old('cep', $reserva->clienteSolicitante->cep ?? '')">
                        <x-slot name="append">
                            <button type="button" id="buscarCepButton" class="btn btn-secondary">Buscar</button>
                        </x-slot>
                    </x-admin.text>
                </x-admin.field>
                <x-admin.field cols="5">
                    <x-admin.label label="Endereço" />
                    <x-admin.text name="endereco" id="endereco" :value="old('endereco', $reserva->clienteSolicitante->endereco ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="2">
                    <x-admin.label label="Número" />
                    <x-admin.text name="numero" id="numero_pf" :value="old('numero', $reserva->clienteSolicitante->numero ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="2">
                    <x-admin.label label="Compl." />
                    <x-admin.text name="complemento" id="complemento" :value="old('complemento', $reserva->clienteSolicitante->complemento ?? '')"/>
                </x-admin.field>
            </x-admin.field-group>

            <x-admin.field-group>
                <x-admin.field cols="4">
                    <x-admin.label label="Bairro" />
                    <x-admin.text name="bairro" id="bairro" :value="old('bairro', $reserva->clienteSolicitante->bairro ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="4">
                    <x-admin.label label="Cidade" />
                    <x-admin.text name="cidade" id="cidade" :value="old('cidade', $reserva->clienteSolicitante->cidade ?? '')"/>
                </x-admin.field>
                <x-admin.field cols="2">
                    <x-admin.label label="Estado (UF)" />
                    <x-admin.text name="estado" id="estado_pf" :value="old('estado', $reserva->clienteSolicitante->estado ?? '')" maxlength="2" placeholder="SP"/>
                </x-admin.field>
                <x-admin.field cols="2">
                    <x-admin.label label="País" />
                    <x-admin.text name="pais" id="pais" :value="old('pais', $reserva->clienteSolicitante->pais ?? '')"/>
                </x-admin.field>
            </x-admin.field-group>
        </div>

        
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
        @endif
        {{-- Fim do @else (modo edição) --}}

        @if ($edit && !in_array($reserva->situacao_reserva, ['CANCELADA', 'FINALIZADO']))
            <hr class="mt-4"/>
            <h5><i class="fas fa-sign-in-alt"></i> Check-in</h5>

            @if (in_array($reserva->situacao_reserva, ['HOSPEDADO', 'NO SHOW', 'cancelado']))
                <div class="alert alert-info" style="background: {{\App\Models\Reserva::SITUACOESRESERVA[$reserva->situacao_reserva]['background']}}; color: white">
                    O status da reserva já foi atualizado para: <strong>{{ $reserva->situacao_reserva }}.</strong>
                    <br/><br/>
                    <strong>Data da operação: </strong> {{$reserva->checkin ? timestamp_br($reserva->checkin->checkin_at) : '' }}
                    <input class="form-check-input" type="hidden" name="situacao_reserva" id="confirmarCheckin" value={{ $reserva->situacao_reserva }}>
                </div>
            @else
                <div class="d-flex justify-content-start mt-2" style="gap: 20px">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="situacao_reserva" id="confirmarCheckin" value="hospedado" {{ $reserva->situacao_reserva == 'hospedado' ? 'checked' : '' }}>
                        <label class="form-check-label btn btn-success" for="confirmarCheckin">
                            <i class="fas fa-check"></i> Check-in
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="situacao_reserva" id="noShow" value="no show" {{ $reserva->situacao_reserva == 'no show' ? 'checked' : '' }}>
                        <label class="form-check-label btn btn-warning" for="noShow">
                            <i class="fas fa-times-circle"></i> No Show
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="situacao_reserva" id="cancelado" value="cancelada" {{ $reserva->situacao_reserva == 'cancelada' ? 'checked' : '' }}>
                        <label class="form-check-label btn btn-danger" for="cancelado">
                            <i class="fas fa-ban"></i> Cancelado
                        </label>
                    </div>
                </div>

                <div class="mt-3">
                    <button id="confirmCheckinButton" class="btn btn-primary" style="display: none;">Confirmar</button>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const checkinRadios = document.querySelectorAll('input[name="situacao_reserva"][type="radio"]');
                        const confirmCheckinButton = document.getElementById('confirmCheckinButton');

                        checkinRadios.forEach(radio => {
                            radio.addEventListener('change', function () {
                                confirmCheckinButton.style.display = 'block';
                            });
                        });
                    });
                </script>
            @endif
        @endif

        {{-- Edição de período foi unificada no carrinho (aba Disponibilidade/Pagamento) --}}
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSolicitanteSelect = document.querySelector('select[name="tipo_solicitante"]');
        const tipoReservaSelect = document.querySelector('select[name="tipo_reserva"]');
        const situacaoReservaSelect = document.querySelector('select[name="situacao_reserva"]');

        const preReservaHide = document.getElementById('pre-reserva-hide');
        const preReservaHideRequired = preReservaHide ? preReservaHide.querySelectorAll('input, select, textarea') : [];


        function esconderPreReserva() {
            if (!preReservaHide || !situacaoReservaSelect) return;
            if (situacaoReservaSelect.value === 'PRÉ RESERVA') {
                preReservaHide.style.display = 'none';
            } else {
                preReservaHide.style.display = 'block';
            }
        }
        esconderPreReserva();

        if (situacaoReservaSelect) {
            situacaoReservaSelect.addEventListener('change', esconderPreReserva);
        }

        const pfHideFields = document.querySelectorAll('.pf-hide');

        // Função para atualizar a visibilidade dos campos
        function atualizarCampos() {
            if (!tipoSolicitanteSelect) return;
            if (tipoSolicitanteSelect.value === 'PJ') {
                pfHideFields.forEach(field => field.style.display = 'flex');
            } else {
                pfHideFields.forEach(field => field.style.display = 'none');
            }
        }

        // Atualiza os campos ao carregar a página
        atualizarCampos();

        // Adiciona um evento de mudança ao select
        if (tipoSolicitanteSelect) {
            tipoSolicitanteSelect.addEventListener('change', atualizarCampos);
        }

        // Exibir/ocultar opções de Day Use
        const fieldComCafe = document.getElementById('field-com-cafe');
        function atualizarCamposDayUse() {
            if (!fieldComCafe) return;
            if (tipoReservaSelect && tipoReservaSelect.value === 'DAY_USE') {
                fieldComCafe.style.display = 'block';
            } else {
                fieldComCafe.style.display = 'none';
                const comCafeInput = document.getElementById('com_cafe');
                if (comCafeInput) {
                    comCafeInput.checked = false;
                }
            }
        }

        if (tipoReservaSelect) {
            atualizarCamposDayUse();
            tipoReservaSelect.addEventListener('change', atualizarCamposDayUse);
        }
  

        // Select2 AJAX para busca de cliente por nome
        setTimeout(function() {
            var $buscaCliente = $('#buscaClienteSelect');
            
            if ($buscaCliente.data('select2')) {
                $buscaCliente.select2('destroy');
            }
            
            $buscaCliente.select2({
                dropdownParent: $buscaCliente.parent(),
                language: 'pt-BR',
                ajax: {
                    url: '{{ route("admin.clientes.search") }}',
                    dataType: 'json',
                    delay: 400,
                    cache: false,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data.results || [] };
                    }
                },
                minimumInputLength: 2,
                placeholder: 'Digite o nome ou CPF para buscar...',
                allowClear: true,
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text || data.id;
                }
            });

            $buscaCliente.on('select2:select', function (e) {
                var cliente = e.params.data;

                document.getElementById('nomeSolicitante').value   = cliente.nome || '';
                document.getElementById('cpf').value               = cliente.cpf || '';
                document.getElementById('modal_email').value       = cliente.email || '';
                document.getElementById('celular').value           = cliente.celular || '';
                document.getElementById('telefone').value          = cliente.telefone || '';
                document.getElementById('rg').value                = cliente.rg || '';
                document.getElementById('passaporte').value        = cliente.passaporte || '';
                document.getElementById('orgao_expedidor').value   = cliente.orgao_expedidor || '';
                document.getElementById('profissao').value         = cliente.profissao || '';
                document.getElementById('sexo').value              = cliente.sexo || '';
                document.getElementById('estado_civil').value      = cliente.estado_civil || '';
                document.getElementById('nacionalidade').value     = cliente.nacionalidade || '';
                if (cliente.data_nascimento) {
                    var p = cliente.data_nascimento.split('-');
                    document.getElementById('data_nascimento').value = p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : cliente.data_nascimento;
                }
                document.getElementById('cep_pf').value            = cliente.cep || '';
                document.getElementById('endereco').value          = cliente.endereco || '';
                document.getElementById('numero_pf').value         = cliente.numero || '';
                document.getElementById('complemento').value       = cliente.complemento || '';
                document.getElementById('bairro').value            = cliente.bairro || '';
                document.getElementById('cidade').value            = cliente.cidade || '';
                document.getElementById('estado_pf').value         = cliente.estado || '';
                document.getElementById('pais').value              = cliente.pais || '';

                // Exibir campos ocultos pelo modo Pré Reserva
                if (preReservaHide) preReservaHide.style.display = 'block';
            });
        }, 300);

        function mapEstadoToUF(estado) {
            if (!estado) return '';
            estado = (estado || '').trim();
            if (estado.length === 2) return estado.toUpperCase();

            const map = {
                'acre': 'AC', 'alagoas': 'AL', 'amapá': 'AP', 'amapa': 'AP', 'amazonas': 'AM',
                'bahia': 'BA', 'ceará': 'CE', 'ceara': 'CE', 'distrito federal': 'DF',
                'espírito santo': 'ES', 'espirito santo': 'ES', 'goiás': 'GO', 'goias': 'GO',
                'maranhão': 'MA', 'maranhao': 'MA', 'mato grosso': 'MT', 'mato grosso do sul': 'MS',
                'minas gerais': 'MG', 'pará': 'PA', 'para': 'PA', 'paraíba': 'PB', 'paraiba': 'PB',
                'paraná': 'PR', 'parana': 'PR', 'pernambuco': 'PE', 'piauí': 'PI', 'piaui': 'PI',
                'rio de janeiro': 'RJ', 'rio grande do norte': 'RN', 'rio grande do sul': 'RS',
                'rondônia': 'RO', 'rondonia': 'RO', 'roraima': 'RR', 'santa catarina': 'SC',
                'são paulo': 'SP', 'sao paulo': 'SP', 'sergipe': 'SE', 'tocantins': 'TO',
            };
            return map[estado.toLowerCase()] || estado;
        }

        const buscarCpfButton = document.getElementById('buscarCpfButton');
        const cpfInput = document.getElementById('cpf');
        const cpfError = document.getElementById('cpfError');
        const clienteInfo = document.getElementById('clienteInfo');
        const modalElement = document.getElementById('criarClienteModal');
        const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
        if (typeof $ !== 'undefined' && $.fn.mask) {
            $('#cpf').mask('000.000.000-00', {reverse: true});
            $('#responsavelCpf').mask('000.000.000-00', {reverse: true});
            $('#responsavelReservaCpf').mask('000.000.000-00', {reverse: true});
            $('#celular').mask('00 00000-0000', {reverse: true});
        }

        if (buscarCpfButton) buscarCpfButton.addEventListener('click', function () {
            const cpf = (cpfInput && cpfInput.value) ? cpfInput.value.trim() : '';
            if (cpfError) cpfError.style.display = 'none';
            if (!cpf) return;

            fetch('/admin/clientes/cpf/' + encodeURIComponent(cpf), {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Cliente não encontrado');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.id) {
                        throw new Error('Cliente não encontrado');
                    }
                    document.getElementById('nomeSolicitante').value   = data.nome ?? '';
                    document.getElementById('cpf').value               = data.cpf ?? '';
                    document.getElementById('modal_email').value       = data.email ?? '';
                    document.getElementById('celular').value           = data.celular ?? '';
                    document.getElementById('telefone').value          = data.telefone ?? '';
                    document.getElementById('rg').value                = data.rg ?? '';
                    document.getElementById('passaporte').value        = data.passaporte ?? '';
                    document.getElementById('orgao_expedidor').value   = data.orgao_expedidor ?? '';
                    document.getElementById('profissao').value         = data.profissao ?? '';
                    document.getElementById('sexo').value              = data.sexo ?? '';
                    document.getElementById('estado_civil').value      = data.estado_civil ?? '';
                    document.getElementById('nacionalidade').value     = data.nacionalidade ?? '';
                    if (data.data_nascimento) {
                        var p = data.data_nascimento.split('-');
                        document.getElementById('data_nascimento').value = p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : data.data_nascimento;
                    }
                    document.getElementById('cep_pf').value            = data.cep ?? '';
                    document.getElementById('endereco').value          = data.endereco ?? '';
                    document.getElementById('numero_pf').value         = data.numero ?? '';
                    document.getElementById('complemento').value       = data.complemento ?? '';
                    document.getElementById('bairro').value            = data.bairro ?? '';
                    document.getElementById('cidade').value            = data.cidade ?? '';
                    document.getElementById('estado_pf').value         = mapEstadoToUF(data.estado) || (data.estado ?? '');
                    document.getElementById('pais').value              = data.pais ?? '';

                    if (preReservaHide) preReservaHide.style.display = 'block';
                })
                .catch(function (error) {
                    if (cpfError) cpfError.style.display = 'block';
                    console.log(error);
                    setTimeout(function () {
                        if (cpfError) cpfError.style.display = 'none';
                    }, 2000);
                });
        });

        const verificarCnpjFaturamentoButton = document.getElementById('verificarCnpjFaturamento');
        const verificarCnpjSolicitanteButton = document.getElementById('verificarCnpjSolicitante');
        const cnpjFaturamentoError = document.getElementById('cnpjFaturamentoError');

        const cnpjSolicitanteError = document.getElementById('cnpjSolicitanteError');

        if (verificarCnpjFaturamentoButton) verificarCnpjFaturamentoButton.addEventListener('click', function () {
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

        if (verificarCnpjSolicitanteButton) verificarCnpjSolicitanteButton.addEventListener('click', function () {
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


        var copyFaturamentoEl = document.getElementById('copy_faturamento_to_solicitante');
        if (copyFaturamentoEl) copyFaturamentoEl.addEventListener('change', function() {
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


        // Busca de CEP via ViaCEP
        function preencherEnderecoPorCep(cep) {
            cep = cep.replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.erro) { alert('CEP não encontrado.'); return; }
                    document.getElementById('endereco').value  = d.logradouro || '';
                    document.getElementById('bairro').value    = d.bairro     || '';
                    document.getElementById('cidade').value    = d.localidade || '';
                    document.getElementById('estado_pf').value = d.uf         || '';
                    if (!document.getElementById('pais').value) {
                        document.getElementById('pais').value = 'Brasil';
                    }
                })
                .catch(function() { alert('Erro ao buscar CEP.'); });
        }

        var buscarCepBtn = document.getElementById('buscarCepButton');
        var cepInput     = document.getElementById('cep_pf');
        if (buscarCepBtn) {
            buscarCepBtn.addEventListener('click', function() {
                preencherEnderecoPorCep(cepInput.value);
            });
        }
        if (cepInput) {
            cepInput.addEventListener('blur', function() {
                preencherEnderecoPorCep(this.value);
            });
        }

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