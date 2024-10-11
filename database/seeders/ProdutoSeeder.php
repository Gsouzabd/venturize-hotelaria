<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;

class ProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = array_keys(Produto::CATEGORIAS);
        $unidades = array_keys(Produto::UNIDADES);
        $impressoras = Produto::IMPRESSORA;

        $produtos = [
            [
                'descricao' => 'Coca-Cola 350ml',
                'valor_unitario' => 3.50,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010015',
                'codigo_interno' => '001',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Pepsi 350ml',
                'valor_unitario' => 3.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010022',
                'codigo_interno' => '002',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Água Mineral 500ml',
                'valor_unitario' => 2.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010039',
                'codigo_interno' => '003',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Suco de Laranja 1L',
                'valor_unitario' => 5.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010046',
                'codigo_interno' => '004',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Cerveja 350ml',
                'valor_unitario' => 4.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010053',
                'codigo_interno' => '005',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Hambúrguer',
                'valor_unitario' => 10.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010060',
                'codigo_interno' => '006',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Pizza Margherita',
                'valor_unitario' => 25.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010077',
                'codigo_interno' => '007',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Salada Caesar',
                'valor_unitario' => 15.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010084',
                'codigo_interno' => '008',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Batata Frita',
                'valor_unitario' => 8.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010091',
                'codigo_interno' => '009',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
            [
                'descricao' => 'Sorvete de Chocolate',
                'valor_unitario' => 7.00,
                'categoria_produto' => $categorias[array_rand($categorias)],
                'codigo_barras_produto' => '7894900010107',
                'codigo_interno' => '010',
                'impressora' => $impressoras[array_rand($impressoras)],
                'unidade' => $unidades[array_rand($unidades)],
                'ativo' => 1,
                'criado_por' => 'Admin',
                'complemento' => '',
                'produto_servico' => 'produto',
            ],
        ];

        foreach ($produtos as $produto) {
            Produto::create($produto);
        }
    }
}