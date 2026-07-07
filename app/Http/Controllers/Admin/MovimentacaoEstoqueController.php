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

        $request->validate([
            'movimentacoes' => ['required', 'array', 'min:1'],
            'movimentacoes.*.produto_id' => ['required', 'exists:produtos,id'],
            'movimentacoes.*.tipo_movimento' => ['required', 'in:entrada,saida,perda,transferencia'],
            'movimentacoes.*.quantidade' => ['required', 'numeric', 'gt:0'],
            'movimentacoes.*.local_estoque_id' => ['required_unless:movimentacoes.*.tipo_movimento,transferencia', 'nullable', 'exists:locais_estoque,id'],
            'movimentacoes.*.estoque_origem_id' => ['required_if:movimentacoes.*.tipo_movimento,transferencia', 'nullable', 'exists:locais_estoque,id'],
            'movimentacoes.*.estoque_destino_id' => ['required_if:movimentacoes.*.tipo_movimento,transferencia', 'nullable', 'exists:locais_estoque,id', 'different:movimentacoes.*.estoque_origem_id'],
            'movimentacoes.*.valor_unitario' => ['nullable', 'regex:/^\d{1,10}([.,]\d{1,2})?$/'],
            'movimentacoes.*.justificativa' => ['nullable', 'string', 'max:255'],
        ], [
            'movimentacoes.required' => 'Adicione ao menos um produto clicando em "+ Adicionar" antes de salvar.',
            'movimentacoes.*.produto_id.required' => 'Selecione o produto na lista de sugestões.',
            'movimentacoes.*.produto_id.exists' => 'Produto inválido — selecione na lista de sugestões.',
            'movimentacoes.*.quantidade.gt' => 'A quantidade deve ser maior que zero.',
            'movimentacoes.*.local_estoque_id.required_unless' => 'Selecione o local de estoque.',
            'movimentacoes.*.estoque_origem_id.required_if' => 'Selecione o estoque de origem.',
            'movimentacoes.*.estoque_destino_id.required_if' => 'Selecione o estoque de destino.',
            'movimentacoes.*.estoque_destino_id.different' => 'Origem e destino da transferência devem ser diferentes.',
            'movimentacoes.*.valor_unitario.regex' => 'Valor unitário inválido — use números com até 2 casas decimais (ex.: 12,50).',
        ]);

        try {
            $this->movimentacaoEstoqueService->handleMovimentacoes($request);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao registrar movimentação: '.$e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.movimentacoes-estoque.index')
            ->with('notice', config('app.messages.'.($request->get('id') ? 'update' : 'insert')));
    }

    public function estornar($id, Request $request)
    {
        $this->authorize('gerenciar_estoque');

        $request->validate([
            'justificativa' => ['nullable', 'string', 'max:255'],
        ]);

        $movimentacao = $this->model->findOrFail($id);

        try {
            $avisos = $this->movimentacaoEstoqueService->estornar($movimentacao, $request->get('justificativa'));
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        $mensagem = 'Movimentação #'.$movimentacao->id.' estornada com sucesso.';
        if ($avisos) {
            $mensagem .= ' '.implode(' ', $avisos);
        }

        return redirect()->back()->with('notice', $mensagem);
    }
}
