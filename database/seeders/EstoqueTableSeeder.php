<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estoque;
use App\Models\LocalEstoque;

class EstoqueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Local Estoque records
        LocalEstoque::create(['nome' => 'Cozinha']);
        LocalEstoque::create(['nome' => 'Bar']);
        LocalEstoque::create(['nome' => 'Recepção']);
        LocalEstoque::create(['nome' => 'Apartamento']);

        // Create Estoque records
        Estoque::create([
            'produto_id' => 2, // Cerveja
            'local_estoque_id' => 2, // Bar
            'quantidade' => 100,
        ]);

        Estoque::create([
            'produto_id' => 1, // Coca-Cola
            'local_estoque_id' => 2, // Bar
            'quantidade' => 200,
        ]);

        Estoque::create([
            'produto_id' => 3, // Água Mineral
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 150,
        ]);

        Estoque::create([
            'produto_id' => 4, // Suco de Laranja
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 80,
        ]);

        Estoque::create([
            'produto_id' => 6, // Hambúrguer
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 50,
        ]);

        Estoque::create([
            'produto_id' => 7, // Pizza Margherita
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 30,
        ]);

        Estoque::create([
            'produto_id' => 8, // Salada Caesar
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 40,
        ]);

        Estoque::create([
            'produto_id' => 9, // Batata Frita
            'local_estoque_id' => 1, // Cozinha
            'quantidade' => 60,
        ]);

        Estoque::create([
            'produto_id' => 10, // Sorvete de Chocolate
            'local_estoque_id' => 3, // Recepção
            'quantidade' => 70,
        ]);
    }
}