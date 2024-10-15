<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalEstoque;
use Illuminate\Http\Request;

class LocalEstoqueController extends Controller
{
    private LocalEstoque $model;

    public function __construct(LocalEstoque $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        $locaisEstoque = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.locais-estoque.list', compact('locaisEstoque', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $localEstoque = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.locais-estoque.form', compact('localEstoque', 'edit'));
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
            ->route('admin.locais-estoque.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $localEstoque = $this->model->findOrFail($id);
        $localEstoque->delete();

        return redirect()
            ->route('admin.locais-estoque.index')
            ->with('notice', config('app.messages.delete'));
    }
}