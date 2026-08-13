<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Database\Seeder;

class ServicosSeeder extends Seeder
{
    /**
     * Cria a categoria "Serviços" e os produtos de serviço padrão usados na aba
     * "Serviços" do pedido/consumo do módulo Bar (mesa e apartamento).
     *
     * Idempotente: usa firstOrCreate por nome/descrição, então pode ser rodado
     * novamente sem duplicar registros.
     *
     * ATENÇÃO: os preços abaixo são placeholders (0.00) e DEVEM ser ajustados
     * pelo usuário na tela de produtos (Admin > Produtos) antes de usar em produção.
     */
    public function run(): void
    {
        $categoria = Categoria::firstOrCreate(
            ['nome' => 'Serviços'],
            ['descricao' => 'Serviços avulsos cobrados no pedido/consumo (sem controle de estoque)']
        );

        $servicos = [
            'Café da manhã extra',
            'Impressão folha A4',
            'Hora extra adicional hospedagem',
            'Jantar extra evento',
            'Almoço extra evento',
            'Extra adulto quarto',
        ];

        foreach ($servicos as $indice => $descricao) {
            Produto::firstOrCreate(
                [
                    'descricao' => $descricao,
                    'categoria_produto' => $categoria->id,
                ],
                [
                    'valor_unitario' => 0.00,
                    'preco_custo' => 0.00,
                    'preco_venda' => 0.00, // placeholder — ajustar valor real na tela de produtos
                    'codigo_barras_produto' => null,
                    'codigo_interno' => 'SERV'.str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT),
                    'impressora' => null,
                    'unidade' => 'UN',
                    'ativo' => 1,
                    'criado_por' => 'Admin',
                    'complemento' => '',
                    'produto_servico' => 'servico',
                ]
            );
        }
    }
}
