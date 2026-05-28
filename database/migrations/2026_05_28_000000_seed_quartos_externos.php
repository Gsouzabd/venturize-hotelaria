<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $quartos = [
            [
                'andar'                   => 'Externo',
                'numero'                  => 1,
                'referencia'              => 'Quarto 01',
                'ramal'                   => null,
                'posicao_quarto'          => 'Frente',
                'quantidade_cama_casal'   => 1,
                'quantidade_cama_solteiro'=> 0,
                'classificacao'           => 'Externo',
                'acessibilidade'          => 'Não',
                'inativo'                 => 'Não',
                'created_at'              => now(),
                'updated_at'              => now(),
            ],
            [
                'andar'                   => 'Externo',
                'numero'                  => 2,
                'referencia'              => 'Quarto 02',
                'ramal'                   => null,
                'posicao_quarto'          => 'Frente',
                'quantidade_cama_casal'   => 1,
                'quantidade_cama_solteiro'=> 0,
                'classificacao'           => 'Externo',
                'acessibilidade'          => 'Não',
                'inativo'                 => 'Não',
                'created_at'              => now(),
                'updated_at'              => now(),
            ],
            [
                'andar'                   => 'Externo',
                'numero'                  => 3,
                'referencia'              => 'Suíte Master',
                'ramal'                   => null,
                'posicao_quarto'          => 'Frente',
                'quantidade_cama_casal'   => 1,
                'quantidade_cama_solteiro'=> 0,
                'classificacao'           => 'Suíte Master',
                'acessibilidade'          => 'Não',
                'inativo'                 => 'Não',
                'created_at'              => now(),
                'updated_at'              => now(),
            ],
        ];

        foreach ($quartos as $quarto) {
            $existe = DB::table('quartos')
                ->where('andar', $quarto['andar'])
                ->where('numero', $quarto['numero'])
                ->exists();

            if (! $existe) {
                DB::table('quartos')->insert($quarto);
            }
        }
    }

    public function down(): void
    {
        DB::table('quartos')
            ->where('andar', 'Externo')
            ->whereIn('numero', [1, 2, 3])
            ->delete();
    }
};
