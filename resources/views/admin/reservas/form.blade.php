@extends('layouts.admin.master')
{{-- @php dd($reserva) @endphp --}}

@section('title', ($edit ? 'Editando' : 'Inserindo'). ' Reserva')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.reserva-form save-route="admin.reservas.save" back-route="admin.reservas.index" submit-title="Finalizar" class="reservarForm">
        <div class="col-md-12">
            <ul class="nav nav-tabs mb-4" id="reservaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="informacoes-gerais-tab" data-toggle="tab" href="#informacoes-gerais" role="tab" aria-controls="informacoes-gerais" aria-selected="true">
                        <i class="fas fa-info-circle"></i> Informações Gerais
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$edit ? '' : 'disabled'}}" id="disponibilidade-tab" data-toggle="tab" href="#disponibilidade" role="tab" aria-controls="disponibilidade" aria-selected="false">
                        <i class="fas fa-calendar-alt"></i> Disponibilidade
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link {{$edit ? '' : 'disabled'}}" id="pagamento-tab" data-toggle="tab" href="#pagamento" role="tab" aria-controls="pagamento" aria-selected="false">
                        <i class="fas fa-credit-card"></i> Pagamento
                    </a>
                </li>
                
            </ul>

            <!-- Conteúdo das tabs -->
            <div class="tab-content " id="reservaTabContent">
                <!-- Tab 1: Informações Gerais -->
                @if($edit)
                    <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">
                @endif
                @include('admin.reservas.partials.informacoes-gerais')

                <!-- Tab 2: Disponibilidade -->
                @include('admin.reservas.partials.disponibilidade')

                <!-- Tab 3: Pagamento -->
                @include('admin.reservas.partials.pagamento')

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
            if (window.location.href.indexOf('edit') > -1) {
                @php
                    $cartItems = $reserva->getCartSerializedAttribute();
                    $precosDiarios = $reserva->getPrecosDiarios();
                    // dd($precosDiarios);
                @endphp
                
                @foreach ($cartItems as $item)
                    adicionarQuartoAoCart(
                        "{{ $item['quartoId'] ?? '' }}",
                        "{{ $item['quartoNumero'] ?? '' }}",
                        "{{ $item['quartoAndar'] ?? '' }}",
                        "{{ $item['quartoClassificacao'] ?? '' }}",
                        "{{ $item['tipo_acomodacao'] ?? '' }}",
                        "{{ $item['nome'] ?? '' }}",
                        "{{ $item['cpf'] ?? '' }}",
                        "{{ $item['dataCheckin'] ?? '' }}",
                        "{{ $item['dataCheckout'] ?? '' }}",
                        {!! json_encode($precosDiarios ?? '') !!},
                        "{{ $item['total'] ?? '' }}",
                        "{{ $item['criancas_ate_7'] ?? '' }}",
                        "{{ $item['criancas_mais_7'] ?? '' }}",
                        "{{ $item['adultos'] ?? '' }}"
                    );
                @endforeach
            }
        });
    </script>

    <script src="{{ asset('assets/admin/reserva.js') }}"></script>
@endsection