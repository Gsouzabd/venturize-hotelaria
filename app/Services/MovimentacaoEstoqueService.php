<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use Illuminate\Http\Request;
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

            if ($produto->preco_custo != $valorUnitario || ! $produto->preco_custo) {
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

        if (! $estoque) {
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

    /**
     * Reverte o efeito de uma movimentação no saldo e cria a movimentação-espelho
     * (tipo 'estorno'). Retorna avisos (ex.: saldo negativado) para exibir ao usuário.
     *
     * @return string[] avisos
     */
    public function estornar(MovimentacaoEstoque $movimentacao, ?string $justificativa = null): array
    {
        if ($movimentacao->tipo === 'estorno') {
            throw new \Exception('Não é possível estornar um estorno.');
        }

        if ($movimentacao->estornada_por_id) {
            throw new \Exception('Esta movimentação já foi estornada (movimentação #'.$movimentacao->estornada_por_id.').');
        }

        return DB::transaction(function () use ($movimentacao, $justificativa) {
            $avisos = [];

            // Reversão do saldo: entrada devolve do destino; saída/perda devolvem à origem;
            // transferência reverte os dois lados
            $ajustes = match ($movimentacao->tipo) {
                'entrada' => [[$movimentacao->local_estoque_destino_id, -$movimentacao->quantidade]],
                'saida', 'perda' => [[$movimentacao->local_estoque_origem_id, +$movimentacao->quantidade]],
                'transferencia' => [
                    [$movimentacao->local_estoque_origem_id, +$movimentacao->quantidade],
                    [$movimentacao->local_estoque_destino_id, -$movimentacao->quantidade],
                ],
                default => throw new \Exception("Tipo de movimentação não estornável: {$movimentacao->tipo}"),
            };

            foreach ($ajustes as [$localId, $delta]) {
                $estoque = Estoque::firstOrNew([
                    'produto_id' => $movimentacao->produto_id,
                    'local_estoque_id' => $localId,
                ]);

                $estoque->quantidade = ($estoque->quantidade ?? 0) + $delta;
                $estoque->save();

                if ($estoque->quantidade < 0) {
                    $avisos[] = "Atenção: o saldo de {$movimentacao->produto->descricao} em {$estoque->localEstoque->nome} ficou negativo ({$estoque->quantidade}).";
                }
            }

            $motivo = $justificativa ? ": {$justificativa}" : '';

            $estorno = MovimentacaoEstoque::create([
                'produto_id' => $movimentacao->produto_id,
                'local_estoque_origem_id' => $movimentacao->local_estoque_destino_id,
                'local_estoque_destino_id' => $movimentacao->local_estoque_origem_id,
                'quantidade' => $movimentacao->quantidade,
                'tipo' => 'estorno',
                'usuario_id' => Auth::id(),
                'data_movimentacao' => now(),
                'valor_unitario_custo' => $movimentacao->valor_unitario_custo,
                'valor_unitario_venda' => $movimentacao->valor_unitario_venda,
                'justificativa' => "Estorno da movimentação #{$movimentacao->id}{$motivo}",
            ]);

            $movimentacao->update(['estornada_por_id' => $estorno->id]);

            return $avisos;
        });
    }
}
