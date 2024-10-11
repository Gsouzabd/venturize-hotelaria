<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LocalEstoque;

class LocaisEstoqueTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LocalEstoque::create(['nome' => 'Cozinha']);
        LocalEstoque::create(['nome' => 'Bar']);
        LocalEstoque::create(['nome' => 'Recepção']);
        LocalEstoque::create(['nome' => 'Apartamento']);
    }
}
