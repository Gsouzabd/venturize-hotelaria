<?php

namespace App\Services\Bar;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Produto;
use App\Models\Reserva;
use App\Models\Bar\Mesa;
use App\Models\Bar\Pedido;
use App\Models\LocalEstoque;
use Illuminate\Support\Facades\DB;
use App\Services\MovimentacaoEstoqueService;

class MesaService {

    private $movimentacaoEstoqueService;

    public function __construct(MovimentacaoEstoqueService $movimentacaoEstoqueService)
    {
        $this->movimentacaoEstoqueService = $movimentacaoEstoqueService;
    }

    public function abrirMesa($data)
    {
        // Encontrar a mesa pelo ID
        $mesa = Mesa::find($data['mesa_id']);
        $reserva = Reserva::find($data['reserva_id']);  

        // Verificar se a mesa está disponível
        if (strtolower($mesa->status) === 'disponível') {
            // Criar um novo pedido para essa mesa
            $pedido = Pedido::create([
                'reserva_id' =>  $reserva->id,   
                'mesa_id' => $mesa->id,
                'cliente_id' => $reserva->clienteResponsavel ? $reserva->clienteResponsavel->id : $reserva->clienteSolicitante->id,
                'status' => 'aberto', // Pedido está em andamento
                'total' => 0, // Inicialmente o total é zero
                'created_at' => Carbon::now('America/Sao_Paulo'),
            ]);


            if($pedido->reserva_id){
                // Alterar o status da mesa para 'ocupada'
                $mesa->status = 'ocupada';
                $mesa->save();
            }

            return $pedido; // Retorna o pedido recém-criado
        }

        return "Mesa Ocupada";
    }
    public function statusMesaNoDia()
    {
        $mesas = Mesa::all();
        $pedidos = Pedido::where('status', 'aberto')->get();

        $status = [];

        foreach ($mesas as $mesa) {
            $status[$mesa->id] = [
                'mesa' => $mesa,
                'status' => 'Livre',
                'pedido' => null
            ];
        }

        // dd($pedidos);
        foreach ($pedidos as $pedido) {
            if ($pedido->mesa) {
                $status[$pedido->mesa->id] = [
                    'mesa' => $pedido->mesa,
                    'status' => $pedido->status === 'aberto' ? 'Ocupada' : 'Livre',
                    'pedido' => $pedido
                ];
            }
        }

        // Ordenar pelo número da mesa
        usort($status, function ($a, $b) {
            return $a['mesa']->numero <=> $b['mesa']->numero;
        });

        return $status;
    }
    

    public function cancelarItemPedido($data) {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($data['pedido_id']);
    
        // Verificar se o pedido está aberto
        if ($pedido->status === 'aberto') {
            $itensCancelados = [];
    
            // Iterar sobre os itens a serem removidos
            foreach ($data['itens_cart'] as $item) {
                // Encontrar o item no pedido
                $itemPedido = $pedido->itens()->where('produto_id', $item['produto_id'])->first();
    
                if ($itemPedido) {
                    // Adicionar o item à lista de itens cancelados
                    $itensCancelados[] = [
                        'descricao' => Produto::find($item['produto_id'])->descricao,
                        'preco' => $itemPedido->preco,
                        'quantidade' => $item['quantidade'], // Usar a quantidade recebida na requisição
                    ];
    
                    // Verificar a quantidade a ser cancelada
                    if ($itemPedido->quantidade > $item['quantidade']) {
                        // Diminuir a quantidade do item no pedido
                        $itemPedido->quantidade -= $item['quantidade'];
                        $itemPedido->save();
                    } else {
                        // Remover o item do pedido
                        $itemPedido->delete();
                    }
    
                    // Registrar a entrada no estoque
                    $produto = Produto::find($item['produto_id']);
                    if ($produto->composicoes()->exists()) {
                        foreach ($produto->composicoes as $composicao) {
                            $this->movimentacaoEstoqueService->registrarEntrada([
                                'produto_id' => $composicao->insumo_id,
                                'local_estoque_id' => LocalEstoque::where('nome', 'Bar')->first()->id,
                                'quantidade' => $composicao->quantidade * $item['quantidade'],
                                'valor_unitario' => Produto::find($composicao->insumo_id)->preco_custo,
                                'justificativa' => 'Cancelamento de venda de produto no bar',
                            ]);
                        }
                    } else {
                        $this->movimentacaoEstoqueService->registrarEntrada([
                            'produto_id' => $item['produto_id'],
                            'local_estoque_id' => LocalEstoque::where('nome', 'Bar')->first()->id,
                            'quantidade' => $item['quantidade'],
                            'valor_unitario' => Produto::find($item['produto_id'])->preco_custo,
                            'justificativa' => 'Cancelamento de venda de produto no bar',
                        ]);
                    }
                }
            }
    
            // Atualizar o total do pedido
            $total = $pedido->itens()->sum(DB::raw('quantidade * preco'));
            $taxaServico = $pedido->pedido_apartamento ? 0 : $total * 0.1;

            $pedido->update([
                'total' => $total,
                'total_com_taxa' => $total + $taxaServico
            ]);
    
            return $itensCancelados; // Retorna os itens cancelados
        }
    
        return "Pedido Fechado";
    }
    
