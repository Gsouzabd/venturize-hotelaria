<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    private Cliente $model;

    public function __construct(Cliente $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['email'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        if ($filters['email']) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        $clientes = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.clientes.list', compact('clientes', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $cliente = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.clientes.form', compact('cliente', 'edit'));
    }

    public function save(Request $request)
    {
        $data = $request->all();

        $data['data_nascimento']  = parseDateVenturize($data['data_nascimento']);
        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.clientes.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $cliente = $this->model->findOrFail($id);
        $cliente->delete();

        return redirect()
            ->route('admin.clientes.index')
            ->with('notice', config('app.messages.delete'));
    }


    public function findById($id)
    {
        $cliente = $this->model->findOrFail($id);
        return response()->json($cliente);
    } 

    public function findByCpf($cpf)
    {
        $cliente = $this->model->where('cpf', $cpf)->firstOrFail();
        if(!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }
       
        return response()->json($cliente);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', $request->get('query', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $numericQuery = preg_replace('/[^0-9]/', '', $query);

        $clientes = $this->model->where(function ($q) use ($query, $numericQuery) {
                $q->where('nome', 'like', '%' . $query . '%');
                if (!empty($numericQuery)) {
                    $q->orWhereRaw("REPLACE(REPLACE(cpf, '.', ''), '-', '') LIKE ?", ['%' . $numericQuery . '%']);
                } else {
                    $q->orWhere('cpf', 'like', '%' . $query . '%');
                }
            })
                                ->limit(20)
                                ->get();

        $results = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'text' => $cliente->nome . ($cliente->cpf ? ' - CPF: ' . $cliente->cpf : ''),
                'nome' => $cliente->nome,
                'cpf' => $cliente->cpf,
                'data_nascimento' => $cliente->data_nascimento,
                'rg' => $cliente->rg,
                'cep' => $cliente->cep,
                'cidade' => $cliente->cidade,
                'endereco' => $cliente->endereco,
                'numero' => $cliente->numero,
                'bairro' => $cliente->bairro,
                'estado' => $cliente->estado,
                'pais' => $cliente->pais,
                'email' => $cliente->email,
                'telefone' => $cliente->telefone,
                'celular' => $cliente->celular,
            ];
        });

        return response()->json(['results' => $results]);
    }
    
}
