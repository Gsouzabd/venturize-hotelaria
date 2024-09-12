<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservaController extends Controller
{
    private Reserva $model;

    public function __construct(Reserva $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['cliente_id'] ??= '';
        $filters['quarto_id'] ??= '';
    
        $query = $this->model->newQuery();
    
        if ($filters['cliente_id']) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
    
        if ($filters['quarto_id']) {
            $query->where('quarto_id', $filters['quarto_id']);
        }
    
        $reservas = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));
    
        // Pegando os quartos e clientes para os filtros
        $quartos = Quarto::pluck('numero', 'id')->toArray();
        $clientes = Cliente::pluck('nome', 'id')->toArray();
    
        // Passando os dados para a view
        return view('admin.reservas.list', compact('reservas', 'filters', 'quartos', 'clientes'));
    }

    public function mapa(Request $request)
    {
        // Validação e filtragem dos parâmetros
        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial')) : Carbon::today();
        $intervaloDias = $request->input('intervalo', 30); // Intervalo padrão de 30 dias
        $intervaloDias = in_array($intervaloDias, [7, 15, 30, 60]) ? $intervaloDias : 30; // Verificação de intervalo
    
        // Data final com base no intervalo fornecido
        $dataFinal = $dataInicial->copy()->addDays($intervaloDias - 1); // -1 para garantir que o último dia seja incluído
    
        // Buscar reservas dentro do intervalo
        $reservas = Reserva::with(['cliente', 'quarto'])
            ->where(function ($query) use ($dataInicial, $dataFinal) {
                $query->whereBetween('data_checkin', [$dataInicial, $dataFinal]) // Chegada dentro do intervalo
                      ->orWhereBetween('data_checkout', [$dataInicial, $dataFinal])  // Saída dentro do intervalo
                      ->orWhere(function ($q) use ($dataInicial, $dataFinal) {
                          $q->where('data_checkin', '<=', $dataInicial)           // Chegada antes ou no início do intervalo
                            ->where('data_checkout', '>=', $dataFinal);             // Saída depois ou no final do intervalo
                      });
            })
            ->get();
    
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

    public function save(Request $request)
    {
        $data = $request->all();

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.reservas.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
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
