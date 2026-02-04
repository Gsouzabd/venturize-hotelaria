<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Despesa;
use App\Models\Usuario;
use App\Models\DespesaCategoria;
use App\Models\CategoriaDespesa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DespesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar categorias de despesas
        $categorias = [
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Alimentação para Venda'],
                ['descricao' => 'Alimentos destinados à venda', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Alimentação para Consumo Interno'],
                ['descricao' => 'Alimentos para funcionários e café da manhã', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Material de Limpeza'],
                ['descricao' => 'Produtos de limpeza e higiene', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Material Descartável'],
                ['descricao' => 'Copos, pratos, talheres descartáveis', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Manutenção'],
                ['descricao' => 'Serviços e materiais de manutenção', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Energia Elétrica'],
                ['descricao' => 'Contas de energia elétrica', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Água e Esgoto'],
                ['descricao' => 'Contas de água e esgoto', 'fl_ativo' => true]
            ),
            CategoriaDespesa::firstOrCreate(
                ['nome' => 'Internet e Telefone'],
                ['descricao' => 'Serviços de internet e telefonia', 'fl_ativo' => true]
            ),
        ];

        // Buscar um usuário para associar às despesas
        $usuario = Usuario::first();
        if (!$usuario) {
            $this->command->warn('Nenhum usuário encontrado. Criando despesas sem usuário.');
        }

        // Período de novembro de 2025
        $dataInicial = Carbon::create(2025, 11, 1);
        $dataFinal = Carbon::create(2025, 11, 30);

        // Array de despesas de exemplo
        $despesas = [
            [
                'numero_nota_fiscal' => 'NF001/2025',
                'descricao' => 'Compra de alimentos para o restaurante - Carnes e verduras',
                'data' => Carbon::create(2025, 11, 5),
                'valor_total' => 2500.00,
                'observacoes' => 'Compra semanal de alimentos',
                'rateios' => [
                    ['categoria' => 'Alimentação para Venda', 'valor' => 2000.00],
                    ['categoria' => 'Alimentação para Consumo Interno', 'valor' => 500.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF002/2025',
                'descricao' => 'Material de limpeza e produtos de higiene',
                'data' => Carbon::create(2025, 11, 8),
                'valor_total' => 450.00,
                'observacoes' => 'Detergentes, desinfetantes e papel higiênico',
                'rateios' => [
                    ['categoria' => 'Material de Limpeza', 'valor' => 450.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF003/2025',
                'descricao' => 'Compra de material descartável e embalagens',
                'data' => Carbon::create(2025, 11, 10),
                'valor_total' => 320.00,
                'observacoes' => 'Copos, pratos e talheres descartáveis',
                'rateios' => [
                    ['categoria' => 'Material Descartável', 'valor' => 320.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF004/2025',
                'descricao' => 'Manutenção do sistema de ar condicionado',
                'data' => Carbon::create(2025, 11, 12),
                'valor_total' => 850.00,
                'observacoes' => 'Limpeza e manutenção preventiva',
                'rateios' => [
                    ['categoria' => 'Manutenção', 'valor' => 850.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF005/2025',
                'descricao' => 'Compra de alimentos diversos - Rateio entre categorias',
                'data' => Carbon::create(2025, 11, 15),
                'valor_total' => 1800.00,
                'observacoes' => 'Compra mista de alimentos',
                'rateios' => [
                    ['categoria' => 'Alimentação para Venda', 'valor' => 1200.00],
                    ['categoria' => 'Alimentação para Consumo Interno', 'valor' => 600.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF006/2025',
                'descricao' => 'Conta de energia elétrica - Novembro 2025',
                'data' => Carbon::create(2025, 11, 18),
                'valor_total' => 1200.00,
                'observacoes' => 'Conta mensal de energia',
                'rateios' => [
                    ['categoria' => 'Energia Elétrica', 'valor' => 1200.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF007/2025',
                'descricao' => 'Conta de água e esgoto - Novembro 2025',
                'data' => Carbon::create(2025, 11, 20),
                'valor_total' => 380.00,
                'observacoes' => 'Conta mensal de água',
                'rateios' => [
                    ['categoria' => 'Água e Esgoto', 'valor' => 380.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF008/2025',
                'descricao' => 'Serviços de internet e telefone - Novembro 2025',
                'data' => Carbon::create(2025, 11, 22),
                'valor_total' => 250.00,
                'observacoes' => 'Plano mensal de internet e telefonia',
                'rateios' => [
                    ['categoria' => 'Internet e Telefone', 'valor' => 250.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF009/2025',
                'descricao' => 'Compra de alimentos e material de limpeza',
                'data' => Carbon::create(2025, 11, 25),
                'valor_total' => 950.00,
                'observacoes' => 'Compra mista com rateio',
                'rateios' => [
                    ['categoria' => 'Alimentação para Venda', 'valor' => 600.00],
                    ['categoria' => 'Material de Limpeza', 'valor' => 350.00],
                ]
            ],
            [
                'numero_nota_fiscal' => 'NF010/2025',
                'descricao' => 'Manutenção de equipamentos da cozinha',
                'data' => Carbon::create(2025, 11, 28),
                'valor_total' => 650.00,
                'observacoes' => 'Reparo de geladeira e fogão',
                'rateios' => [
                    ['categoria' => 'Manutenção', 'valor' => 650.00],
                ]
            ],
        ];

        // Criar despesas
        foreach ($despesas as $despesaData) {
            $despesa = Despesa::create([
                'numero_nota_fiscal' => $despesaData['numero_nota_fiscal'],
                'descricao' => $despesaData['descricao'],
                'data' => $despesaData['data'],
                'valor_total' => $despesaData['valor_total'],
                'observacoes' => $despesaData['observacoes'],
                'usuario_id' => $usuario ? $usuario->id : null,
            ]);

            // Criar rateios
            foreach ($despesaData['rateios'] as $rateio) {
                $categoria = collect($categorias)->firstWhere('nome', $rateio['categoria']);
                
                if ($categoria) {
                    DespesaCategoria::create([
                        'despesa_id' => $despesa->id,
                        'categoria_despesa_id' => $categoria->id,
                        'valor' => $rateio['valor'],
                        'observacoes' => null,
                    ]);
                }
            }
        }

        $this->command->info('Despesas e categorias criadas com sucesso para novembro de 2025!');
    }
}

