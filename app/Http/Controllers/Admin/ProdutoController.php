<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\ProdutoComposicao;
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
        $this->authorize('visualizar_produtos');

        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['categoria_id'] ??= '';
        $filters['created_at'] ??= '';
        $filters['id'] ??= '';
        $filters['codigo_interno'] ??= '';
        $filters['ativo'] ??= '1';

        $query = $this->model->newQuery();

        if ($filters['ativo'] !== '') {
            $query->where('ativo', $filters['ativo']);
        }
    
        if ($filters['nome']) {
            $query->where('descricao', 'like', '%' . $filters['nome'] . '%');
        }
    
        if ($filters['categoria_id']) {
            $query->where('categoria_produto', $filters['categoria_id']);
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
        $this->authorize('gerenciar_produtos');

        $edit = boolval($id);
        $produto = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();
        $categorias = Categoria::all();

        return view('admin.produtos.form', compact('produto', 'edit', 'categorias'));
    }

    public function save(Request $request)
    {
        $this->authorize('gerenciar_produtos');

        $data = $request->all();

        // Save the main product data
        if ($id = $request->get('id')) {
            $produto = $this->model->findOrFail($id);
            $data['criado_por'] = $produto->criado_por; // Pega o valor de criado_por do registro existente
            $produto->update($data);
        } else {
            $data['criado_por'] = auth()->user()->id;
            $produto = $this->model->create($data);
        }
    
        // Save the product composition if it exists
        if ($request->has('possui_composicao') && $request->get('possui_composicao') == 'on') {
            // Delete existing compositions if updating
            if ($id) {
                ProdutoComposicao::where('produto_id', $produto->id)->delete();
            }
    
            foreach ($data['insumo'] as $insumo) {
                ProdutoComposicao::create([
                    'produto_id' => $produto->id,
                    'insumo_id' => $insumo['produto_id'],
                    'quantidade' => $insumo['quantidade'],
                ]);
            }
        }
    
        return redirect()
            ->route('admin.produtos.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $this->authorize('gerenciar_produtos');

        $produto = $this->model->findOrFail($id);

        // Excluir apagaria em cascata histórico de estoque/movimentações/pedidos —
        // produto já referenciado é apenas inativado
        $referenciado = $produto->estoques()->exists()
            || $produto->itens()->exists()
            || \App\Models\MovimentacaoEstoque::where('produto_id', $produto->id)->exists();

        if ($referenciado) {
            $produto->update(['ativo' => 0]);

            return redirect()
                ->route('admin.produtos.index')
                ->with('notice', 'O produto possui histórico (estoque, movimentações ou pedidos) e foi inativado em vez de excluído. Ele deixa de aparecer nas buscas e listagens.');
        }

        $produto->delete();

        return redirect()
            ->route('admin.produtos.index')
            ->with('notice', config('app.messages.delete'));
    }


    public function search(Request $request)
    {
        $this->authorize('visualizar_produtos');

        $query = $request->get('query');
        $produtos = Produto::query()
            ->where('ativo', 1)
            ->where(function ($q) use ($query) {
                $q->where('descricao', 'like', "%{$query}%")
                    ->orWhere('codigo_interno', $query);
            })
            ->when($request->get('local_estoque_id'), function ($q, $localId) {
                // Restringe a produtos com registro de estoque no local (saída/perda/transferência)
                $q->whereHas('estoques', fn ($e) => $e->where('local_estoque_id', $localId));
            })
            ->orderBy('descricao')
            ->limit(20)
            ->get(['id', 'descricao', 'unidade', 'codigo_interno', 'preco_custo', 'preco_venda']);
    
        // Map unit codes to full names
        $unidades = Produto::UNIDADES;
    
        // Add full unit name to each product
        $produtos->transform(function ($produto) use ($unidades) {
            $produto->unidade_nome = $unidades[$produto->unidade] ?? $produto->unidade;
            return $produto;
        });
    
        return response()->json($produtos);
    }


}