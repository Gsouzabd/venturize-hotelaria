@extends('layouts.admin.master')
@php 
use Carbon\Carbon; 
use App\Models\Reserva;

$contadores = collect($statusQuartoNoDia)->countBy('status');

// Criar uma cópia de SITUACOESRESERVA e alterar os backgrounds de HOSPEDADO e RESERVADO
$situacoesReserva = Reserva::SITUACOESRESERVA;
$situacoesReserva['HOSPEDADO']['background'] = '#b70000';
$situacoesReserva['RESERVADO']['background'] = '#033287';
@endphp
@section('title', 'Dashboard')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')

<div class="container my-4">
    <!-- Indicadores de status dos quartos -->
    <div class="row text-center mb-4" id="indicadores">
        @foreach (array_keys($situacoesReserva) as $key)
            @php
                $situacao = $situacoesReserva[$key];
            @endphp
            <div class="col-md-2 col-6">
                <div class="circle-indicator mx-auto" style="background-color: {{ $situacao['background'] }};">
                    {{ $contadores[$key] ?? 0 }}
                </div>
                <div>
                    <p>
                        <i class="fas fa-circle" style="color: {{ $situacao['background'] }}"></i> {{ $situacao['label'] }}
                    </p>
                </div>
            </div>
        @endforeach
        <div class="col-md-2 col-6">
            <div class="circle-indicator mx-auto" style="background-color: #00A65B;">
                {{ $totalDisponiveis }}
            </div>
            <div>
                <p>
                    <i class="fas fa-circle" style="color: #00A65B;"></i> Livre
                </p>
            </div>
        </div>
    </div>
</div>

    
    <!-- Seção de quartos por andar -->
    @php
        $quartosPorAndar = collect($statusQuartoNoDia)->groupBy(function($status) {
            return $status['quarto']->andar;
        });
    @endphp
    
    @foreach($quartosPorAndar as $andar => $quartos)
        <div class="row mb-4 andar-block">
            <div class="col-12">
                <span class="andar">Andar: {{ $andar }}</span>
            </div>
                        @php
                // Criar uma cópia de SITUACOESRESERVA e alterar os backgrounds de HOSPEDADO e RESERVADO
                $situacoesReserva = \App\Models\Reserva::SITUACOESRESERVA;
                $situacoesReserva['HOSPEDADO']['background'] = '#b70000';
                $situacoesReserva['RESERVADO']['background'] = '#033287';
            @endphp
            
            @foreach($quartos as $status)
                @php
                    $situacao = $status['status'];
                    $corDeFundo = $situacoesReserva[$situacao]['background'] ?? '#00a65a';
                    $label = $situacoesReserva[$situacao]['label'] ?? 'Livre';
                @endphp
                <div class="col-lg-2 col-md-2 col-sm-4 col-6 mb-4 quartos">
                    <div class="card room-card shadow-sm h-100">
                        <div class="card-body text-center p-2" style="color: white; background-color: {{ $corDeFundo }};"
                            @if($status['status'] != 'Livre' && $status['reserva'])
                                data-toggle="tooltip" data-html="true" title="
                                    <div class='d-flex justify-content-between' style='font-size: 12px;'>
                                        <strong>Cliente:</strong> <span>{{ ucwords(strtolower($status['reserva']->clienteResponsavel->nome ?? 'N/A')) }}</span>
                                    </div>
                                    <div class='d-flex justify-content-between' style='font-size: 12px;'>
                                        <strong>Check-in:</strong> <span>{{ \Carbon\Carbon::parse($status['reserva']->data_checkin)->format('d-m-Y H:i') }}</span>
                                    </div>
                                    <div class='d-flex justify-content-between' style='font-size: 12px;'>
                                        <strong>Check-out:</strong> <span>{{ \Carbon\Carbon::parse($status['reserva']->data_checkout)->format('d-m-Y H:i') }}</span>
                                    </div>
                                "
                            @endif
                        >
                            <!-- Número do Quarto -->
                            <h5 class="card-title mb-2">{{ $status['quarto']->numero }}</h5>
                            
                            <!-- Nome do Quarto (Classificação) -->
                            <p class="room-name">{{ $status['quarto']->classificacao }}</p>
                            
                            <!-- Ícone baseado no status -->
                            @if($status['status'] == 'HOSPEDADO')
                                <i class="fas fa-lock fa-2x text-danger"></i> <!-- Ícone de cadeado -->
                            @elseif($status['status'] == 'Livre')
                                <i class="fas fa-thumbs-up fa-2x" style="color: #0d6c0d;"></i> <!-- Ícone de joinha -->
                            @elseif($status['status'] == 'RESERVADO')
                                <i class="fas fa-dollar-sign fa-2x" style="color: #062381;"></i> <!-- Ícone de pagamento -->
                            @else
                                <i class="fas fa-question-circle fa-2x text-warning"></i> <!-- Ícone de status desconhecido -->
                            @endif
                            
                            <p class="card-text quarto">{{ $label }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
    
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip(); 
        });
    </script>







<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Reservas - Mês Atual: {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('F') }}</h5>
            </div>
            <div class="card-body">
                <canvas id="reservasPorMesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Reservas (Mantido igual) -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Últimas 5 Reservas</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Quarto</th>
                            <th>Operador</th>
                            <th>Situação</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimasReservas as $reserva)
                        <tr>
                            <td>{{ $reserva->id }}</td>
                            <td>{{ $reserva->clienteSolicitante->nome}}</td>
                            <td>{{ $reserva->quarto->numero }}</td>
                            <td>{{ $reserva->operador->nome }}</td>
                            <td>
                                <span class="badge" style="background: {{Reserva::SITUACOESRESERVA[$reserva->situacao_reserva]['background']}}; color: white;">
                                    {{ $reserva->situacao_reserva }}
                                </span>
                            </td>
                            <td>{{ timestamp_br($reserva->created_at) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Função para gerar os dias do mês atual
    function getDaysInMonth(month, year) {
        const date = new Date(year, month, 1);
        const days = [];
        while (date.getMonth() === month) {
            days.push(new Date(date).getDate()); // Pega o dia
            date.setDate(date.getDate() + 1);
        }
        return days;
    }

    const now = new Date();
    const currentMonth = now.getMonth(); // Mês atual (0-11)
    const currentYear = now.getFullYear(); // Ano atual

    const daysInMonth = getDaysInMonth(currentMonth, currentYear);

    // Obtém os dados do PHP e preenche os valores para os dias sem reservas como zero
    const reservasData = @json($reservasPorMes->pluck('total', 'dia')->toArray());

    const reservasByDay = daysInMonth.map(day => reservasData[day] || 0); // Preenche os dias sem reservas com 0

    const ctx = document.getElementById('reservasPorMesChart').getContext('2d');
    const reservasPorMesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: daysInMonth, // Dias do mês
            datasets: [{
                label: 'Reservas',
                data: reservasByDay, // Dados correspondentes aos dias
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.2 // Suaviza as curvas da linha
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    ticks: {
                        callback: function(value, index) {
                            return index + 1; // Mostrar os dias do mês
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false // Remove a legenda para focar na linha
                }
            }
        }
    });
</script>

@endpush
