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
}
