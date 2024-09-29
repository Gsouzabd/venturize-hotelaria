<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use Termwind\Components\Dd;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservaRequest;

class ReservaController extends Controller
{
    protected $reservaService;
    protected $model;

    public function __construct(ReservaService $reservaService, Reserva $model)
    {
        $this->reservaService = $reservaService;
        $this->model = $model;
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

        // Deixando as datas no formato dd/mm/YYYY
        
        if ($filters['data_checkin']) {
            $filters['data_checkin'] = Carbon::createFromFormat('Y-m-d', $filters['data_checkin'])->format('d-m-Y');
        }

        if ($filters['data_checkout']) {
            $filters['data_checkout'] = Carbon::createFromFormat('Y-m-d', $filters['data_checkout'])->format('d-m-Y');
        }

        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('Y-m-d', $filters['created_at'])->format('d-m-Y');
        }
        
        // Pegando os quartos, clientes e operadores para os filtros
        $quartos = Quarto::pluck('numero', 'id')->toArray();
        $clientes = Cliente::pluck('nome', 'id')->toArray();
        $operadores = Usuario::pluck('nome', 'id')->toArray(); // Supondo que os operadores são usuários
        
        // Passando os dados para a view
        return view('admin.reservas.list', compact('reservas', 'filters', 'quartos', 'clientes', 'operadores'));
    }

    public function mapa(Request $request)
    {
        // Validação e filtragem dos parâmetros
        $dataInicial = $request->input('data_inicial') 
        ? Carbon::parse($request->input('data_inicial'))->startOfWeek() 
        : Carbon::today()->startOfWeek();
        $intervaloDias = $request->input('intervalo', 30); // Intervalo padrão de 30 dias
        $intervaloDias = in_array($intervaloDias, [7, 15, 30, 60]) ? $intervaloDias : 30; // Verificação de intervalo
    
        // Data final com base no intervalo fornecido
        $dataFinal = $dataInicial->copy()->addDays($intervaloDias - 1); // -1 para garantir que o último dia seja incluído
    

        // Buscar reservas dentro do intervalo
        $reservas = Reserva::with(['clienteSolicitante', 'quarto'])
            ->where(function ($query) use ($dataInicial, $dataFinal) {
                $query->whereBetween('data_checkin', [$dataInicial, $dataFinal]) // Chegada dentro do intervalo
                      ->orWhereBetween('data_checkout', [$dataInicial, $dataFinal])  // Saída dentro do intervalo
                      ->orWhere(function ($q) use ($dataInicial, $dataFinal) {
                          $q->where('data_checkin', '<=', $dataInicial)           // Chegada antes ou no início do intervalo
                            ->where('data_checkout', '>=', $dataFinal);             // Saída depois ou no final do intervalo
                      });
            })
            ->get();


            // dd($reservas);
    
        // Buscar todos os quartos
        $quartos = Quarto::all();

        // dd($reservas, $quartos, $dataInicial, $intervaloDias, $dataFinal);
    
        // Retornar a view com as variáveis necessárias
        return view('admin.reservas.mapa', compact('reservas', 'quartos', 'dataInicial', 'intervaloDias', 'dataFinal'));
    }
    public function edit($id = null)
    {
        $edit = boolval($id);
        $reserva = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        $clientes = Cliente::pluck('nome', 'id')->toArray();
        $quartos = Quarto::pluck('numero', 'id')->toArray();
        $operadores = Usuario::pluck('nome', 'id')->toArray();

        return view('admin.reservas.form', compact('reserva', 'edit', 'clientes', 'quartos', 'operadores'));
    }

    public function save(ReservaRequest $request)
    {   
        $data = $request->all();
        $this->reservaService->criarOuAtualizarReserva($data);

        return redirect()
            ->route('admin.reservas.index')
            ->with('notice', config('app.messages.' . ($request->get('id') ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $reserva = $this->model->findOrFail($id);
        $reserva->delete();

        return redirect()
            ->route('admin.reservas.index')
            ->with('notice', config('app.messages.delete'));
    }
    
}
