<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FornecedorRequest;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    private Fornecedor $model;

    public function __construct(Fornecedor $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['cnpj'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        if ($filters['cnpj']) {
            $query->where('cnpj', 'like', '%' . $filters['cnpj'] . '%');
        }

        $fornecedores = $query
            ->orderBy('nome', 'asc')
            ->paginate(config('app.rows_per_page', 15));

        return view('admin.fornecedores.index', compact('fornecedores', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $fornecedor = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.fornecedores.form', compact('fornecedor', 'edit'));
    }

    public function save(FornecedorRequest $request)
    {
        $data = $request->validated();

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.fornecedores.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $fornecedor = $this->model->findOrFail($id);
        
        // Verificar se o fornecedor está em uso
        if ($fornecedor->despesas()->count() > 0) {
            return redirect()
                ->route('admin.fornecedores.index')
                ->with('error', 'Não é possível excluir este fornecedor pois ele está sendo utilizado em despesas.');
        }

        $fornecedor->delete();

        return redirect()
            ->route('admin.fornecedores.index')
            ->with('notice', config('app.messages.delete'));
    }
}
