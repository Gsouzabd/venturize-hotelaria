<?php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Quarto;
use App\Models\CheckIn;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Pagamento;
use App\Models\Bar\Pedido;
use Termwind\Components\Dd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\ReservaService;
use App\Services\PagamentoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReservaRequest;
use App\Models\Bar\ItemPedido;
use App\Models\CheckOut;

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

    /**
     * Calcula o total de uma reserva Day Use (retorno JSON para o front).
     */
    public function calcularDayUse(Request $request)
    {
        // GET envia com_cafe como string "true"/"false"; normalizar para validação boolean
        $request->merge([
            'com_cafe' => filter_var($request->input('com_cafe'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
        ]);

        $request->validate([
            'data_entrada' => 'required|string',
            'adultos' => 'required|integer|min:0',
            'criancas_ate_7' => 'required|integer|min:0',
            'criancas_mais_7' => 'required|integer|min:0',
            'com_cafe' => 'sometimes|boolean',
        ]);
        try {
            $dataUso = $request->input('data_entrada');
            $adultos = (int) $request->input('adultos', 1);
            $criancasAte7 = (int) $request->input('criancas_ate_7', 0);
            $criancasMais7 = (int) $request->input('criancas_mais_7', 0);
            $comCafe = filter_var($request->input('com_cafe'), FILTER_VALIDATE_BOOLEAN);
            $total = $this->reservaService->calcularTotalDayUse($dataUso, $adultos, $criancasAte7, $criancasMais7, $comCafe);
            return response()->json(['total' => round($total, 2)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'total' => 0], 422);
        }
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
        $filters['tipo_reserva'] ??= '';

        // Se a rota for a específica de Day Use, força o filtro
        if (Route::currentRouteName() === 'admin.reservas.day-use') {
            $filters['tipo_reserva'] = 'DAY_USE';
        }
    
        $query = $this->model->newQuery();
    
        if ($filters['cliente_id']) {
            $query->where('cliente_id', $filters['cliente_id']);
        }
    
        if ($filters['quarto_id']) {
            $query->where('quarto_id', $filters['quarto_id']);
        }

        if ($filters['tipo_reserva']) {
            $query->where('tipo_reserva', $filters['tipo_reserva']);
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
        // var_dump($totalCheckout);
        $totalPedido = 0;
        $totalTaxaServicoConsumo = 0;
        if($reserva->pedidos()->count() > 0) {
            foreach ($reserva->pedidos as $pedido) {
                $totalPedido += floatval($pedido->total);
                $totalTaxaServicoConsumo += $pedido->remover_taxa == false ? floatval($pedido->taxa_servico) : 0;
            }
            if($reserva->remover_taxa_servico == 1){
                $totalTaxaServicoConsumo = 0;
            }
            $totalCheckout = floatval( $totalCheckout + $totalPedido + $totalTaxaServicoConsumo );
            $totalCheckout = number_format($totalCheckout, 2, ',', '.');
        }

        $totalConsumo = $totalPedido;
        // dd($totalPedido, $totalTaxaServicoConsumo, $totalCheckout);
        

        $pedido = $reserva->pedidos()->where('pedido_apartamento', 1)->first();
        // dd($pedido);
        if($edit){
            if (!$pedido) {
                $pedido = Pedido::create([
                    'reserva_id' => $reserva->id,
                    'cliente_id' => $reserva->clienteResponsavel->id ?? $reserva->clienteSolicitante->id,
                    'mesa_id' => null, // Assuming no mesa is associated
                    'status' => 'aberto', // Set an appropriate status
                    'total' => 0.00,
                    'pedido_apartamento' => true,
                ]);
            }
        }

 
        return view('admin.reservas.form', compact('reserva', 'edit', 'clientes', 'quartos', 'operadores',
         'metodosPagamento', 'totalCheckout', 'pedido', 'totalConsumo', 'totalTaxaServicoConsumo'));
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
                if ($reserva->tipo_reserva === 'DAY_USE') {
                    // Day Use: pagamento em estrutura plana (valores_recebidos[], metodos_pagamento[], etc.)
                    $valoresRecebidos = $data['valores_recebidos'] ?? [];
                    $metodosPagamento = $data['metodos_pagamento'] ?? [];
                    $submetodosPagamento = $data['submetodos_pagamento'] ?? [];
                    $observacoesPagamento = $data['observacoes_pagamento'] ?? [];

                    $pagamentos = [];
                    $valorPago = 0;
                    foreach ($valoresRecebidos as $index => $valor) {
                        $metodoPagamento = $metodosPagamento[$index] ?? null;
                        $submetodoPagamento = $submetodosPagamento[$index] ?? null;
                        $observacaoPagamento = $observacoesPagamento[$index] ?? null;
                        $key = "{$metodoPagamento}-{$submetodoPagamento}-{$observacaoPagamento}";
                        if (!isset($pagamentos[$key])) {
                            $pagamentos[$key] = 0;
                        }
                        $pagamentos[$key] += $valor;
                        $valorPago += $valor;
                    }
                    $pagamentosJson = json_encode($pagamentos);
                    $this->pagamentoService->salvarPagamentos($reserva->id, $pagamentosJson, $valorPago, $reserva->total);
                } else {
                    $quartoId = $reserva->quarto_id;
                    if (isset($data['quartos'][$quartoId])) {
                        $quartoData = $data['quartos'][$quartoId];

                        $valoresRecebidos = $quartoData['valores_recebidos'] ?? [];
                        $metodosPagamento = $quartoData['metodos_pagamento'] ?? [];
                        $submetodosPagamento = $quartoData['submetodos_pagamento'] ?? [];
                        $observacoesPagamento = $quartoData['observacoes_pagamento'] ?? [];

                        $pagamentos = [];
                        $valorPago = 0;
                        foreach ($valoresRecebidos as $index => $valor) {
                            $metodoPagamento = $metodosPagamento[$index] ?? null;
                            $submetodoPagamento = $submetodosPagamento[$index] ?? null;
                            $observacaoPagamento = $observacoesPagamento[$index] ?? null;
                            $key = "{$metodoPagamento}-{$submetodoPagamento}-{$observacaoPagamento}";
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
            }

            $reserva = $reservas[0] ?? null;
            if ($reserva && $reserva->situacao_reserva != 'RESERVADO' && $reserva->created_at != $reserva->updated_at) {
                $data['confirmCheckout'] = isset($data['confirmCheckout']) ? true : false;

                // dd($data);
                $situacao_reserva = $data['confirmCheckout'] ? 'FINALIZADO' : $data['situacao_reserva'];
    
                $this->updateSituacaoReserva($reserva->id, $situacao_reserva);
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
        
        return redirect()
                    ->route('admin.reservas.mapa')
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
    
            if ($situacao_reserva && $situacao_reserva != 'FINALIZADO') {
                CheckIn::updateOrCreate(
                    ['reserva_id' => $reserva->id],
                    ['checkin_at' => Carbon::now('America/Sao_Paulo')]
                );
            }
            else if ($situacao_reserva == 'FINALIZADO') {
                CheckOut::updateOrCreate(
                    ['reserva_id' => $reserva->id],
                    ['checkout_at' => Carbon::now('America/Sao_Paulo')]
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
        return $this->reservaService->gerarFichaNacional($id);
    }

    public function gerarExtrato($id)
    {
        $reserva = Reserva::with(['quarto', 'pedidos' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $totalConsumo = $reserva->pedidos->sum('total');
        // dd($reserva->pedidos);
        $totalTaxaServicoConsumoConsumo = $reserva->pedidos->filter(function($pedido) {
            return $pedido->remover_taxa == 0;
        })->sum('taxa_servico');

        // dd($totalTaxaServicoConsumoConsumo);

        if($reserva->remover_taxa_servico == 1){
            $totalTaxaServicoConsumoConsumo = 'Cliente optou por remover';
        }

        // dd($reserva->remover_taxa);
        $itensConsumidos = $reserva->pedidos->flatMap(function($pedido) {
            return $pedido->itens->map(function($item) {
                return [
                    'produto' => $item->produto->descricao,
                    'quantidade' => $item->quantidade,
                    'valor_unitario' => $item->preco,
                    'total' => $item->quantidade * $item->preco,
                    'data_adicao' => $item->created_at,
                    'pedido' => $item->pedido
                ];
            });
        });

        // dd($itensConsumidos);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($pdfOptions);

        $html = view('pdf.extrato_reserva', compact('reserva', 'totalConsumo', 'totalTaxaServicoConsumoConsumo', 'itensConsumidos'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('extrato_reserva.pdf');
    }


    public function removerTaxaServico($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->remover_taxa_servico = 1;
        $reserva->save();

        return redirect()->back()->with('notice', 'Taxa de Servico removido com suceeso.');
    }

    public function moverReserva(Request $request, $id)
    {
        $request->validate([
            'quarto_id'    => 'required|integer|exists:quartos,id',
            'data_checkin' => 'required|date',
            'data_checkout'=> 'required|date|after:data_checkin',
        ]);

        $reserva     = Reserva::findOrFail($id);
        $quartoId    = $request->input('quarto_id');
        $dataCheckin = $request->input('data_checkin');
        $dataCheckout= $request->input('data_checkout');

        $conflito = Reserva::where('quarto_id', $quartoId)
            ->where('id', '!=', $id)
            ->where('situacao_reserva', '!=', 'CANCELADA')
            ->where(function ($q) use ($dataCheckin, $dataCheckout) {
                $q->whereBetween('data_checkin', [$dataCheckin, $dataCheckout])
                  ->orWhereBetween('data_checkout', [$dataCheckin, $dataCheckout])
                  ->orWhere(function ($q) use ($dataCheckin, $dataCheckout) {
                      $q->where('data_checkin', '<', $dataCheckin)
                        ->where('data_checkout', '>', $dataCheckout);
                  });
            })
            ->exists();

        if ($conflito) {
            return response()->json(['success' => false, 'message' => 'Quarto não disponível para as datas selecionadas.'], 409);
        }

        $reserva->quarto_id    = $quartoId;
        $reserva->data_checkin = $dataCheckin;
        $reserva->data_checkout= $dataCheckout;
        $reserva->save();

        return response()->json(['success' => true]);
    }

    public function transferirApartamento(Request $request, $id)
    {
        $request->validate([
            'quarto_id'          => 'required|integer|exists:quartos,id',
            'data_transferencia' => 'required|string',
        ]);

        $reserva          = Reserva::findOrFail($id);
        $novoQuartoId     = $request->input('quarto_id');
        $dataTransferenciaInput = trim((string) $request->input('data_transferencia'));
        $dataTransferencia = null;
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $dataTransferencia = Carbon::createFromFormat($format, $dataTransferenciaInput)->format('Y-m-d');
                break;
            } catch (\Exception $e) {
                // tenta o próximo formato
            }
        }

        if (!$dataTransferencia) {
            return redirect()->back()->with('error', 'O campo data transferência não é uma data válida.');
        }

        $dataTransferenciaCarbon = Carbon::parse($dataTransferencia)->startOfDay();
        $dataCheckinAtual = Carbon::parse($reserva->data_checkin);
        $dataCheckoutAtual = Carbon::parse($reserva->data_checkout);

        // Preserva a distancia entre check-in e check-out ao mover a reserva.
        $duracaoDias = $dataCheckinAtual->copy()->startOfDay()->diffInDays($dataCheckoutAtual->copy()->startOfDay());
        if ($duracaoDias < 1) {
            $duracaoDias = 1;
        }

        $novoCheckin = $dataTransferenciaCarbon->copy()->setTime(
            intval($dataCheckinAtual->format('H')),
            intval($dataCheckinAtual->format('i')),
            intval($dataCheckinAtual->format('s'))
        );
        $novoCheckout = $dataTransferenciaCarbon->copy()->addDays($duracaoDias)->setTime(
            intval($dataCheckoutAtual->format('H')),
            intval($dataCheckoutAtual->format('i')),
            intval($dataCheckoutAtual->format('s'))
        );

        $conflito = Reserva::where('quarto_id', $novoQuartoId)
            ->where('id', '!=', $id)
            ->where('situacao_reserva', '!=', 'CANCELADA')
            ->where(function ($q) use ($novoCheckin, $novoCheckout) {
                $q->whereBetween('data_checkin', [$novoCheckin, $novoCheckout])
                  ->orWhereBetween('data_checkout', [$novoCheckin, $novoCheckout])
                  ->orWhere(function ($q) use ($novoCheckin, $novoCheckout) {
                      $q->where('data_checkin', '<', $novoCheckin)
                        ->where('data_checkout', '>', $novoCheckout);
                  });
            })
            ->exists();

        if ($conflito) {
            return redirect()->back()->with('error', 'Quarto não disponível para as datas selecionadas.');
        }

        $novoQuarto = Quarto::find($novoQuartoId);
        $reserva->quarto_id    = $novoQuartoId;
        $reserva->data_checkin = $novoCheckin->format('Y-m-d H:i:s');
        $reserva->data_checkout = $novoCheckout->format('Y-m-d H:i:s');

        // Mantém o carrinho serializado alinhado ao quarto/data atuais para não "desfazer" a transferência no próximo save.
        $cart = $reserva->getCartSerializedAttribute();
        $isCartList = is_array($cart) && isset($cart[0]) && is_array($cart[0]);
        $cartItem = $isCartList ? $cart[0] : (is_array($cart) ? $cart : []);

        if (is_array($cartItem) && !empty($cartItem)) {
            $cartItem['quartoId'] = (string) $novoQuartoId;
            $cartItem['quartoNumero'] = (string) ($novoQuarto->numero ?? ($cartItem['quartoNumero'] ?? ''));
            $cartItem['quartoAndar'] = (string) ($novoQuarto->andar ?? ($cartItem['quartoAndar'] ?? ''));
            $cartItem['quartoClassificacao'] = (string) ($novoQuarto->classificacao ?? ($cartItem['quartoClassificacao'] ?? ''));
            $cartItem['dataCheckin'] = $novoCheckin->format('d/m/Y');
            $cartItem['dataCheckout'] = $novoCheckout->format('d/m/Y');
            $reserva->cart_serialized = json_encode($isCartList ? [$cartItem] : $cartItem, JSON_UNESCAPED_UNICODE);
        }

        $reserva->save();

        return redirect()
            ->to(route('admin.reservas.edit', ['id' => $id]) . '#transferencia')
            ->with('notice', 'Transferência realizada com sucesso.');
    }

    public function adicionarAcompanhante(Request $request, $id)
    {
        $reserva = Reserva::findOrFail($id);

        $request->validate([
            'nome'           => 'required|string|max:255',
            'cpf'            => 'nullable|string|max:20',
            'tipo'           => 'required|in:Adulto,Criança mais de 7 anos,Criança até 7 anos',
            'data_nascimento'=> 'nullable|date',
            'email'          => 'nullable|email|max:255',
            'telefone'       => 'nullable|string|max:20',
        ]);

        // Tenta vincular a um cliente existente pelo CPF
        $clienteId = null;
        if ($request->filled('cpf')) {
            $cpfLimpo = preg_replace('/\D/', '', $request->cpf);
            $cliente = \App\Models\Cliente::where('cpf', 'like', "%{$cpfLimpo}%")->first();
            if ($cliente) {
                $clienteId = $cliente->id;
            }
        }

        $acompanhante = \App\Models\Acompanhante::create([
            'reserva_id'     => $reserva->id,
            'cliente_id'     => $clienteId,
            'nome'           => $request->nome,
            'cpf'            => $request->cpf,
            'tipo'           => $request->tipo,
            'data_nascimento'=> $request->data_nascimento,
            'email'          => $request->email,
            'telefone'       => $request->telefone,
        ]);

        return response()->json([
            'success'      => true,
            'acompanhante' => $acompanhante,
            'cliente_id'   => $clienteId,
            'edit_url'     => $clienteId ? route('admin.clientes.edit', ['id' => $clienteId]) : null,
        ]);
    }

    public function removerAcompanhante($id, $aid)
    {
        Reserva::findOrFail($id);
        $acompanhante = \App\Models\Acompanhante::where('reserva_id', $id)->findOrFail($aid);
        $acompanhante->delete();

        return response()->json(['success' => true]);
    }

    public function salvarRefeicoes(Request $request, $id)
    {
        Reserva::findOrFail($id);

        \App\Models\ReservaRefeicao::where('reserva_id', $id)->delete();

        foreach ($request->input('refeicoes', []) as $refeicao) {
            \App\Models\ReservaRefeicao::create([
                'reserva_id'     => $id,
                'hospede_nome'   => $refeicao['hospede_nome'] ?? '',
                'hospede_tipo'   => $refeicao['hospede_tipo'] ?? 'titular',
                'acompanhante_id'=> $refeicao['acompanhante_id'] ?? null,
                'cafe'           => isset($refeicao['cafe']) ? 1 : 0,
                'almoco'         => isset($refeicao['almoco']) ? 1 : 0,
                'jantar'         => isset($refeicao['jantar']) ? 1 : 0,
            ]);
        }

        return redirect()
            ->to(route('admin.reservas.edit', ['id' => $id]) . '#refeicoes')
            ->with('notice', 'Refeições salvas com sucesso.');
    }
}


