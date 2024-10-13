<?php

namespace App\Http\Controllers\Admin;

use App\Models\Produto;
use App\Models\LocalEstoque;
use Illuminate\Http\Request;
use App\Models\MovimentacaoEstoque;
use App\Http\Controllers\Controller;
use App\Services\MovimentacaoEstoqueService;

class MovimentacaoEstoqueController extends Controller
{
    protected $movimentacaoEstoqueService;
    protected $model;

    public function __construct(MovimentacaoEstoqueService $movimentacaoEstoqueService, MovimentacaoEstoque $model)
    {
        $this->model = $model;
        $this->movimentacaoEstoqueService = $movimentacaoEstoqueService;
    }

    public function index()
    {
        // Carregar locais de estoque com movimentações de origem e destino
        $locaisEstoque = LocalEstoque::with(['movimentacoesOrigem.produto', 'movimentacoesOrigem.usuario', 'movimentacoesDestino.produto', 'movimentacoesDestino.usuario'])->get();
        
        return view('admin.movimentacao-estoque.list', compact('locaisEstoque'));
    }
    
    public function edit($id = null)
    {
        $transferencia = false;
;
        if (strpos(url()->current(), 'transf') !== false) {
            $transferencia = true;
        }

        if($id == 'create') {
            $id = null;
        }
        $edit = boolval($id);
        $movimentacao = $edit ? $this->model->findOrFail($id) : new MovimentacaoEstoque();

        $locaisEstoque = LocalEstoque::all();


        $produtos = Produto::all();

        return view('admin.movimentacao-estoque.form', compact('movimentacao', 'edit', 'locaisEstoque', 'produtos', 'transferencia'));
    }

    // Método unificado para movimentações
    public function save(Request $request)
    {    
        // Processar movimentações
        $result = $this->movimentacaoEstoqueService->handleMovimentacoes($request);
    
        // Verifica se há erro na resposta do serviço
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }
    
        return redirect()
            ->route('admin.movimentacoes-estoque.index')
            ->with('notice', config('app.messages.' . ($request->get('id') ? 'update' : 'insert')));
    }



}
