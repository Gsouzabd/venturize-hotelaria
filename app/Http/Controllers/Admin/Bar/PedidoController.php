<?php

namespace App\Http\Controllers\Admin\Bar;

use App\Events\MyEvent;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Bar\Mesa;
use App\Models\Categoria;
use App\Models\Bar\Pedido;
use App\Models\Bar\ImpressaoPedido;
use Illuminate\Http\Request;
use App\Events\ItemAdicionado;
use App\Services\Bar\MesaService;
use App\Services\PrinterService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PedidoController extends Controller
{
    private Pedido $model;
    private MesaService $mesaService;
    private PrinterService $printerService;

    public function __construct(Pedido $model, MesaService $mesaService, PrinterService $printerService)
    {
        $this->model = $model;
        $this->mesaService = $mesaService;
        $this->printerService = $printerService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['numero'] ??= '';
        $filters['status'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['numero']) {
            $query->where('id', 'like', '%' . $filters['numero'] . '%');
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
        $categoriaIds = Categoria::whereIn('nome', ['Alimentos', 'Bebidas', 'Drinks', 'Gelo'])->pluck('id');
        
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

        // dd($data);
        if (isset($data['pedido_id'])) {
            if (isset($data['action'])) {
                if ($data['action'] == "add-obs") {
                    $pedido = $this->model->findOrFail($data['pedido_id']);
                    $pedido->observacoes = $data['observacoes'];
                    $pedido->save();
                    return redirect()
                        ->route('admin.bar.pedidos.edit', ['id' => $pedido->id])
                        ->with('notice', 'Observações salvas com sucesso.');
                }
                if ($data['action'] === 'add-itens') {
                    // Adicionar itens ao pedido
                    $itens = $this->mesaService->adicionarItemPedido($data);
                
                    // Gerar conteúdo do PDF para o cupom
                    $pdfContent = $this->mesaService->gerarCupomItemAdicionado($data['pedido_id'], $itens);
                    $pdfPath = storage_path("app/public/cupom_pedido_{$data['pedido_id']}.pdf");
                    file_put_contents($pdfPath, $pdfContent);
                    $pdfUrl = asset("storage/cupom_pedido_{$data['pedido_id']}.pdf");
                
                    // Disparar o evento
                    event(new ItemAdicionado($data, $itens, $pdfUrl));
                
                    $conteudoCupom = "Pedido ID: " . $data['pedido_id'] . "\n";
                    foreach ($itens as $item) {
                        $conteudoCupom .= "{$item['nome']} - R$ {$item['preco']}\n";
                    }
                
                    // Acionar a impressão
                    $this->mesaService->imprimirCupom($conteudoCupom);
                
                    // Retornar resposta com o URL do PDF
                    return response()->json([
                        'success' => 'Itens adicionados ao pedido com sucesso.',
                        'pdf_url' => $pdfUrl,
                    ]);
                } elseif ($data['action'] == "remove-item") {
                    $itensCancelados = $this->mesaService->cancelarItemPedido($data);
                    
                    $justificativa = $data['justificativa'];
                    $itensCancelados[0]['justificativa'] = $justificativa;
        
                    $pdfContent = $this->mesaService->gerarCupomCancelamento($data['pedido_id'], $itensCancelados);
        
                    // Salvar o PDF em um arquivo temporário
                    $pdfPath = storage_path("app/public/cupom_cancelamento_pedido_{$data['pedido_id']}.pdf");
                    file_put_contents($pdfPath, $pdfContent);
        
                    // Retornar uma resposta que abre o PDF em uma nova aba
                    return response()->json([
                        'success' => 'Item removido com sucesso.',
                        'pdf_url' => asset("storage/cupom_cancelamento_pedido_{$data['pedido_id']}.pdf")
                    ]);
                } elseif ($data['action'] == "fechar-pedido") {
                    // dd($data);  
                    // var_dump($data);
                    $removerTaxaServico = $data['removeServiceFee'];
                    // dd($removerTaxaServico);
                    $pedidoId = $this->mesaService->fecharConta($data['pedido_id'], $removerTaxaServico);

                    if ($pedidoId) {
                        $pdfContent = $this->mesaService->gerarCupomFechamento($pedidoId);
        
                        // Salvar o PDF em um arquivo temporário
                        $pdfPath = storage_path("app/public/cupom_fechamento_pedido_{$pedidoId}.pdf");
                        file_put_contents($pdfPath, $pdfContent);
        
                        // Retornar uma resposta que abre o PDF em uma nova aba
                        return response()->json([
                            'success' => 'Pedido fechado com sucesso.',
                            'pdf_url' => asset("storage/cupom_fechamento_pedido_{$pedidoId}.pdf")
                        ]);
                    } else {
                        return response()->json([
                            'error' => 'Erro ao fechar a mesa.'
                        ]);
                    }
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

    public function showCupomParcial($idPedido)
    {
        $pedido = $this->model->findOrFail($idPedido);
        
        $pdfOutput = $this->mesaService->gerarCupomParcial($idPedido);
        
        // Verificar se deve pular o registro de impressão (caso já tenha sido feito pela API ou já exista pendente)
        // Usando helper request() para garantir acesso aos parâmetros query string
        // Adicionada verificação de impressão pendente para evitar duplicidade
        if ((request()->has('no_log') && request()->input('no_log') == '1') || $pedido->temImpressaoPendente()) {
            // Log::info('Pulando registro de impressão (no_log=1 ou já existe pendente)');
            return response($pdfOutput, 200, [
                'Content-Type' => 'application/pdf'
            ]);
        }

        // Log::info('Criando novo registro de impressão no Controller');
        // Criar registro de impressão com status 'pendente'
        $impressao = $pedido->impressoes()->create([
            'agente_impressao' => 'sistema_web',
            'ip_origem' => request()->ip(),
            'status_impressao' => 'pendente',
            'dados_impressao' => [
                'user_agent' => request()->userAgent(),
                'timestamp_criacao' => now()->toISOString(),
                'tipo_cupom' => 'parcial'
            ]
        ]);
        
        // Retornar o PDF
        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'X-Impressao-ID' => $impressao->id,
            'X-Impressao-Status' => 'pendente'
        ]);
    }

    public function showExtratoParcial($idPedido)
    {
        $pdfOutput = $this->mesaService->gerarExtratoParcial($idPedido);
        return response($pdfOutput, 200, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Verifica o status de impressão de um pedido
     */
    public function statusImpressao($idPedido)
    {
        $pedido = $this->model->findOrFail($idPedido);
        
        return response()->json([
            'pedido_id' => $idPedido,
            'foi_impresso' => $pedido->foiImpresso(),
            'tem_impressao_pendente' => $pedido->temImpressaoPendente(),
            'total_impressoes' => $pedido->totalImpressoes(),
            'ultima_impressao' => $pedido->ultimaImpressao ? [
                'id' => $pedido->ultimaImpressao->id,
                'status' => $pedido->ultimaImpressao->status_impressao,
                'agente' => $pedido->ultimaImpressao->agente_impressao,
                'data' => $pedido->ultimaImpressao->created_at->format('d/m/Y H:i:s'),
                'detalhes_erro' => $pedido->ultimaImpressao->detalhes_erro
            ] : null,
            'historico_impressoes' => $pedido->impressoes()->orderBy('created_at', 'desc')->get()->map(function($impressao) {
                return [
                    'id' => $impressao->id,
                    'status' => $impressao->status_impressao,
                    'agente' => $impressao->agente_impressao,
                    'data' => $impressao->created_at->format('d/m/Y H:i:s'),
                    'detalhes_erro' => $impressao->detalhes_erro
                ];
            })
        ]);
    }
}