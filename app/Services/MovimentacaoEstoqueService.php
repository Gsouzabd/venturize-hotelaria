<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\Produto;
use Illuminate\Http\Request;
use App\Models\MovimentacaoEstoque;
use Illuminate\Support\Facades\Auth;

class MovimentacaoEstoqueService
{
    public function handleMovimentacoes(Request $request)
    {
        foreach ($request->movimentacoes as $movimentacao) {
            $tipo = $movimentacao['tipo_movimento'];

            switch ($tipo) {
                case 'entrada':
                    $this->registrarEntrada($movimentacao);
                    break;
                case 'saida':
                    $this->registrarSaida($movimentacao);
                    break;
                case 'transferencia':
                    $this->registrarTransferencia($movimentacao);
                    break;
                default:
                    throw new \Exception("Tipo de movimentação desconhecido: $tipo");
            }
        }
    }

    public function registrarEntrada(array $movimentacao)
    {
        // Atualizar o estoque
        $estoque = Estoque::firstOrNew([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['local_estoque_id'],
        ]);

        $estoque->quantidade += $movimentacao['quantidade'];
        $estoque->save();

        // Registrar a movimentação
        $movimentacaoCreated = MovimentacaoEstoque::create([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_destino_id' => $movimentacao['local_estoque_id'],
            'quantidade' => $movimentacao['quantidade'],
            'tipo' => 'entrada',
            'usuario_id' => Auth::id(),
            'data_movimentacao' => now(),
            'preco_custo' => $movimentacao['valor_unitario'],
            'justificativa' => $movimentacao['justificativa'],
        ]);

        // Atualiando o preço de custo do produto caso o valor unitário seja diferente
        if ($movimentacaoCreated) {
            $produto = Produto::find($movimentacao['produto_id']);  
        
            // Converter valor_unitario para o formato correto
            $valorUnitario = str_replace(',', '.', $movimentacao['valor_unitario']);
        
            if ($produto->preco_custo != $valorUnitario || !$produto->preco_custo) {
                $produto->preco_custo = $valorUnitario;
                $produto->save();
            }
        }
    }

    public function registrarSaida(array $movimentacao)
    {
        // Atualizar o estoque
        $estoque = Estoque::where([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['local_estoque_id'],
        ])->first();

        if (!$estoque || $estoque->quantidade < $movimentacao['quantidade']) {
            throw new \Exception('Estoque insuficiente para o produto ID: ' . $movimentacao['produto_id']);
        }


        $estoque->quantidade -= $movimentacao['quantidade'];

        $estoque->save();

        // Registrar a movimentação
        MovimentacaoEstoque::create([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_origem_id' => $movimentacao['local_estoque_id'],
            'quantidade' => $movimentacao['quantidade'],
            'tipo' => 'saida',
            'usuario_id' => Auth::id(),
            'data_movimentacao' => now(),
            'preco_venda' => $movimentacao['valor_unitario'],
            'justificativa' => $movimentacao['justificativa'],
        ]);

    }


    public function registrarTransferencia(array $movimentacao) 
    {
        // dd($movimentacao);
        // Atualizar o estoque de origem
        $estoqueOrigem = Estoque::where([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['estoque_origem_id'],
        ])->first();

        if (!$estoqueOrigem || $estoqueOrigem->quantidade < $movimentacao['quantidade']) {
            throw new \Exception('Estoque insuficiente para o produto ID: ' . $movimentacao['produto_id']);
        }

        $estoqueOrigem->quantidade -= $movimentacao['quantidade'];
        $estoqueOrigem->save();

        // Atualizar o estoque de destino
        $estoqueDestino = Estoque::firstOrNew([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['estoque_destino_id'],
        ]);

        $estoqueDestino->quantidade += $movimentacao['quantidade'];
        $estoqueDestino->save();

        // Registrar a movimentação
        MovimentacaoEstoque::create([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_origem_id' => $movimentacao['estoque_origem_id'],
            'local_estoque_destino_id' => $movimentacao['estoque_destino_id'],
            'quantidade' => $movimentacao['quantidade'],
            'tipo' => 'transferencia',
            'usuario_id' => Auth::id(),
            'data_movimentacao' => now(),
            'justificativa' => $movimentacao['justificativa'],
        ]);
    }

}