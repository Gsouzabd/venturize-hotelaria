<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProdutoController extends Controller
{
    private Produto $model;

    public function __construct(Produto $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['categoria_id'] ??= '';
        $filters['created_at'] ??= '';
        $filters['id'] ??= '';
        $filters['codigo_interno'] ??= '';
    
        $query = $this->model->newQuery();
    
        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }
    
        if ($filters['categoria_id']) {
            $query->where('categoria_id', $filters['categoria_id']);
        }

        if ($filters['id']) {
            $query->where('id', $filters['id']);
        }
        
        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('d/m/Y', $filters['created_at'])->format('Y-m-d');
        }

        if ($filters['codigo_interno']) {
            $query->where('codigo_interno', 'like', '%' . $filters['codigo_interno'] . '%');
        }

        $categorias = Categoria::all();

        $produtos = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.produtos.list', compact('produtos', 'filters', 'categorias'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $produto = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();
        $categorias = Categoria::all();

        return view('admin.produtos.form', compact('produto', 'edit', 'categorias'));
    }

    public function save(Request $request)
    {
        $data = $request->all();

        // dd($data);
        

        if ($id = $request->get('id')) {
            $produto = $this->model->findOrFail($id);
            $data['criado_por'] = $produto->criado_por; // Pega o valor de criado_por do registro existente
            $produto->update($data);
        } else {
            $data['criado_por'] = auth()->user()->id;
            $this->model->fill($data)->save();
        }


        return redirect()
            ->route('admin.produtos.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $produto = $this->model->findOrFail($id);
        $produto->delete();

        return redirect()
            ->route('admin.produtos.index')
            ->with('notice', config('app.messages.delete'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $produtos = Produto::where('descricao', 'like', "%{$query}%")->get(['id', 'descricao']);
    
        return response()->json($produtos);
    }



}