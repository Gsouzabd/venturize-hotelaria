<?php

namespace App\Http\Controllers\Admin\Bar;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Bar\Mesa;
use App\Models\Categoria;
use App\Models\Bar\Pedido;
use Illuminate\Http\Request;
use App\Services\Bar\MesaService;
use App\Http\Controllers\Controller;

class PedidoController extends Controller
{
    private Pedido $model;
    private MesaService $mesaService;

    public function __construct(Pedido $model, MesaService $mesaService)
    {
        $this->model = $model;
        $this->mesaService = $mesaService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['numero'] ??= '';
        $filters['status'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['numero']) {
            $query->where('numero', 'like', '%' . $filters['numero'] . '%');
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $pedidos = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.bar.pedidos.list', compact('pedidos', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $pedido = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();
        $mesas = Mesa::all();
        $clientes = Cliente::all();
        // Obter os IDs das categorias "Bebidas" e "Alimentos"
        $categoriaIds = Categoria::whereNotIn('nome', ['Materiais de Serviços'])->pluck('id');
        
        // Obter os produtos que pertencem às categorias "Bebidas" e "Alimentos"
        $produtos = Produto::whereIn('categoria_produto', $categoriaIds)->get();

        $produtosAgrupados = $produtos->groupBy('categoria_produto');

        // dd($edit);

        return view('admin.bar.pedidos.form', compact('pedido', 'edit', 'mesas', 'clientes', 'produtosAgrupados'));
    }

    public function save(Request $request)
    {
        $data = $request->all();

        // dd($data);
        if($data['pedido_id']){
            if($data['action']){
                if($data['action'] == "add-itens"){
                    
                    $itens =  $this->mesaService->adicionarItemPedido($data);

                    // Gerar o PDF do cupom
                    $novoItem = end($itens); // Pega o último item adicionado

                    $pdfContent = $this->mesaService->gerarCupom($data['pedido_id'], $novoItem);
            
                    // Salvar o PDF em um arquivo temporário
                    $pdfPath = storage_path("app/public/cupom_pedido_{$data['pedido_id']}.pdf");
                    file_put_contents($pdfPath, $pdfContent);
            
                    // Retornar uma resposta que abre o PDF em uma nova aba
                    return response()->json([
                        'success' => 'Itens adicionados ao pedido com sucesso.',
                        'pdf_url' => asset("storage/cupom_pedido_{$data['pedido_id']}.pdf")
                    ]);
                } elseif($data['action'] == "remove-item"){
                    $this->mesaService->cancelarItemPedido($data);

                    return redirect()
                        ->route('admin.bar.pedidos.edit', ['id' => $data['pedido_id']])
                        ->with('notice', 'Item removido com sucesso');
                }
            }
        }
        // Abrir a mesa e criar um novo pedido
        $pedido = $this->mesaService->abrirMesa($data);

        // dd($pedido);

        // Verificar se o pedido foi criado com sucesso
        if ($pedido instanceof Pedido) {
                return redirect()
                    ->route('admin.bar.pedidos.edit', ['id' => $pedido->id])
                ->with('notice', config('app.messages.insert'));
        }

        return redirect()
            ->route('admin.bar.pedidos.index')
            ->with('error', 'Erro ao abrir a mesa. "' . $pedido . '"');
    }

    public function destroy($id)
    {
        $pedido = $this->model->findOrFail($id);
        $pedido->delete();

        return redirect()
            ->route('admin.bar.pedidos.index')
            ->with('notice', config('app.messages.delete'));
    }
}