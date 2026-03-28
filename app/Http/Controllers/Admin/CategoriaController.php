<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    private Categoria $model;

    public function __construct(Categoria $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $this->authorize('visualizar_produtos');

        $filters = $request->all();
        $filters['nome'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        $categorias = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.categorias.list', compact('categorias', 'filters'));
    }

    public function edit($id = null)
    {
        $this->authorize('gerenciar_produtos');

        $edit = boolval($id);
        $categoria = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.categorias.form', compact('categoria', 'edit'));
    }

    public function save(Request $request)
    {
        $this->authorize('gerenciar_produtos');

        $data = $request->all();

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.categorias.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $this->authorize('gerenciar_produtos');

        $categoria = $this->model->findOrFail($id);
        $categoria->delete();

        return redirect()
            ->route('admin.categorias.index')
            ->with('notice', config('app.messages.delete'));
    }
}