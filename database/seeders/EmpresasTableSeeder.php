<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmpresasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('empresas')->insert([
            [
                'razao_social' => 'Empresa XYZ 1',
                'nome_fantasia' => 'Exemplo XYZ',
                'cnpj' => '63.264.233/0001-11',
                'inscricao_estadual' => Str::random(12),
                'email' => 'exemplo1@empresa.com',
                'telefone' => '123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'razao_social' => 'Empresa ABC 2',
                'nome_fantasia' => 'Exemplo  ABC2',
                'cnpj' => '63.264.233/0001-11',
                'inscricao_estadual' => Str::random(12),
                'email' => 'exemplo2@empresa.com',
                'telefone' => '987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Adicione mais registros conforme necessário
        ]);
    }
}