@extends('layouts.admin.master')
{{-- @php dd($reserva) @endphp --}}

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm">
        <input type="hidden" name="reserva_id" value="{{$edit ? $reserva->id : ''}}">
        <input type="hidden" name="is_edit" value="{{$edit ?? ''}}">

        <div class="col-md-12">
            <ul class="nav nav-tabs mb-4" id="reservaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="informacoes-gerais-tab" data-toggle="tab" href="#informacoes-gerais" role="tab" aria-controls="informacoes-gerais" aria-selected="true">
                        <i class="fas fa-info-circle"></i> Informações Gerais
                    </a>
                </li>
                @if ($edit && $reserva->situacao_reserva != 'HOSPEDADO')
                    <li class="nav-item">
                        <a class="nav-link {{$edit ? '' : 'disabled'}}" id="disponibilidade-tab" data-toggle="tab" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false">
                            <i class="fas fa-calendar-alt"></i> Disponibilidade
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$edit ? '' : 'disabled'}}" id="pagamento-tab" data-toggle="tab" href="#pagamento" role="tab" aria-controls="pagamento" aria-selected="false">
                            <i class="fas fa-credit-card"></i> Pagamento
                        </a>
                    </li>
                @endif
                @if ($edit && $reserva->situacao_reserva != 'CANCELADA')
                    @if (in_array($reserva->situacao_reserva, ['HOSPEDADO', 'NO SHOW', 'CANCELADO']))
                        <li class="nav-item">
                            <a class="nav-link {{$edit ? '' : 'disabled'}} checkin-done" id="checkin-tab" data-toggle="tab" href="#checkin" role="tab" aria-controls="checkin" aria-selected="false">
                                </i> Check-in <i class="fas fa-check-circle"></i>
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{$edit ? '' : 'disabled'}}" id="checkin-tab" data-toggle="tab" href="#checkin" role="tab" aria-controls="checkin" aria-selected="false">
                                <i class="fas fa-sign-in-alt"></i> Check-in
                            </a>
                        </li>
                    @endif
                @endif
                
                @if ($edit && $reserva->situacao_reserva == 'HOSPEDADO')
                <li class="nav-item">
                    <a class="nav-link" id="checkout-tab" data-toggle="tab" href="#checkout" role="tab" aria-controls="checkout" aria-selected="false">
                        <i class="fas fa-utensils"></i> Consumo 
                    </a>
                </li>
                    <li class="nav-item">
                        <a class="nav-link" id="checkout-tab" data-toggle="tab" href="#checkout" role="tab" aria-controls="checkout" aria-selected="false">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </a>
                    </li>
                @endif
            </ul>

            @if ($edit && $reserva->situacao_reserva == 'HOSPEDADO')
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
                @if ($edit && $reserva->situacao_reserva != 'HOSPEDADO')
                    @include('admin.reservas.partials.pagamento')
                @endif
                @if ($edit && $reserva->situacao_reserva == 'HOSPEDADO')
                    <!-- Tab 5: Check-out -->
                    @include('admin.reservas.partials.checkout')
                @endif
                <!-- Tab 4: Check-in -->
                @include('admin.reservas.partials.checkin')

            </div>
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
                        <input type="text" class="form-control" id="responsavelNome" name="responsavelNome" required>
                    </div>
                    <div class="form-group" id="cpfGroup">
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
                        <select class="form-control" id="tipoReserva" name="tipoReserva" required>
                            <option value="pre-reserva">Pré-Reserva</option>
                            <option value="reserva">Reserva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="responsavelNome">Nome</label>
                        <input type="text" class="form-control" id="responsavelReservaNome" name="responsavelReservaNome" required>
                    </div>
                    <div class="form-group" id="cpfGroup2" style="display: none;">
                        <label for="responsavelCpf">CPF</label>
                        <input type="text" class="form-control" id="responsavelReservaCpf" name="responsavelReservaCpf" required>
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
                    );
            @endif
        });
    </script>

    <script src="{{ asset('assets/admin/reserva.js') }}"></script>
@endsection