<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Estoque;
use App\Models\Produto;
use App\Models\LocalEstoque;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EstoqueController extends Controller
{
    private Estoque $model;

    public function __construct(Estoque $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['produto_id'] ??= '';
        $filters['local_estoque_id'] ??= '';
        $filters['created_at'] ??= '';
        $filters['id'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['produto_id']) {
            $query->where('produto_id', $filters['produto_id']);
        }

        if ($filters['local_estoque_id']) {
            $query->where('local_estoque_id', $filters['local_estoque_id']);
        }

        if ($filters['id']) {
            $query->where('id', $filters['id']);
        }

        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('d/m/Y', $filters['created_at'])->format('Y-m-d');
        }

        $locaisEstoque = LocalEstoque::all();

        $estoques = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.estoque.list', compact('estoques', 'filters', 'locaisEstoque'));
    }

    public function edit($id = null, $local_estoque_id = null)
    {
        $edit = boolval($id);
        $estoque = $edit ? $this->model->findOrFail($id) : new Estoque();
        $locaisEstoque = LocalEstoque::all();

        $produtos = Produto::all();
    
        return view('admin.estoque.form', compact('estoque', 'edit', 'locaisEstoque', 'produtos'));
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
            ->route('admin.estoque.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $estoque = $this->model->findOrFail($id);
        $estoque->delete();

        return redirect()
            ->route('admin.estoque.index')
            ->with('notice', config('app.messages.delete'));
    }
}