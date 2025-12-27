<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoriaDespesaRequest;
use App\Models\CategoriaDespesa;
use Illuminate\Http\Request;

class CategoriaDespesaController extends Controller
{
    private CategoriaDespesa $model;

    public function __construct(CategoriaDespesa $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['fl_ativo'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        if ($filters['fl_ativo'] !== '') {
            $query->where('fl_ativo', $filters['fl_ativo']);
        }

        $categorias = $query
            ->orderBy('nome', 'asc')
            ->paginate(config('app.rows_per_page', 15));

        return view('admin.categorias-despesas.index', compact('categorias', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $categoria = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.categorias-despesas.form', compact('categoria', 'edit'));
    }

    public function save(CategoriaDespesaRequest $request)
    {
        $data = $request->validated();
        $data['fl_ativo'] = $request->has('fl_ativo') ? true : false;

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.categorias-despesas.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $categoria = $this->model->findOrFail($id);
        
        // Verificar se a categoria está em uso
        if ($categoria->despesaCategorias()->count() > 0) {
            return redirect()
                ->route('admin.categorias-despesas.index')
                ->with('error', 'Não é possível excluir esta categoria pois ela está sendo utilizada em despesas.');
        }

        $categoria->delete();

        return redirect()
            ->route('admin.categorias-despesas.index')
            ->with('notice', config('app.messages.delete'));
    }
}

