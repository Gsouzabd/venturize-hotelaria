<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Quarto;
use App\Models\CheckIn;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Models\Pagamento;
use Termwind\Components\Dd;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\PagamentoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservaRequest;

class ReservaController extends Controller
{
    protected $reservaService;
    protected $model;
    protected $pagamentoService;

    public function __construct(ReservaService $reservaService, Reserva $model, PagamentoService $pagamentoService)
    {
        $this->model = $model;
        $this->reservaService = $reservaService;
        $this->pagamentoService = $pagamentoService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['cliente_id'] ??= '';
        $filters['quarto_id'] ??= '';
        $filters['data_checkin'] ??= '';
        $filters['data_checkout'] ??= '';
        $filters['created_at'] ??= '';
        $filters['operador_id'] ??= '';
    
        $query = $this->model->newQuery();
    
        if ($filters['cliente_id']) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
    
        if ($filters['quarto_id']) {
            $query->where('quarto_id', $filters['quarto_id']);
        }

        if ($filters['data_checkin']) {
            $filters['data_checkin'] = Carbon::createFromFormat('d/m/Y', $filters['data_checkin'])->format('Y-m-d');
        }
    
        if ($filters['data_checkout']) {
            $filters['data_checkout'] = Carbon::createFromFormat('d/m/Y', $filters['data_checkout'])->format('Y-m-d');
        }
    
        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('d/m/Y', $filters['created_at'])->format('Y-m-d');
        }
    
