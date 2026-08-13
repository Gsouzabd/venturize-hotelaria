<?php

use App\Models\GrupoUsuario;
use App\Models\Permissao;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cria as permissões 'estoque_entrada' e 'estoque_saida' (se ainda não existirem) e
     * as vincula a todo GrupoUsuario que já possui 'gerenciar_estoque', para que grupos
     * existentes não percam acesso às telas de entrada/saída após a nova checagem
     * granular de permissões no MovimentacaoEstoqueController.
     */
    public function up(): void
    {
        $novasPermissoes = [
            'estoque_entrada' => 'Estoque - Entrada',
            'estoque_saida' => 'Estoque - Saída',
        ];

        $novasPermissoesIds = [];
        foreach ($novasPermissoes as $nome => $label) {
            $permissao = Permissao::firstOrCreate(['nome' => $nome]);
            $novasPermissoesIds[] = $permissao->id;
        }

        $permissaoGerenciarEstoque = Permissao::where('nome', 'gerenciar_estoque')->first();

        if (! $permissaoGerenciarEstoque) {
            return;
        }

        $gruposComGerenciarEstoque = GrupoUsuario::whereHas(
            'permissoes',
            fn ($q) => $q->where('permissoes.id', $permissaoGerenciarEstoque->id)
        )->get();

        foreach ($gruposComGerenciarEstoque as $grupo) {
            $grupo->permissoes()->syncWithoutDetaching($novasPermissoesIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissoesIds = Permissao::whereIn('nome', ['estoque_entrada', 'estoque_saida'])->pluck('id');

        DB::table('grupo_permissoes')->whereIn('permissao_id', $permissoesIds)->delete();
        Permissao::whereIn('nome', ['estoque_entrada', 'estoque_saida'])->delete();
    }
};
