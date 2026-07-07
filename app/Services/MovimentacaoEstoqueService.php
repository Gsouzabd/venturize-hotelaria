<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\Produto;
use Illuminate\Http\Request;
use App\Models\MovimentacaoEstoque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimentacaoEstoqueService
{
    public function handleMovimentacoes(Request $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->movimentacoes as $movimentacao) {
                $tipo = $movimentacao['tipo_movimento'];

                switch ($tipo) {
                    case 'entrada':
                        $this->registrarEntrada($movimentacao);
                        break;
                    case 'saida':
                        $this->registrarSaida($movimentacao);
                        break;
                    case 'perda':
                        $this->registrarSaida($movimentacao, 'perda');
                        break;
                    case 'transferencia':
                        $this->registrarTransferencia($movimentacao);
                        break;
                    default:
                        throw new \Exception("Tipo de movimentação desconhecido: $tipo");
                }
            }
        });
    }

    // Aceita valores digitados com vírgula decimal ("12,50"); vazio vira null
    private function normalizarDecimal(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        return (float) str_replace(',', '.', trim($valor));
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

        $valorUnitario = $this->normalizarDecimal($movimentacao['valor_unitario'] ?? null);

        // Registrar a movimentação
        $movimentacaoCreated = MovimentacaoEstoque::create([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_destino_id' => $movimentacao['local_estoque_id'],
            'quantidade' => $movimentacao['quantidade'],
            'tipo' => 'entrada',
            'usuario_id' => Auth::id(),
            'data_movimentacao' => now(),
            'valor_unitario_custo' => $valorUnitario,
            'justificativa' => $movimentacao['justificativa'] ?? null,
        ]);

        // Atualiando o preço de custo do produto caso o valor unitário seja diferente
        if ($movimentacaoCreated && $valorUnitario !== null) {
            $produto = Produto::find($movimentacao['produto_id']);

            if ($produto->preco_custo != $valorUnitario || !$produto->preco_custo) {
                $produto->preco_custo = $valorUnitario;
                $produto->save();
            }
        }
    }

    public function registrarSaida(array $movimentacao, string $tipo = 'saida')
    {
        // Atualizar o estoque
        $estoque = Estoque::where([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['local_estoque_id'],
        ])->first();

        
        if (!$estoque) {
            // Criar um novo estoque mesmo que o valor seja negativo
            $estoque = new Estoque([
                'produto_id' => $movimentacao['produto_id'],
                'local_estoque_id' => $movimentacao['local_estoque_id'],
                'quantidade' => 0,
            ]);
        }


        $estoque->quantidade -= $movimentacao['quantidade'];

        $estoque->save();

        // Registrar a movimentação
        MovimentacaoEstoque::create([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_origem_id' => $movimentacao['local_estoque_id'],
            'quantidade' => $movimentacao['quantidade'],
            'tipo' => $tipo,
            'usuario_id' => Auth::id(),
            'data_movimentacao' => now(),
            'valor_unitario_venda' => $this->normalizarDecimal($movimentacao['valor_unitario'] ?? null),
            'justificativa' => $movimentacao['justificativa'] ?? null,
        ]);
    }


    public function registrarTransferencia(array $movimentacao)
    {
        // Atualizar o estoque de origem (cria negativado se não existir, como na saída)
        $estoqueOrigem = Estoque::firstOrNew([
            'produto_id' => $movimentacao['produto_id'],
            'local_estoque_id' => $movimentacao['estoque_origem_id'],
        ]);

        $estoqueOrigem->quantidade = ($estoqueOrigem->quantidade ?? 0) - $movimentacao['quantidade'];
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
            'justificativa' => $movimentacao['justificativa'] ?? null,
        ]);
    }

}