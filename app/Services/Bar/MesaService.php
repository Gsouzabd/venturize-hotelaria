<?php

namespace App\Services\Bar;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Produto;
use App\Models\Reserva;
use App\Models\Bar\Mesa;
use App\Models\Bar\Pedido;
use Illuminate\Support\Facades\DB;

class MesaService {

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
                'cliente_id' => $reserva->clienteSolicitante ? $reserva->clienteSolicitante->id : $reserva->clienteResponsavel->id,
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

    public function fecharMesa($mesaId)
    {
        // Encontrar a mesa pelo ID
        $mesa = Mesa::find($mesaId);

        // Verificar se a mesa está ocupada
        if ($mesa->status === 'ocupada') {
            // Encontrar o pedido aberto para essa mesa
            $pedido = Pedido::where('mesa_id', $mesaId)->where('status', 'aberto')->first();

            if ($pedido) {
                // Calcular o total do pedido (exemplo simples somando o preço dos itens)
                $total = $pedido->itens()->sum(DB::raw('quantidade * preco'));

                // Atualizar o pedido com o total e marcar como 'pago'
                $pedido->update([
                    'status' => 'pago',
                    'total' => $total,
                ]);

                // Liberar a mesa (alterar status para 'disponível')
                $mesa->update([
                    'status' => 'disponível',
                ]);

                return response()->json(['message' => 'Mesa fechada e pagamento realizado com sucesso.']);
            }

            return response()->json(['error' => 'Não há pedidos abertos para essa mesa.'], 400);
        }

        return response()->json(['error' => 'Mesa já está disponível ou reservada.'], 400);
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
            // Iterar sobre os itens a serem removidos
            foreach ($data['itens_cart'] as $item) {
                // Remover o item do pedido
                $pedido->itens()->where('produto_id', $item['produto_id'])->delete();
            }

            // Atualizar o total do pedido
            $total = $pedido->itens()->sum(DB::raw('quantidade * preco'));
            $pedido->update([
                'total' => $total,
            ]);

            return "Item removido com sucesso";
        }

        return "Pedido Fechado";
    }

    public function adicionarItemPedido($data){
        // Encontrar o pedido pelo ID
        $pedido = Pedido::find($data['pedido_id']);
    
        // Verificar se o pedido está aberto
        if ($pedido->status === 'aberto') {
            $itens = [];
    
            // Iterar sobre os itens temporários
            foreach ($data['itens_temp'] as $item) {
                // Adicionar um novo item ao pedido
                $novoItem = $pedido->itens()->create([
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco' => Produto::find($item['produto_id'])->preco_venda, // Obtenha o preço do produto
                ]);
    
                $itens[] = $novoItem;
    
                // Gerar cupom para o item adicionado
                $this->gerarCupom($pedido, $novoItem);
            }
    
            // Atualizar o total do pedido
            $total = $pedido->itens()->sum(DB::raw('quantidade * preco'));
            $pedido->update([
                'total' => $total,
            ]);
    
            return $itens; // Retorna os itens recém-criados
        }
    
        return "Pedido Fechado";
    }


    public function gerarCupom($idPedido, $novoItem) {
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
    
        // dd($pedido);
        // Dados do pedido e item adicionado
        $html = view('pdf.cupom', compact('pedido', 'novoItem'))->render();
    
        // Carregar o HTML no Dompdf
        $dompdf->loadHtml($html);
    
        // Renderizar o PDF
        $dompdf->render();
    
        // Enviar o PDF para o navegador
        return $dompdf->output();
    }
}