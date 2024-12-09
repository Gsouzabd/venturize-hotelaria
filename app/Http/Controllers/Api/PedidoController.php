<?php

namespace App\Http\Controllers\Api;

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
        // $this->middleware('auth:sanctum');
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

        $pedidos = $query->orderBy('id', 'desc')->paginate(config('app.rows_per_page'));

        return response()->json($pedidos);
    }

    public function show($id)
    {
        $pedido = $this->model->findOrFail($id);
        return response()->json($pedido);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $pedido = $this->mesaService->abrirMesa($data);

        if ($pedido instanceof Pedido) {
            return response()->json($pedido, 201);
        }

        return response()->json(['error' => 'Erro ao abrir a mesa.'], 400);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if (isset($data['pedido_id'])) {
            if (isset($data['action'])) {
                if ($data['action'] == "add-itens") {
                    $itens = $this->mesaService->adicionarItemPedido($data);
                    $pdfContent = $this->mesaService->gerarCupomItemAdicionado($data['pedido_id'], $itens);
                    $pdfPath = storage_path("app/public/cupom_pedido_{$data['pedido_id']}.pdf");
                    file_put_contents($pdfPath, $pdfContent);

                    return response()->json([
                        'success' => 'Itens adicionados ao pedido com sucesso.',
                        'pdf_url' => asset("storage/cupom_pedido_{$data['pedido_id']}.pdf")
                    ]);
                } elseif ($data['action'] == "remove-item") {
                    $itensCancelados = $this->mesaService->cancelarItemPedido($data);
                    $justificativa = $data['justificativa'];
                    $itensCancelados[0]['justificativa'] = $justificativa;
                    $pdfContent = $this->mesaService->gerarCupomCancelamento($data['pedido_id'], $itensCancelados);
                    $pdfPath = storage_path("app/public/cupom_cancelamento_pedido_{$data['pedido_id']}.pdf");
                    file_put_contents($pdfPath, $pdfContent);

                    return response()->json([
                        'success' => 'Item removido com sucesso.',
                        'pdf_url' => asset("storage/cupom_cancelamento_pedido_{$data['pedido_id']}.pdf")
                    ]);
                } elseif ($data['action'] == "fechar-pedido") {
                    $removerTaxaServico = $data['removeServiceFee'];
                    $pedidoId = $this->mesaService->fecharConta($data['pedido_id'], $removerTaxaServico);

                    if ($pedidoId) {
                        $pdfContent = $this->mesaService->gerarCupomFechamento($pedidoId);
                        $pdfPath = storage_path("app/public/cupom_fechamento_pedido_{$pedidoId}.pdf");
                        file_put_contents($pdfPath, $pdfContent);

                        return response()->json([
                            'success' => 'Pedido fechado com sucesso.',
                            'pdf_url' => asset("storage/cupom_fechamento_pedido_{$pedidoId}.pdf")
                        ]);
                    } else {
                        return response()->json(['error' => 'Erro ao fechar a mesa.'], 400);
                    }
                }
            }
        }

        $pedido = $this->model->findOrFail($id);
        $pedido->update($data);

        return response()->json($pedido);
    }

    public function destroy($id)
    {
        $pedido = $this->model->findOrFail($id);
        $pedido->delete();

        return response()->json(null, 204);
    }

    public function showCupomParcial($idPedido)
    {
        $pdfOutput = $this->mesaService->gerarCupomParcial($idPedido);
        return response($pdfOutput, 200)->header('Content-Type', 'application/pdf');
    }
}