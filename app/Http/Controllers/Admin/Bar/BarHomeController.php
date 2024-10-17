<?php

namespace App\Http\Controllers\Admin\Bar;

use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Services\Bar\MesaService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Quarto;
use App\Models\Reserva;

class BarHomeController extends Controller
{
    private $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    public function index(Request $request)
    {
        $totalUsuarios = Usuario::count();
        $totalClientes = Cliente::count();

        // Mesas / Reservas do DIA 
        $statusMesaNoDia = $this->mesaService->statusMesaNoDia();
        $totalMesasOcupadas = collect($statusMesaNoDia)->where('status', 'Ocupada')->count();
        $totalMesasLivres = collect($statusMesaNoDia)->where('status', 'Livre')->count();

        $quartos = Quarto::all();

        $reservas = Reserva::where('situacao_reserva' , 'HOSPEDADO')->get();

        // dd($statusMesaNoDia);

        return view('admin.bar.index', compact(
            'totalUsuarios', 'totalClientes', 'statusMesaNoDia', 'totalMesasOcupadas', 'totalMesasLivres', 'quartos', 'reservas'
        ));
    }
}