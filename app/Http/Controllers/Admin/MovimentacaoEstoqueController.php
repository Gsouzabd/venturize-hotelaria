<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalEstoque;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Services\MovimentacaoEstoqueService;
use Illuminate\Http\Request;

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
        $this->authorize('visualizar_estoque');

        // Locais pais com sub-locais e movimentações (origem e destino) de todos
        $relacoes = [
            'movimentacoesOrigem.produto', 'movimentacoesOrigem.usuario',
            'movimentacoesOrigem.localOrigem', 'movimentacoesOrigem.localDestino',
            'movimentacoesDestino.produto', 'movimentacoesDestino.usuario',
            'movimentacoesDestino.localOrigem', 'movimentacoesDestino.localDestino',
        ];

        $locaisEstoque = LocalEstoque::with(array_merge(
            $relacoes,
            array_map(fn ($r) => 'children.'.$r, $relacoes)
        ))->whereNull('parent_id')->orderBy('nome')->get();

        return view('admin.movimentacao-estoque.list', compact('locaisEstoque'));
    }

    public function edit($id = null)
    {
        $this->authorize('gerenciar_estoque');

        $transferencia = false;

        if (strpos(url()->current(), 'transf') !== false) {
            $transferencia = true;
        }

        if ($id == 'create') {
            $id = null;
        }
        $edit = boolval($id);
        $movimentacao = $edit ? $this->model->findOrFail($id) : new MovimentacaoEstoque;

        // Carrega hierarquia para optgroups nas dropdowns
        $locaisEstoque = LocalEstoque::with('children')->whereNull('parent_id')->orderBy('nome')->get();

        $produtos = Produto::all();

        return view('admin.movimentacao-estoque.form', compact('movimentacao', 'edit', 'locaisEstoque', 'produtos', 'transferencia'));
    }

    // Método unificado para movimentações
    public function save(Request $request)
    {
        $this->authorize('gerenciar_estoque');

        // Processar movimentações
        $result = $this->movimentacaoEstoqueService->handleMovimentacoes($request);

        // Verifica se há erro na resposta do serviço
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()
            ->route('admin.movimentacoes-estoque.index')
            ->with('notice', config('app.messages.'.($request->get('id') ? 'update' : 'insert')));
    }
}