    public function gerarCupomCancelamento($idPedido, $itensCancelados) {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($idPedido);
    
        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
    
        // Definir o tamanho do papel para impressora térmica (80mm de largura)
        $customPaper = array(0, 0, 226.77, 841.89); // 80mm x 297mm (A4 height for long receipts)
        $dompdf->setPaper($customPaper);
    
        // Dados do pedido e itens cancelados
        $html = view('pdf.cupom_cancelamento', compact('pedido', 'itensCancelados',))->render();
    
        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);
    
        // Renderizar o PDF
        $dompdf->render();
    
        // Enviar o PDF para o navegador
        return $dompdf->output();
    }

    public function adicionarItemPedido($data) {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($data['pedido_id']);
    
        // Verificar se o pedido está aberto
        if ($pedido->status === 'aberto') {
            $itens = [];
    
            // Iterar sobre os itens temporários
            foreach ($data['itens_temp'] as $item) {
                // Verificar se o item já existe no pedido
                $existingItem = $pedido->itens()->where('produto_id', $item['produto_id'])->first();
                $produto = Produto::find($item['produto_id']);
    
                if ($existingItem) {
                    // Atualizar a quantidade do item existente
                    $existingItem->quantidade += $item['quantidade'];
                    $existingItem->save();
                
                    // Criar uma cópia do item existente para adicionar ao array de itens
                    $updatedItem = clone $existingItem;
                    $updatedItem->quantidade = $item['quantidade']; // Usar a quantidade recebida na requisição
                    $itens[] = $updatedItem;
                } else {
                    // Adicionar um novo item ao pedido
                    $novoItem = $pedido->itens()->create([
                        'produto_id' => $item['produto_id'],
                        'quantidade' => $item['quantidade'],
                        'preco' => Produto::find($item['produto_id'])->preco_venda, // Obtenha o preço do produto
                        'operador_id' => auth()->user()->id, // Adicionar o operador ao item
                    ]);
                    $itens[] = $novoItem;
                }

                if($produto->composicoes()->exists()){
                    foreach ($produto->composicoes as $composicao) {
                        $this->movimentacaoEstoqueService->registrarSaida([
                            'produto_id' => $composicao->insumo_id,
                            'local_estoque_id' => LocalEstoque::where('nome', 'Bar')->first()->id,
                            'quantidade' => $composicao->quantidade,
                            'valor_unitario_venda' => Produto::find($composicao->insumo_id)->preco_venda,
                            'justificativa' => 'Venda de produto no bar',
                        ]);
                    }
                }else{
                    $this->movimentacaoEstoqueService->registrarSaida([
                        'produto_id' => $item['produto_id'],
                        'local_estoque_id' => LocalEstoque::where('nome', 'Bar')->first()->id,
                        'quantidade' => $item['quantidade'],
                        'valor_unitario_venda' => Produto::find($item['produto_id'])->preco_venda,
                        'justificativa' => 'Venda de produto no bar',
                    ]);
                }

            }
    
            // Atualizar o total do pedido
            $total = $pedido->itens()->sum(DB::raw('quantidade * preco'));
            $taxaServico = $pedido->pedido_apartamento ? 0 : $total * 0.1;
            $pedido->update([
                'total' => $total,
                'taxa_servico' => $taxaServico,
                'removar_taxa' => $pedido->pedido_apartamento ? 1 : $pedido->remover_taxa,
                'total_com_taxa' => $total + $taxaServico
            ]);
    
            // Adicionar a descrição do produto aos itens
            foreach ($itens as $item) {
                $item['descricao'] = Produto::find($item['produto_id'])->descricao;
            }
    
            return $itens; // Retorna os itens recém-criados ou atualizados
        }
    
        return "Pedido Fechado";
    }

    public function fecharConta($idPedido, $removerTaxaServico = false)
    {
        // dd($removerTaxaServico);
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($idPedido);
        if ($pedido instanceof \Illuminate\Support\Collection) {
            $pedido = $pedido->first();
        }

        if ($pedido && $pedido->status === 'aberto') {
            // Atualizar o status do pedido para 'fechado'
            $pedido->status = 'fechado';
            $pedido->remover_taxa = $removerTaxaServico != "false" ? 1 : 0;
            if($pedido->pedido_apartamento) {
                $pedido->remover_taxa = 1;
            }
            $pedido->save();

            // Atualizar o status da mesa para 'disponível'
            $mesa = Mesa::find($pedido->mesa_id);
            if ($mesa) {
                $mesa->status = 'disponível';
                $mesa->save();
            }

            // Gerar o cupom de fechamento
            $this->gerarCupomFechamento($idPedido);

            return $pedido->id;
        }

        return null;
    }

    public function gerarCupomFechamento($idPedido)
    {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::with('itens.produto')->find($idPedido);
        if ($pedido instanceof \Illuminate\Support\Collection) {
            $pedido = $pedido->first();
        }
        // dd($pedido);
        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Definir o tamanho do papel para impressora térmica (80mm de largura)
        $customPaper = array(0, 0, 226.77, 841.89); // 80mm x 297mm (A4 height for long receipts)
        $dompdf->setPaper($customPaper);

        // Dados do pedido e itens
        $html = view('pdf.cupom_fechamento', compact('pedido'))->render();

        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);

        // Renderizar o PDF
        $dompdf->render();

        // Enviar o PDF para o navegador
        return $dompdf->output();
    }


    public function gerarCupomItemAdicionado($idPedido, $novosItens) {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($idPedido);
        if ($pedido instanceof \Illuminate\Support\Collection) {
            $pedido = $pedido->first();
        }
    
        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
    
        // Definir o tamanho do papel para impressora térmica (80mm de largura)
        $customPaper = array(0, 0, 226.77, 841.89); // 80mm x 297mm (A4 height for long receipts)
        $dompdf->setPaper($customPaper);
    
        // Dados do pedido e itens adicionados
        $html = view('pdf.cupom_item_adicionado', compact('pedido', 'novosItens'))->render();
    
        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);
    
        // Renderizar o PDF
        $dompdf->render();
    
        // Enviar o PDF para o navegador
        return $dompdf->output();
    }

    public function gerarCupomParcial($idPedido)
    {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::with('itens.produto')->find($idPedido);
        if ($pedido instanceof \Illuminate\Support\Collection) {
            $pedido = $pedido->first();
        }

        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Definir o tamanho do papel para impressora térmica (80mm de largura)
        $customPaper = array(0, 0, 226.77, 841.89); // 80mm x 297mm (A4 height for long receipts)
        $dompdf->setPaper($customPaper);

        // Dados do pedido e itens
        $html = view('pdf.cupom_parcial', compact('pedido'))->render();

        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);

        // Renderizar o PDF
        $dompdf->render();

        // Enviar o PDF para o navegador
        return $dompdf->output();
    }

    public function gerarExtratoParcial($idPedido)
    {
        // Encontrar o pedido pelo ID
        $pedido = Pedido::with('itens.produto')->find($idPedido);
        $reserva = $pedido->reserva;
        if ($pedido instanceof \Illuminate\Support\Collection) {
            $pedido = $pedido->first();
        }

        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);


        // Dados do pedido e itens
        $html = view('pdf.extrato_parcial', compact('pedido', 'reserva'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('extrato_reserva.pdf');
    }
}