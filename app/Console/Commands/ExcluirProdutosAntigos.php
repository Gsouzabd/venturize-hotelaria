<?php

namespace App\Console\Commands;

use App\Models\Bar\ItemPedido;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Models\ProdutoComposicao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExcluirProdutosAntigos extends Command
{
    protected $signature = 'produtos:excluir-antigos
                            {--dry-run : Apenas lista o que seria excluído, sem alterar nada}
                            {--forcar : Executa a exclusão de fato (exige confirmação)}';

    protected $description = 'Exclui produtos antigos (anteriores à importação Bitz), com backup CSV. '
        .'Produtos com itens de pedido do bar ou usados como insumo de produtos mantidos são pulados. '
        .'A exclusão apaga em cascata estoques e movimentações do produto.';

    // Marcadores gravados pelas migrations import_bitz_* — produtos fora deles são os "antigos"
    private const MARCADORES_BITZ = ['importado_bitz', 'importado_bitz_saldo'];

    public function handle()
    {
        $candidatos = Produto::query()
            ->where(function ($q) {
                $q->whereNull('complemento')->orWhereNotIn('complemento', self::MARCADORES_BITZ);
            })
            ->withSum('estoques as saldo_total', 'quantidade')
            ->orderBy('id')
            ->get();

        if ($candidatos->isEmpty()) {
            $this->info('Nenhum produto antigo encontrado — nada a fazer.');

            return 0;
        }

        $candidatoIds = $candidatos->pluck('id');

        // Produtos com itens de pedido do bar: excluir apagaria itens de pedidos históricos (FK cascade)
        $idsComPedidos = ItemPedido::whereIn('produto_id', $candidatoIds)->distinct()->pluck('produto_id');

        // Produtos usados como insumo em receitas de produtos que vão permanecer:
        // excluir apagaria a linha de composição da receita do produto mantido
        $idsInsumoDeMantidos = ProdutoComposicao::whereIn('insumo_id', $candidatoIds)
            ->whereNotIn('produto_id', $candidatoIds)
            ->distinct()
            ->pluck('insumo_id');

        $movsPorProduto = MovimentacaoEstoque::whereIn('produto_id', $candidatoIds)
            ->groupBy('produto_id')
            ->select('produto_id', DB::raw('count(*) as total'))
            ->pluck('total', 'produto_id');

        [$pulados, $excluiveis] = $candidatos->partition(
            fn ($p) => $idsComPedidos->contains($p->id) || $idsInsumoDeMantidos->contains($p->id)
        );

        $this->table(
            ['ID', 'Código', 'Descrição', 'Saldo', 'Movs', 'Ação'],
            $candidatos->map(fn ($p) => [
                $p->id,
                $p->codigo_interno ?? '—',
                mb_strimwidth($p->descricao, 0, 45, '…'),
                (float) ($p->saldo_total ?? 0),
                $movsPorProduto[$p->id] ?? 0,
                $idsComPedidos->contains($p->id) ? 'PULAR (pedidos do bar)'
                    : ($idsInsumoDeMantidos->contains($p->id) ? 'PULAR (insumo de produto mantido)' : 'excluir'),
            ])
        );

        $this->info("Candidatos: {$candidatos->count()} | A excluir: {$excluiveis->count()} | Pulados: {$pulados->count()}");
        $comSaldo = $excluiveis->filter(fn ($p) => (float) ($p->saldo_total ?? 0) != 0);
        if ($comSaldo->isNotEmpty()) {
            $this->warn("Atenção: {$comSaldo->count()} produto(s) a excluir ainda têm saldo em estoque (ids: {$comSaldo->pluck('id')->implode(', ')}). O saldo será apagado junto.");
        }

        if (! $this->option('forcar')) {
            $this->comment('Dry-run: nada foi alterado. Rode com --forcar para excluir de fato.');

            return 0;
        }

        if ($excluiveis->isEmpty()) {
            $this->info('Nada a excluir.');

            return 0;
        }

        if (! $this->confirm("Excluir DEFINITIVAMENTE {$excluiveis->count()} produto(s) e, em cascata, seus estoques e movimentações?")) {
            $this->comment('Cancelado.');

            return 1;
        }

        $dir = $this->exportarBackup($excluiveis->pluck('id'));
        $this->info("Backup CSV gravado em: {$dir}");

        $total = DB::transaction(fn () => Produto::whereIn('id', $excluiveis->pluck('id'))->delete());

        $this->info("{$total} produto(s) excluído(s). {$pulados->count()} pulado(s) — considere inativá-los (UPDATE produtos SET ativo = 0).");

        return 0;
    }

    private function exportarBackup($ids): string
    {
        $dir = storage_path('app/backups/produtos-antigos-'.now()->format('Ymd-His'));
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $tabelas = [
            'produtos.csv' => DB::table('produtos')->whereIn('id', $ids),
            'estoques.csv' => DB::table('estoques')->whereIn('produto_id', $ids),
            'movimentacoes_estoque.csv' => DB::table('movimentacoes_estoque')->whereIn('produto_id', $ids),
            'produto_composicaos.csv' => DB::table('produto_composicaos')
                ->where(fn ($q) => $q->whereIn('produto_id', $ids)->orWhereIn('insumo_id', $ids)),
        ];

        foreach ($tabelas as $arquivo => $query) {
            $handle = fopen($dir.DIRECTORY_SEPARATOR.$arquivo, 'w');
            $primeira = true;

            foreach ($query->orderBy('id')->cursor() as $row) {
                $row = (array) $row;
                if ($primeira) {
                    fputcsv($handle, array_keys($row));
                    $primeira = false;
                }
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }

        return $dir;
    }
}
