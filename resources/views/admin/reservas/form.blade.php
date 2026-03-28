@extends('layouts.admin.master')
{{-- @php dd($reserva) @endphp --}}

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm" isEdit="{{$edit}}">
        <input type="hidden" name="reserva_id" value="{{$edit ? $reserva->id : ''}}">
        <input type="hidden" name="is_edit" value="{{$edit ?? ''}}">

        <div class="col-md-12">
            <ul class="nav nav-tabs mb-4" id="reservaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="informacoes-gerais-tab" data-toggle="tab" href="#informacoes-gerais" role="tab" aria-controls="informacoes-gerais" aria-selected="true">
                        <i class="fas fa-info-circle"></i> Informações Gerais
                    </a>
                </li>
                @if ($reserva->situacao_reserva != 'HOSPEDADO' && $reserva->situacao_reserva != 'FINALIZADO')
                    <li class="nav-item">
                        <a class="nav-link {{$edit ? '' : 'disabled'}}" id="disponibilidade-tab" data-toggle="tab" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false" data-label-default="Disponibilidade" data-label-dayuse="Day Use">
                            <i class="fas fa-calendar-alt"></i> <span id="disponibilidade-tab-label">Disponibilidade</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$edit ? '' : 'disabled'}}" id="pagamento-tab" data-toggle="tab" href="#pagamento" role="tab" aria-controls="pagamento" aria-selected="false">
                            <i class="fas fa-credit-card"></i> Pagamento
                        </a>
                    </li>
                @endif
                
                @if ($edit)
                    <li class="nav-item">
                        <a class="nav-link" id="acompanhantes-tab" data-toggle="tab" href="#acompanhantes" role="tab" aria-controls="acompanhantes" aria-selected="false">
                            <i class="fas fa-users"></i> Acompanhantes
                        </a>
                    </li>
                @endif
                @if ($reserva->situacao_reserva == 'HOSPEDADO')
                    <li class="nav-item">
                        <a class="nav-link" id="consumo-tab" href="{{ route('admin.bar.pedidos.edit', ['id' => $pedido->id]) }}">
                            <i class="fas fa-utensils"></i> Consumo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="refeicoes-tab" data-toggle="tab" href="#refeicoes" role="tab" aria-controls="refeicoes" aria-selected="false">
                            <i class="fas fa-coffee"></i> Refeições
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="transferencia-tab" data-toggle="tab" href="#transferencia" role="tab" aria-controls="transferencia" aria-selected="false">
                            <i class="fas fa-exchange-alt"></i> Transferência
                        </a>
                    </li>
                @endif
                @if ($reserva->situacao_reserva == 'HOSPEDADO' || $reserva->situacao_reserva == 'FINALIZADO')

                    <li class="nav-item">
                        <a class="nav-link {{$reserva->situacao_reserva == 'FINALIZADO' ? 'checkin-done' : ''}}" id="checkout-tab" data-toggle="tab" href="#checkout" role="tab" aria-controls="checkout" aria-selected="false">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </a>
                    </li>

                @endif
            </ul>

            @if ($reserva->situacao_reserva == 'HOSPEDADO' || $reserva->situacao_reserva == 'FINALIZADO')
                <a class="btn btn-primary float-right" href="{{ route('admin.reserva.gerarFichaNacional', ['id' => $reserva->id]) }}" target="_blank">
                    <i class="fas fa-file-alt"></i> Gerar Ficha Nacional
                </a>
                <a class="btn btn-secondary float-right mr-2" href="{{ route('admin.reservas.gerar-extrato', ['id' => $reserva->id]) }}" target="_blank">
                    <i class="fas fa-file-pdf"></i> Gerar Extrato
                </a>
            @endif

            <!-- Conteúdo das tabs -->
            <div class="tab-content" id="reservaTabContent">
                <!-- Tab 1: Informações Gerais -->
                @include('admin.reservas.partials.informacoes-gerais')
                <!-- Tab 2: Disponibilidade -->
                @include('admin.reservas.partials.disponibilidade')
                <!-- Tab 3: Pagamento -->
                @if ($reserva->situacao_reserva != 'HOSPEDADO' && $reserva->situacao_reserva != 'FINALIZADO')
                    @include('admin.reservas.partials.pagamento')
                @endif
                @if ($reserva->situacao_reserva == 'HOSPEDADO' || $reserva->situacao_reserva == 'FINALIZADO')
                    <!-- Tab 5: Check-out -->
                    @include('admin.reservas.partials.checkout')
                @endif
                @if ($edit)
                    <!-- Tab: Acompanhantes -->
                    @include('admin.reservas.partials.acompanhantes')
                @endif
                @if ($reserva->situacao_reserva == 'HOSPEDADO')
                    <!-- Tab: Refeições -->
                    @include('admin.reservas.partials.refeicoes')
                    <!-- Tab: Transferência -->
                    @include('admin.reservas.partials.transferencia')
                @endif

            </div>

            @if ($edit && $reserva->situacao_reserva == 'HOSPEDADO')
                <div class="mt-3 p-3 bg-light border rounded" style="position: sticky; bottom: 0; z-index: 10;">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-save"></i> Salvar Alterações da Reserva
                    </button>
                </div>
            @endif
        </div>

    </x-admin.reserva-form>
    
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
                        <input type="text" class="form-control" id="responsavelNome" name="responsavelNome" >
                    </div>
                    <div class="form-group" id="cpfGroup">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelCpf" name="responsavelCpf" >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="saveResponsavel">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="responsavelReservaModal" tabindex="-1" role="dialog" aria-labelledby="responsavelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responsavelReservaModalLabel">Informações do Responsável pela Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tipoReserva">Tipo de Reserva</label>
                        <select class="form-control" id="tipoReserva" name="tipoReserva" >
                            <option value="PRÉ RESERVA">Pré-Reserva</option>
                            <option value="RESERVADO">Reserva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="responsavelNome">Nome</label>
                        <input type="text" class="form-control" id="responsavelReservaNome" name="responsavelReservaNome" >
                    </div>
                    <div class="form-group" id="cpfGroup2" style="display: none;">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelReservaCpf" name="responsavelReservaCpf">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="solicitanteHospedeCheckboxModal">
                        <label class="form-check-label" for="solicitanteHospedeCheckboxModal">
                            Solicitante será o hóspede?
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="saveResponsavelReserva" data-dismiss="modal">Salvar</button>
                </div>
            </div>
        </div>
    </div>
    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            localStorage.removeItem('cart');
    
            // Script do cart quando for tela de edição
            @if ($edit)
                @php
                    $item = $reserva->getCartSerializedAttribute();
                    $precosDiarios = $reserva->getPrecosDiarios();
                    $acompanhantes = $reserva->acompanhantes;
                    // dd($precosDiarios);

                @endphp
                
                    adicionarQuartoAoCart(
                        "{{ $item['quartoId'] ?? '' }}",
                        "{{ $item['quartoNumero'] ?? '' }}",
                        "{{ $item['quartoAndar'] ?? '' }}",
                        "{{ $item['quartoClassificacao'] ?? '' }}",
                        "{{ $item['tipo_acomodacao'] ?? '' }}",
                        "{{ $reserva->clienteResponsavel->nome ?? '' }}",
                        "{{ $reserva->clienteResponsavel->cpf ?? '' }}",
                        "{{ $item['dataCheckin'] ?? '' }}",
                        "{{ $item['dataCheckout'] ?? '' }}",
                        {!! json_encode($precosDiarios ?? '') !!},
                        "{{ $item['total'] ?? '' }}",
                        "{{ $reserva->criancas_ate_7 ?? '' }}",
                        "{{ $reserva->criancas_mais_7 ?? '' }}",
                        "{{ $reserva->adultos ?? 1 }}",
                        {!! json_encode($acompanhantes ?? '') !!},
                        "{{ $item['quartoComposicao'] ?? '' }}",

                    );
            @endif
        });
    </script>

    <script src="{{ asset('assets/admin/reserva.js') }}?v={{ @filemtime(public_path('assets/admin/reserva.js')) }}"></script>
    <script>
        (function () {
            function activateReservaTabByLink(tabLink) {
                if (!tabLink) return;

                var target = tabLink.getAttribute('href');
                if (!target || target.charAt(0) !== '#') return;

                var targetPane = document.querySelector(target);
                if (!targetPane) return;

                var tabsContainer = document.getElementById('reservaTabs');
                if (tabsContainer) {
                    tabsContainer.querySelectorAll('.nav-link').forEach(function (link) {
                        link.classList.remove('active');
                        link.setAttribute('aria-selected', 'false');
                    });
                }

                var contentContainer = document.getElementById('reservaTabContent');
                if (contentContainer) {
                    contentContainer.querySelectorAll('.tab-pane').forEach(function (pane) {
                        pane.classList.remove('show', 'active');
                    });
                }

                tabLink.classList.add('active');
                tabLink.setAttribute('aria-selected', 'true');
                targetPane.classList.add('show', 'active');

                if (history.replaceState) {
                    history.replaceState(null, '', target);
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var editForm = document.querySelector('form.edit-form');
                if (editForm) {
                    editForm.addEventListener('submit', function (event) {
                        var submitter = event.submitter || document.activeElement;
                        if (!submitter) return;

                        var action = submitter.getAttribute('formaction') || this.getAttribute('action') || '';
                        var isTransferencia = action.indexOf('/transferir') !== -1;
                        var isRefeicoes = action.indexOf('/refeicoes') !== -1;

                        // Submits dessas abas devem sair como POST real (sem _method=PUT do form principal)
                        if (isTransferencia || isRefeicoes) {
                            var methodInput = this.querySelector('input[name="_method"]');
                            if (methodInput) {
                                methodInput.disabled = true;
                            }
                        }
                    });
                }

                var tabLinks = document.querySelectorAll('#reservaTabs .nav-link[data-toggle="tab"]');
                tabLinks.forEach(function (tabLink) {
                    tabLink.addEventListener('click', function (event) {
                        event.preventDefault();
                        activateReservaTabByLink(this);
                    });
                });

                if (window.location.hash) {
                    var linkFromHash = document.querySelector('#reservaTabs .nav-link[data-toggle="tab"][href="' + window.location.hash + '"]');
                    if (linkFromHash) {
                        activateReservaTabByLink(linkFromHash);
                    }
                }
            });
        })();
    </script>
@endsection