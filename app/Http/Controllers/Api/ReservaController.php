<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\CheckIn;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Models\CheckOut;
use App\Models\Pagamento;
use App\Models\Bar\Pedido;
use Illuminate\Http\Request;
use App\Models\Bar\ItemPedido;
use App\Services\ReservaService;
use App\Services\PagamentoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReservaRequest;

class ReservaController extends Controller
{
    protected $reservaService;
    protected $model;
    protected $pagamentoService;

    public function __construct(ReservaService $reservaService, Reserva $model, PagamentoService $pagamentoService)
    {
        $this->model = $model;
        $this->reservaService = $reservaService;
        $this->pagamentoService = $pagamentoService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['cliente_id'] ??= '';
        $filters['quarto_id'] ??= '';
        $filters['data_checkin'] ??= '';
        $filters['data_checkout'] ??= '';
        $filters['created_at'] ??= '';
        $filters['operador_id'] ??= '';
    
        $query = $this->model->newQuery();
    
        if ($filters['cliente_id']) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
    
        if ($filters['quarto_id']) {
            $query->where('quarto_id', $filters['quarto_id']);
        }

        if ($filters['data_checkin']) {
            $filters['data_checkin'] = Carbon::createFromFormat('d/m/Y', $filters['data_checkin'])->format('Y-m-d');
        }
    
        if ($filters['data_checkout']) {
            $filters['data_checkout'] = Carbon::createFromFormat('d/m/Y', $filters['data_checkout'])->format('Y-m-d');
        }
    
        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('d/m/Y', $filters['created_at'])->format('Y-m-d');
        }
    
        if ($filters['data_checkin'] && $filters['data_checkout']) {
            $query->where(function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->whereDate('data_checkin', '>=', $filters['data_checkin'])
                          ->whereDate('data_checkin', '<=', $filters['data_checkout']);
                })->orWhere(function ($query) use ($filters) {
                    $query->whereDate('data_checkout', '>=', $filters['data_checkin'])
                          ->whereDate('data_checkout', '<=', $filters['data_checkout']);
                });
            });
        }
        
        if ($filters['created_at']) {
            $query->whereDate('created_at', $filters['created_at']);
        }
        
        if ($filters['operador_id']) {
            $query->where('usuario_operador_id', $filters['operador_id']);
        }
    
        $reservas = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return response()->json($reservas);
    }

    public function show($id)
    {
        $reserva = $this->model->with(['pagamentos', 'acompanhantes'])->findOrFail($id);
        return response()->json($reserva);
    }

    public function store(Request $request)
    {
        // dd($request);
        $data = $request->all();
        $reservas = $this->reservaService->criarOuAtualizarReserva($data);

        return response()->json($reservas, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $data['id'] = $id;
        $reservas = $this->reservaService->criarOuAtualizarReserva($data);

        return response()->json($reservas);
    }

    public function destroy($id)
    {
        $reserva = $this->model->findOrFail($id);
        $reserva->delete();

        return response()->json(null, 204);
    }

    public function updateSituacaoReserva($id, $situacao_reserva)
    {
        try {
            $reserva = $this->model->findOrFail($id);
            $reserva->situacao_reserva = $situacao_reserva;
            $reserva->save();
    
            if ($situacao_reserva && $situacao_reserva != 'FINALIZADO') {
                CheckIn::updateOrCreate(
                    ['reserva_id' => $reserva->id],
                    ['checkin_at' => Carbon::now('America/Sao_Paulo')]
                );
            }
            else if ($situacao_reserva == 'FINALIZADO') {
                CheckOut::updateOrCreate(
                    ['reserva_id' => $reserva->id],
                    ['checkout_at' => Carbon::now('America/Sao_Paulo')]
                );
            }
    
            return response()->json(['message' => 'Situação da reserva atualizada com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar a situação da reserva: ' . $e->getMessage()], 500);
        }
    }
}