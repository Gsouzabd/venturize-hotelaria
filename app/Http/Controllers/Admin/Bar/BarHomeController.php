<?php

namespace App\Http\Controllers\Admin\Bar;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BarHomeController extends Controller
{
    public function index(Request $request)
    {
        $totalUsuarios = Usuario::count();
        $totalClientes = Cliente::count();
        $totalQuartos = Quarto::count();
        $quartosDisponiveis = Quarto::whereDoesntHave('reservas')->count();
        $reservasAtivas = Reserva::where('situacao_reserva', 'RESERVADO')->count();
        // Validação e filtragem dos parâmetros
        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial')) : Carbon::today();
        $intervaloDias = $request->input('intervalo', 30); // Intervalo padrão de 30 dias
        $intervaloDias = in_array($intervaloDias, [7, 15, 30, 60]) ? $intervaloDias : 30; // Verificação de intervalo

        // Data final com base no intervalo fornecido
        $dataFinal = $dataInicial->copy()->addDays($intervaloDias - 1); // -1 para garantir que o último dia seja incluído

        // Buscar reservas dentro do intervalo
        $reservas = Reserva::with(['clienteResponsavel', 'quarto'])
            ->where(function ($query) use ($dataInicial, $dataFinal) {
                $query->whereBetween('data_checkin', [$dataInicial, $dataFinal]) // Chegada dentro do intervalo
                    ->orWhereBetween('data_checkout', [$dataInicial, $dataFinal])  // Saída dentro do intervalo
                    ->orWhere(function ($q) use ($dataInicial, $dataFinal) {
                        $q->where('data_checkin', '<=', $dataInicial)           // Chegada antes ou no início do intervalo
                            ->where('data_checkout', '>=', $dataFinal);             // Saída depois ou no final do intervalo
                    });
            })
            ->get();


            // Total de usuários, clientes, quartos, etc.
            $totalUsuarios = Usuario::count();
            $totalClientes = Cliente::count();
            $totalQuartos = Quarto::count();
            // $quartosDisponiveis = Quarto::where('disponivel', true)->count();
            $reservasAtivas = Reserva::where('situacao_reserva', 'RESERVADO')->count();
    
            // Mês atual
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
    
            // Buscar reservas agrupadas por dia no mês atual
            $reservasPorMes = Reserva::selectRaw('DAY(data_checkin) as dia, COUNT(*) as total')
                ->whereMonth('data_checkin', $currentMonth)
                ->whereYear('data_checkin', $currentYear)
                ->groupBy('dia')
                ->orderBy('dia')
                ->get()
                ->keyBy('dia'); // Chaveia o array pelo dia do mês
    
            // Últimas 5 reservas
            $ultimasReservas = Reserva::with('clienteResponsavel', 'quarto', 'operador')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();



            //Quartos / Reservas do DIA 
            $statusQuartoNoDia = $this->statusQuartoNoDia();
            $totalOcupados = collect($statusQuartoNoDia)->where('status', 'Ocupado')->count();
            $totalDisponiveis = collect($statusQuartoNoDia)->where('status', 'Livre')->count();
            $totalLimpos = collect($statusQuartoNoDia)->where('status', 'Limpo')->count(); // Exemplo para 'Limpo'
        
            // dd($statusQuartoNoDia);

        return view('admin.index', compact(
            'totalUsuarios', 'totalClientes', 'totalQuartos', 'reservasAtivas', 'reservasPorMes', 'ultimasReservas',
            'reservas', 'dataInicial', 'intervaloDias', 'statusQuartoNoDia', 'totalOcupados', 'totalDisponiveis', 'totalLimpos'
        
        ));
    }

    function statusQuartoNoDia()
    {
        $quartos = Quarto::all();
        $hoje = Carbon::now('America/Sao_Paulo')->toDateString();
        $reservas = Reserva::whereDate('data_checkin', '<=', $hoje)
                            ->whereDate('data_checkout', '>=', $hoje)
                            ->get();

        $status = [];

        foreach ($quartos as $quarto) {
            $status[$quarto->id] = [
                'quarto' => $quarto,
                'status' => 'Livre',
                'reserva' => null
            ];
        }

        foreach ($reservas as $reserva) {
            $status[$reserva->quarto->id] = [
                'quarto' => $reserva->quarto,
                'status' => $reserva->situacao_reserva,
                'reserva' => $reserva
            ];
        }

        // Ordenar pelo número do quarto
        usort($status, function ($a, $b) {
            return $a['quarto']->numero <=> $b['quarto']->numero;
        });

        return $status;
    }

}
