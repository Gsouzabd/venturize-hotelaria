<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $totalUsuarios = Usuario::count();
        $totalClientes = Cliente::count();
        $totalQuartos = Quarto::count();
        $quartosDisponiveis = Quarto::whereDoesntHave('reservas')->count();
        $reservasAtivas = Reserva::where('situacao_reserva', 'CONFIRMADA')->count();
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
            $reservasAtivas = Reserva::where('situacao_reserva', 'CONFIRMADA')->count();
    
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

            

        return view('admin.index', compact(
            'totalUsuarios', 'totalClientes', 'totalQuartos', 'reservasAtivas', 'reservasPorMes', 'ultimasReservas',
            'reservas', 'dataInicial', 'intervaloDias'
        
        ));
    }
}
