@extends('layouts.admin.master')

@section('title', 'Dashboard')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <div>Resumo Geral</div>
    </x-admin.page-header>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card card-bg-info shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total de Usuários</h5>
                <p class="card-text">{{ $totalUsuarios }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card card-bg-success shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total de Clientes</h5>
                <p class="card-text">{{ $totalClientes }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card card-bg-warning shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total de Quartos</h5>
                <p class="card-text">{{ $totalQuartos }}</p>
            </div>
        </div>
    </div>
    
    <!-- Segunda Linha de Cartões -->
    <div class="col-md-4 mb-4">
        <div class="card card-bg-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Reservas Ativas</h5>
                <p class="card-text">{{ $reservasAtivas }}</p>
            </div>
        </div>
    </div>
</div>
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
                                <span class="badge badge-{{ $reserva->situacao_reserva == 'CONFIRMADA' ? 'success' : 'warning' }}">
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