        if ($filters['data_checkin'] && $filters['data_checkout']) {
            $query->where(function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->whereDate('data_checkin', '>=', $filters['data_checkin'])
                          ->whereDate('data_checkin', '<=', $filters['data_checkout']);
                })->orWhere(function ($query) use ($filters) {
                    $query->whereDate('data_checkout', '>=', $filters['data_checkin'])
                          ->whereDate('data_checkout', '<=', $filters['data_checkout']);
                });
            });
        }
        
        if ($filters['created_at']) {
            $query->whereDate('created_at', $filters['created_at']);
        }
        
        if ($filters['operador_id']) {
            $query->where('usuario_operador_id', $filters['operador_id']);
        }
    
            
        
        $reservas = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        // Deixando as datas no formato dd/mm/YYYY
        
        if ($filters['data_checkin']) {
            $filters['data_checkin'] = Carbon::createFromFormat('Y-m-d', $filters['data_checkin'])->format('d-m-Y');
        }

        if ($filters['data_checkout']) {
            $filters['data_checkout'] = Carbon::createFromFormat('Y-m-d', $filters['data_checkout'])->format('d-m-Y');
        }

        if ($filters['created_at']) {
            $filters['created_at'] = Carbon::createFromFormat('Y-m-d', $filters['created_at'])->format('d-m-Y');
        }
        
        // Pegando os quartos, clientes e operadores para os filtros
        $quartos = Quarto::pluck('numero', 'id')->toArray();
        $clientes = Cliente::pluck('nome', 'id')->toArray();
        $operadores = Usuario::pluck('nome', 'id')->toArray(); // Supondo que os operadores são usuários
        
        // Passando os dados para a view
        return view('admin.reservas.list', compact('reservas', 'filters', 'quartos', 'clientes', 'operadores'));
    }

    public function mapa(Request $request)
    {
        // Validação e filtragem dos parâmetros
        $dataInicial = $request->input('data_inicial')
        ? Carbon::parse($request->input('data_inicial'))->startOfWeek() 
        : Carbon::today();
        $intervaloDias = $request->input('intervalo', 30); // Intervalo padrão de 30 dias
        $intervaloDias = in_array($intervaloDias, [7, 15, 30, 60]) ? $intervaloDias : 30; // Verificação de intervalo
    
        // Data final com base no intervalo fornecido
        $dataFinal = $dataInicial->copy()->addDays($intervaloDias - 1); // -1 para garantir que o último dia seja incluído
    

        // Buscar reservas dentro do intervalo
        $reservas = Reserva::with(['clienteSolicitante', 'quarto'])
            ->where(function ($query) use ($dataInicial, $dataFinal) {
                $query->whereBetween('data_checkin', [$dataInicial, $dataFinal]) // Chegada dentro do intervalo
                      ->orWhereBetween('data_checkout', [$dataInicial, $dataFinal])  // Saída dentro do intervalo
                      ->orWhere(function ($q) use ($dataInicial, $dataFinal) {
                          $q->where('data_checkin', '<=', $dataInicial)           // Chegada antes ou no início do intervalo
                            ->where('data_checkout', '>=', $dataFinal);             // Saída depois ou no final do intervalo
                      });
            })
            ->get();

    
        // Buscar todos os quartos
        $quartos = Quarto::all();

        // dd($reservas, $quartos, $dataInicial, $intervaloDias, $dataFinal);
    
        // Retornar a view com as variáveis necessárias
        return view('admin.reservas.mapa', compact('reservas', 'quartos', 'dataInicial', 'intervaloDias', 'dataFinal'));
    }
    public function edit($id = null)
    {
        $edit = boolval($id);
        $reserva = $edit ? $this->model->with(['pagamentos', 'acompanhantes'])->findOrFail($id) : $this->model->newInstance();
        $clientes = Cliente::pluck('nome', 'id')->toArray();
        $quartos = Quarto::pluck('numero', 'id')->toArray();
        $operadores = Usuario::pluck('nome', 'id')->toArray();

        $metodosPagamento = Pagamento::METODOS_PAGAMENTO;


        $totalCheckout = $reserva->total;
        $totalPedido = 0;
        if($reserva->pedidos()->count() > 0) {
            foreach ($reserva->pedidos as $pedido) {
                $totalPedido += floatval($pedido->total_com_taxa ?? $pedido->total);
            }
            $totalCheckout = floatval( $totalCheckout + $totalPedido );
            $totalCheckout = number_format($totalCheckout, 2, ',', '.');
        }

        return view('admin.reservas.form', compact('reserva', 'edit', 'clientes', 'quartos', 'operadores', 'metodosPagamento', 'totalCheckout'));
    }

    public function save(ReservaRequest $request)
    {
        // dd($request->all());
        $data = $request->all();
        
        // Cria ou atualiza a(s) reserva(s)
        $reservas = $this->reservaService->criarOuAtualizarReserva($data);
        
        // Salva os pagamentos de cada reserva
        try {
            foreach ($reservas as $reserva) {
                $quartoId = $reserva->quarto_id;
                if (isset($data['quartos'][$quartoId])) {
                    $quartoData = $data['quartos'][$quartoId];
        
                    $valoresRecebidos = $quartoData['valores_recebidos'] ?? [];
                    $metodosPagamento = $quartoData['metodos_pagamento'] ?? [];
                    $submetodosPagamento = $quartoData['submetodos_pagamento'] ?? [];
        
                    $pagamentos = [];
                    $valorPago = 0;
        
                    foreach ($valoresRecebidos as $index => $valor) {
                        $metodoPagamento = $metodosPagamento[$index] ?? null;
                        $submetodoPagamento = $submetodosPagamento[$index] ?? null;
                        $key = "{$metodoPagamento}-{$submetodoPagamento}";
        
                        if (!isset($pagamentos[$key])) {
                            $pagamentos[$key] = 0;
                        }
        
                        $pagamentos[$key] += $valor;
                        $valorPago += $valor;
                    }
        
                    $pagamentosJson = json_encode($pagamentos);
        
                    $this->pagamentoService->salvarPagamentos($reserva->id, $pagamentosJson, $valorPago, $reserva->total);
                }
            }

            $this->updateSituacaoReserva($reserva->id, $data['situacao_reserva']);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        
        return redirect()
                    ->back()
                    ->with('notice', config('app.messages.' . ($request->get('id') ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $reserva = $this->model->findOrFail($id);
        $reserva->delete();

        return redirect()
            ->route('admin.reservas.index')
            ->with('notice', config('app.messages.delete'));
    }
    
    
    function updateSituacaoReserva($id, $situacao_reserva)
    {
        try {
            $reserva = $this->model->findOrFail($id);
            $reserva->situacao_reserva = $situacao_reserva;
            $reserva->save();
    
            if ($situacao_reserva) {
                CheckIn::updateOrCreate(
                    ['reserva_id' => $reserva->id],
                    ['checkin_at' => Carbon::now('America/Sao_Paulo')]
                );
            }
    
            return redirect()
                ->route('admin.reservas.index')
                ->with('notice', 'Situação da reserva atualizada com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.reservas.index')
                ->with('error', 'Erro ao atualizar a situação da reserva: ' . $e->getMessage());
        }
    }

    public function gerarFichaNacional($id)
    {
        $this->reservaService->gerarFichaNacional($id);
    }

    public function gerarExtrato($id)
    {
        $reserva = Reserva::with(['quarto', 'pedidos'])->findOrFail($id);

        $totalConsumoBar = $reserva->pedidos->sum('total');
        $totalTaxaServicoConsumoBar = $reserva->pedidos->filter(function($pedido) {
            return $pedido->remover_taxa != 0;
        })->sum('taxa_servico');

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($pdfOptions);

        $html = view('pdf.extrato_reserva', compact('reserva', 'totalConsumoBar', 'totalTaxaServicoConsumoBar'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('extrato_reserva.pdf');
    }
}


