@extends('layouts.admin.master')
@php 
use Carbon\Carbon; 

$contadores = collect($statusMesaNoDia)->countBy('status');
@endphp

@section('title', 'Dashboard')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')

<div class="container my-4">
    <!-- Indicadores de status das mesas -->
    <div class="row text-center mb-4" id="indicadores">
        <div class="col-md-2 col-6">
            <div class="circle-indicator mx-auto" style="background-color: #00A65B;">
                {{ $totalMesasLivres }}
            </div>
            <div>
                <p>
                    <i class="fas fa-circle" style="color: #00A65B;"></i> Livre
                </p>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="circle-indicator mx-auto" style="background-color: #b70000;">
                {{ $totalMesasOcupadas }}
            </div>
            <div>
                <p>
                    <i class="fas fa-circle" style="color: #b70000;"></i> Ocupada
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Seção de mesas -->
<div class="row mb-4">
  @foreach($statusMesaNoDia as $status)
      @php
          $corDeFundo = $status['status'] === 'Ocupada' ? '#b70000' : '#00A65B';
          $label = $status['status'] === 'Ocupada' ? 'Ocupada' : 'Livre';
      @endphp
      <div class="col-lg-2 col-md-2 col-sm-4 col-6 mb-4 mesas">
          @if($status['status'] === 'Ocupada' && $status['pedido'])
              <a href="{{ route('admin.bar.pedidos.edit', ['id' => $status['pedido']->id]) }}" class="card room-card shadow-sm h-100" style="text-decoration: none;">
          @else
              <div class="card room-card shadow-sm h-100" data-toggle="modal" 
                data-target="#modal-nova-comanda" data-mesa-id="{{ $status['mesa']->id }}" data-mesa-numero="{{ $status['mesa']->numero }}">
          @endif
              <div class="card-body text-center p-2" style="color: white; background-color: {{ $corDeFundo }};"
                  @if($status['status'] === 'Ocupada' && $status['pedido'])
                      data-toggle="tooltip" data-html="true" title="
                          <div class='d-flex justify-content-between' style='font-size: 12px;'>
                              <strong>Pedido ID:</strong> <span>{{ $status['pedido']->id }}</span>
                          </div>
                          <div class='d-flex justify-content-between' style='font-size: 12px;'>
                              <strong>Status:</strong> <span>{{ $status['pedido']->status }}</span>
                          </div>
                          <div class='d-flex justify-content-between' style='font-size: 12px;'>
                              <strong>Total:</strong> <span>{{ $status['pedido']->total }}</span>
                          </div>
                      "
                  @endif
              >
                  <!-- Número da Mesa -->
                  <h5 class="card-title mb-2">{{ $status['mesa']->numero }}</h5>
                  
                  <!-- Ícone baseado no status -->
                  @if($status['status'] === 'Ocupada')
                      <i class="fas fa-lock fa-2x text-danger"></i> <!-- Ícone de cadeado -->
                  @else
                      <i class="fas fa-thumbs-up fa-2x" style="color: #0d6c0d;"></i> <!-- Ícone de joinha -->
                  @endif
                  
                  <p class="card-text mesa">{{ $label }}</p>
              </div>
          @if($status['status'] === 'Ocupada' && $status['pedido'])
              </a>
          @else
              </div>
          @endif
      </div>
  @endforeach
</div>


<!-- Modal de Abertura de Mesa -->
<div class="modal fade" id="modal-nova-comanda" tabindex="-1" role="dialog" aria-labelledby="modalNovaComandaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content modal-payments">
      <div class="modal-header">
        <h4 class="modal-title text-uppercase text-bold" id="modalNovaComandaLabel">
          <span><i class="fas fa-user"></i> Nova Venda - Mesa: <span id="numero-mesa"></span> </span>
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <x-admin.form save-route="admin.bar.pedidos.save" back-route="admin.bar.home" submitTitle="Abrir">
          <input type="hidden" name="mesa_id" id="mesa-id">

          <x-admin.field-group>
            <!-- Reserva -->
            <x-admin.field cols="12">
                <x-admin.label label="Reserva (UH)"/> <small class="text-muted">(Cliente - Quarto)</small>
                <x-admin.select name="reserva_id" id="reserva-select" class="select2 js-example-programmatic form-control"
                    :items="$reservas->mapWithKeys(function($reserva) {
                        $clienteNome = $reserva->clienteResponsavel->nome ?? 'GR: ' . $reserva->clienteSolicitante->nome;
                        $quartoNumero = $reserva->quarto->numero ?? '';
                        return [$reserva->id => $quartoNumero . ' -- ' . $clienteNome ];
                    })" required>
                </x-admin.select>
            </x-admin.field>
          </x-admin.field-group>

          <x-admin.field-group>
            <!-- DayUse (UH) -->
            <x-admin.field cols="12">
              <x-admin.label label="DayUse (UH)"/>
              <x-admin.select name="dayuse" id="dayuse-select" class="select2 js-example-programmatic form-control">
                <option>DayUse</option>
              </x-admin.select>
            </x-admin.field>
          </x-admin.field-group>
        </x-admin.form>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 

        $('#modal-nova-comanda').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botão que acionou o modal
            var mesaId = button.data('mesa-id'); // Extrai o ID da mesa dos atributos de dados

            var mesaNumero = button.data('mesa-numero'); // Extrai o número da mesa dos atributos de dados

            var modal = $(this);
            modal.find('#numero-mesa').text(mesaNumero); // Define o número da mesa no título do modal

            modal.find('#mesa-id').val(mesaId); // Define o valor do campo oculto com o ID da mesa
        });
    });
</script>

@endsection