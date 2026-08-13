<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalEstoque;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Services\MovimentacaoEstoqueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MovimentacaoEstoqueController extends Controller
{
    protected $movimentacaoEstoqueService;

    protected $model;

    public function __construct(MovimentacaoEstoqueService $movimentacaoEstoqueService, MovimentacaoEstoque $model)
    {
        $this->model = $model;
        $this->movimentacaoEstoqueService = $movimentacaoEstoqueService;
    }

    public function index(Request $request)
    {
        $this->authorize('visualizar_estoque');

        $filters = $request->all();
        $filters['produto_id'] ??= '';
        $filters['data_inicial'] ??= '';
        $filters['data_final'] ??= '';

        $produtoId = $filters['produto_id'] !== '' ? $filters['produto_id'] : null;
        $dataInicial = $filters['data_inicial'] !== '' ? Carbon::createFromFormat('d/m/Y', $filters['data_inicial'])->startOfDay() : null;
        $dataFinal = $filters['data_final'] !== '' ? Carbon::createFromFormat('d/m/Y', $filters['data_final'])->endOfDay() : null;

        $filtroMovimentacoes = function ($query) use ($produtoId, $dataInicial, $dataFinal) {
            $query->when($produtoId, fn ($q) => $q->where('produto_id', $produtoId))
                ->when($dataInicial && $dataFinal, fn ($q) => $q->whereBetween('created_at', [$dataInicial, $dataFinal]))
                ->when($dataInicial && ! $dataFinal, fn ($q) => $q->where('created_at', '>=', $dataInicial))
                ->when($dataFinal && ! $dataInicial, fn ($q) => $q->where('created_at', '<=', $dataFinal));
        };

        // Locais pais com sub-locais e movimentações (origem e destino) de todos
        $relacoes = [
            'produto', 'usuario', 'localOrigem', 'localDestino',
        ];

        $comFiltro = function ($base) use ($filtroMovimentacoes, $relacoes) {
            $with = [];
            foreach (['movimentacoesOrigem', 'movimentacoesDestino'] as $rel) {
                $with[$base.$rel] = $filtroMovimentacoes;
                foreach ($relacoes as $sub) {
                    $with[] = $base.$rel.'.'.$sub;
                }
            }

            return $with;
        };

        $locaisEstoque = LocalEstoque::with(array_merge(
            $comFiltro(''),
            $comFiltro('children.')
        ))->whereNull('parent_id')->orderBy('nome')->get();

        $produtos = Produto::orderBy('descricao')->get();

        return view('admin.movimentacao-estoque.list', compact('locaisEstoque', 'filters', 'produtos'));
    }

    public function edit($id = null)
    {
        $usuario = auth('admin')->user();

        if (! $usuario || (! $usuario->temPermissao('estoque_entrada') && ! $usuario->temPermissao('estoque_saida') && ! $usuario->temPermissao('gerenciar_estoque'))) {
            abort(403);
        }

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

        return view('admin.movimentacao-estoque.form', compact('movimentacao', 'edit', 'locaisEstoque', 'transferencia'));
    }

    // Método unificado para movimentações
    public function save(Request $request)
    {
        $usuario = auth('admin')->user();

        foreach ($request->get('movimentacoes', []) as $movimentacao) {
            $tipo = $movimentacao['tipo_movimento'] ?? null;

            $autorizado = match ($tipo) {
                'entrada' => $usuario && ($usuario->temPermissao('estoque_entrada') || $usuario->temPermissao('gerenciar_estoque')),
                'saida', 'perda' => $usuario && ($usuario->temPermissao('estoque_saida') || $usuario->temPermissao('gerenciar_estoque')),
                'transferencia' => $usuario && $usuario->temPermissao('gerenciar_estoque'),
                default => false,
            };

            if (! $autorizado) {
                abort(403);
            }
        }

        // Defesa em profundidade: valor_unitario nunca deve ser levado em conta em saída/perda,
        // o service sempre usa o preço cadastrado do produto.
        $movimentacoesInput = $request->get('movimentacoes', []);
        foreach ($movimentacoesInput as $index => $movimentacao) {
            if (in_array($movimentacao['tipo_movimento'] ?? null, ['saida', 'perda'], true)) {
                $movimentacoesInput[$index]['valor_unitario'] = null;
            }
        }
        $request->merge(['movimentacoes' => $movimentacoesInput]);

        $validator = Validator::make($request->all(), [
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

        // Quantidade inteira obrigatória, exceto para unidades fracionárias (KG, LT)
        $validator->after(function ($validator) use ($request) {
            $movimentacoes = $request->get('movimentacoes', []);
            $produtos = Produto::whereIn('id', collect($movimentacoes)->pluck('produto_id'))->get()->keyBy('id');

            foreach ($movimentacoes as $index => $movimentacao) {
                $produto = $produtos->get($movimentacao['produto_id'] ?? null);
                if (! $produto || $produto->permiteFracionado()) {
                    continue;
                }

                $quantidade = trim((string) ($movimentacao['quantidade'] ?? ''));
                if ($quantidade !== '' && ! preg_match('/^\d+$/', $quantidade)) {
                    $validator->errors()->add(
                        "movimentacoes.$index.quantidade",
                        "Quantidade de \"{$produto->descricao}\" deve ser um número inteiro (unidade {$produto->unidade})."
                    );
                }
            }
        });

        $validator->validate();

        try {
            $this->movimentacaoEstoqueService->handleMovimentacoes($request);
        } catch (\Throwable $e) {
            \Log::error('Erro ao registrar movimentação de estoque: '.$e->getMessage(), ['exception' => $e]);

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
