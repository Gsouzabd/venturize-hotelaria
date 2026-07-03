<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Importa os saldos de estoque do Bitz (relatórios de 03/07/2026) e cria a
 * hierarquia de locais de estoque definida pela cliente:
 * Cozinha (Dispensa, Freezer, Geladeira), Almoxarifado (Animal, Equipamento,
 * Escritório, Jogo, Limpeza, Manutenção, Maquinário, Pintura, Piscina,
 * Refrigeração, Utensílio de Cozinha), Lavanderia (Descartável) e
 * Inventário (Decoração Dia dos Namorados, Decoração São João).
 */
return new class extends Migration
{
    // Local pai => sub-locais (folhas que recebem estoque)
    private const LOCAIS = [
        'Cozinha' => ['Dispensa', 'Freezer', 'Geladeira'],
        'Almoxarifado' => ['Animal', 'Equipamento', 'Escritório', 'Jogo', 'Limpeza', 'Manutenção', 'Maquinário', 'Pintura', 'Piscina', 'Refrigeração', 'Utensílio de Cozinha'],
        'Lavanderia' => ['Descartável'],
        'Inventário' => ['Decoração Dia dos Namorados', 'Decoração São João'],
    ];

    // Categoria do relatório Bitz => nome do sub-local
    private const CATEGORIA_LOCAL = [
        'DISPENSA' => 'Dispensa',
        'FREEZER' => 'Freezer',
        'GELADEIRA' => 'Geladeira',
        'ANIMAIS' => 'Animal',
        'EQUIPAMENTO' => 'Equipamento',
        'ESCRITORIO' => 'Escritório',
        'JOGOS' => 'Jogo',
        'LIMPEZA' => 'Limpeza',
        'MANUTENÇÃO' => 'Manutenção',
        'MAQUINARIO' => 'Maquinário',
        'PINTURA' => 'Pintura',
        'PISCINA' => 'Piscina',
        'REFRIGERAÇÃO' => 'Refrigeração',
        'UTENSILIOS COZINHA' => 'Utensílio de Cozinha',
        'DESCARTAVEL' => 'Descartável',
        'DECORAÇÃO DIA DOS NAMORADOS' => 'Decoração Dia dos Namorados',
        'DECORAÇÃO SÃO JOÃO' => 'Decoração São João',
    ];

    public function up(): void
    {
        $localIds = $this->criarLocais();

        foreach ($this->saldos() as [$codigo, $nome, $categoria, $unidade, $max, $min, $saldo, $custo, $venda]) {
            $localId = $localIds[self::CATEGORIA_LOCAL[$categoria]];

            $produto = DB::table('produtos')->where('codigo_interno', (string) $codigo)->first();

            if ($produto) {
                DB::table('produtos')->where('id', $produto->id)->update([
                    'estoque_minimo' => $min,
                    'estoque_maximo' => $max,
                    'preco_custo' => $custo,
                    'preco_venda' => $produto->preco_venda ?: $venda,
                    'updated_at' => now(),
                ]);
                $produtoId = $produto->id;
            } else {
                $produtoId = DB::table('produtos')->insertGetId([
                    'descricao' => $nome,
                    'preco_venda' => $venda,
                    'preco_custo' => $custo,
                    'valor_unitario' => $venda,
                    'categoria_produto' => (string) $this->categoriaId($categoria),
                    'codigo_interno' => (string) $codigo,
                    'unidade' => $unidade,
                    'ativo' => 1,
                    'estoque_minimo' => $min,
                    'estoque_maximo' => $max,
                    'criado_por' => 'importacao_bitz',
                    'produto_servico' => 'produto',
                    'complemento' => 'importado_bitz_saldo',
                    'impressora' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Saldo do Bitz é a fonte da verdade: remove registros do produto em
            // outros locais para não duplicar o saldo total
            DB::table('estoques')
                ->where('produto_id', $produtoId)
                ->where('local_estoque_id', '!=', $localId)
                ->delete();

            DB::table('estoques')->updateOrInsert(
                ['produto_id' => $produtoId, 'local_estoque_id' => $localId],
                ['quantidade' => $saldo, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function categoriaId(string $nome): int
    {
        $id = DB::table('categorias')->where('nome', $nome)->value('id');

        return $id ?? DB::table('categorias')->insertGetId([
            'nome' => $nome, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** @return array<string,int> nome do sub-local => id */
    private function criarLocais(): array
    {
        $ids = [];

        foreach (self::LOCAIS as $pai => $filhos) {
            $paiId = DB::table('locais_estoque')->where('nome', $pai)->whereNull('parent_id')->value('id');

            if (! $paiId) {
                $paiId = DB::table('locais_estoque')->insertGetId([
                    'nome' => $pai, 'parent_id' => null, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($filhos as $filho) {
                $filhoId = DB::table('locais_estoque')->where('nome', $filho)->where('parent_id', $paiId)->value('id');

                if (! $filhoId) {
                    $filhoId = DB::table('locais_estoque')->insertGetId([
                        'nome' => $filho, 'parent_id' => $paiId, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                $ids[$filho] = $filhoId;
            }
        }

        return $ids;
    }

    public function down(): void
    {
        $subLocais = array_merge(...array_values(self::LOCAIS));

        $localIds = DB::table('locais_estoque')->whereIn('nome', $subLocais)->whereNotNull('parent_id')->pluck('id');

        DB::table('estoques')->whereIn('local_estoque_id', $localIds)->delete();

        DB::table('produtos')->where('complemento', 'importado_bitz_saldo')->delete();

        DB::table('locais_estoque')->whereIn('id', $localIds)->delete();

        // Remove os pais criados por esta migration que ficaram sem filhos (Cozinha é preservada)
        DB::table('locais_estoque')
            ->whereIn('nome', ['Almoxarifado', 'Lavanderia', 'Inventário'])
            ->whereNull('parent_id')
            ->whereNotIn('id', fn ($q) => $q->select('parent_id')->from('locais_estoque as f')->whereNotNull('parent_id'))
            ->whereNotIn('id', fn ($q) => $q->select('local_estoque_id')->from('estoques'))
            ->delete();
    }

    /**
     * Linhas dos relatórios de saldo do Bitz (03/07/2026), deduplicadas.
     * Formato: [codigo, produto, categoria, unidade, estoque_maximo, estoque_minimo, saldo, preco_custo, preco_venda]
     *
     * @return array<int,array{0:int,1:string,2:string,3:string,4:int,5:int,6:int,7:float,8:float}>
     */
    private function saldos(): array
    {
        return [
            [375, 'Pássaro', 'ANIMAIS', 'UN', 60, 30, 109, 1.25, 1.25],
            [376, 'Roedor (400g)', 'ANIMAIS', 'UN', 100, 60, 58, 1.56, 1.56],
            [377, 'Caprino (400g)', 'ANIMAIS', 'UN', 100, 60, 56, 1.56, 1.56],
            [378, 'Sal mineral', 'ANIMAIS', 'UN', 25, 1, 24, 1.05, 1.05],
            [1064, 'Galinha', 'ANIMAIS', 'UN', 100, 60, 53, 1.56, 1.56],
            [1127, 'Pato', 'ANIMAIS', 'UN', 100, 60, 48, 1.56, 1.56],
            [1183, 'PÉTALAS 100UNID', 'DECORAÇÃO DIA DOS NAMORADOS', 'UN', 0, 0, 9, 6.00, 0.00],
            [1184, 'PÉTALAS 100UNID + GRAVATA + FLOR', 'DECORAÇÃO DIA DOS NAMORADOS', 'UN', 0, 0, 13, 6.00, 0.00],
            [1185, 'BALÃO CORAÇÃO', 'DECORAÇÃO DIA DOS NAMORADOS', 'UN', 0, 0, 19, 0.78, 0.00],
            [1186, 'ROSA PEQUENA', 'DECORAÇÃO DIA DOS NAMORADOS', 'UN', 0, 0, 28, 4.00, 0.00],
            [1187, 'ROSA MÉDIA', 'DECORAÇÃO DIA DOS NAMORADOS', 'UN', 0, 0, 28, 6.00, 0.00],
            [942, 'LUMINARIA BALÃO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 14, 6.00, 0.00],
            [943, 'DEC. PORTA CHAPEUZINHO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 26, 6.00, 0.00],
            [944, 'MINI CHAPEUZINHO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 12, 6.00, 0.00],
            [945, 'DIADEMA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [946, 'PRESILHA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 4, 6.00, 0.00],
            [947, 'CHAPEU FRANJA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 3, 6.00, 0.00],
            [948, 'CHAPEU TRADICIONAL', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 20, 6.00, 0.00],
            [949, 'CHAPEU MENOR', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 5, 6.00, 0.00],
            [950, 'BALÃO PLASTICO PP', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 6, 6.00, 0.00],
            [951, 'BALÃO PLASTICO P', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 10, 6.00, 0.00],
            [952, 'BALÃO PLASTICO M BRASIL', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [953, 'BALÃO PLASTICO GG', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [954, 'BALÃO PAPEL', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [955, 'QUADRO DOS SANTOS', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 3, 6.00, 0.00],
            [956, 'GUIRLANDA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [957, 'BONECO DE MESA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [958, 'BANDEIRINHA PLASTICO NOVO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 16, 6.00, 0.00],
            [959, 'BAMBOLE', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 20, 6.00, 0.00],
            [960, 'CORTINA DUPLA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 17, 6.00, 0.00],
            [961, 'BALÃO TECIDO M', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 7, 6.00, 0.00],
            [962, 'QUADRINHO DE CHITA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 12, 6.00, 0.00],
            [963, 'TOALHA MESA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 24, 6.00, 0.00],
            [964, 'ROLADOR', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [965, 'CAMISA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 18, 6.00, 0.00],
            [966, 'BANDEJA SACHE', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [967, 'BALÃO TECIDO GG', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [968, 'BALÃO TECIDO G', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [969, 'BALÃO TECIDO MEIO GG', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [970, 'BOLAIO/LUMINARIA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [971, 'QUADRO SELF', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [972, 'ABANADOR CARINHA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [973, 'TOALHA MESA RESTAURANTE CONJUNTO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [974, 'TOALHA MESA GRANDE', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 5, 6.00, 0.00],
            [975, 'ARUPEMBA GRANDE', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 2, 6.00, 0.00],
            [976, 'FOGUEIRA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [977, 'GRAVATA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 1, 6.00, 0.00],
            [1188, 'TECIDO BARRACA DO BEIJO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 0, 60.56, 0.00],
            [1189, 'TECIDO CASAL LAMPIÃO', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 0, 65.16, 0.00],
            [1190, 'TECIDO FESTA JUNINA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 0, 53.25, 0.00],
            [1191, 'TECIDO BEBIDAS', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 0, 37.40, 0.00],
            [1192, 'TECIDO CAIXA', 'DECORAÇÃO SÃO JOÃO', 'UN', 0, 0, 0, 48.95, 0.00],
            [436, 'Canudo Drink Mexedor - 100', 'DESCARTAVEL', 'UN', 20, 1, 4, 7.00, 7.00],
            [437, 'Canudo Suco Grosso - 100', 'DESCARTAVEL', 'UN', 20, 1, 6, 7.00, 7.00],
            [438, 'Copo Acr drink unid', 'DESCARTAVEL', 'UN', 30, 10, 70, 0.80, 0.80],
            [440, 'Copo Desc. 050ml', 'DESCARTAVEL', 'UN', 25, 1, 38, 2.50, 2.50],
            [441, 'Copo Desc. 080ml', 'DESCARTAVEL', 'UN', 30, 5, 80, 2.50, 2.50],
            [442, 'Copo Desc.150ml', 'DESCARTAVEL', 'UN', 50, 15, 34, 5.00, 5.00],
            [445, 'Copo Desc. 400ml', 'DESCARTAVEL', 'UN', 20, 5, 6, 7.00, 7.00],
            [446, 'Guardanapo', 'DESCARTAVEL', 'UN', 100, 30, 17, 0.80, 0.80],
            [447, 'Mexedor (100)', 'DESCARTAVEL', 'UN', 20, 1, 30, 9.00, 9.00],
            [448, 'Palito- caixa(200)', 'DESCARTAVEL', 'UN', 0, 10, 10, 1.50, 1.50],
            [449, 'Palito-embalado individual (100)', 'DESCARTAVEL', 'UN', 20, 2, 8, 1.40, 1.40],
            [450, 'Prato PF12', 'DESCARTAVEL', 'UN', 100, 5, 0, 2.00, 2.00],
            [451, 'Prato PF15', 'DESCARTAVEL', 'UN', 150, 10, 122, 2.00, 2.00],
            [452, 'Prato PR21', 'DESCARTAVEL', 'UN', 100, 12, 0, 4.00, 4.00],
            [453, 'Ref. colher', 'DESCARTAVEL', 'UN', 20, 1, 3, 7.50, 7.50],
            [454, 'Ref. Faca Master', 'DESCARTAVEL', 'UN', 30, 3, 11, 7.50, 7.50],
            [455, 'Ref. Garfo Master', 'DESCARTAVEL', 'UN', 30, 3, 6, 7.50, 7.50],
            [456, 'Rolo Filme', 'DESCARTAVEL', 'UN', 2, 1, 0, 140.00, 140.00],
            [457, 'Rolo Saco (40x60)', 'DESCARTAVEL', 'UN', 5, 1, 1, 40.00, 40.00],
            [459, 'Rolo Saco (25x35)', 'DESCARTAVEL', 'UN', 2, 1, 0, 32.00, 32.00],
            [460, 'Rolo Saco (20x30)', 'DESCARTAVEL', 'UN', 6, 2, 4, 30.00, 30.00],
            [461, 'Saco Talher', 'DESCARTAVEL', 'UN', 50, 2, 6, 2.00, 2.00],
            [462, 'Sobremesa Colher', 'DESCARTAVEL', 'UN', 10, 2, 11, 2.00, 2.15],
            [463, 'Sobremesa Garfo', 'DESCARTAVEL', 'UN', 20, 1, 32, 2.40, 2.40],
            [464, 'Tolca (10unid.)', 'DESCARTAVEL', 'UN', 120, 5, 0, 3.00, 3.00],
            [611, 'Prato PR15', 'DESCARTAVEL', 'UN', 100, 5, 89, 2.00, 2.00],
            [677, 'Papel manteiga 50X70', 'DESCARTAVEL', 'UN', 800, 20, 650, 0.40, 0.40],
            [730, 'Embalagem', 'DESCARTAVEL', 'UN', 100, 10, 5, 1.00, 1.00],
            [731, 'Clipes (100)', 'DESCARTAVEL', 'UN', 20, 2, 35, 2.20, 2.20],
            [1031, 'Rolo de alumínio', 'DESCARTAVEL', 'UN', 2, 1, 1, 14.95, 14.95],
            [1035, 'Rolo Filme stretch com cabo', 'DESCARTAVEL', 'UN', 2, 1, 7, 5.00, 5.00],
            [1036, 'Mini Colher cristal', 'DESCARTAVEL', 'UN', 10, 2, 2, 8.69, 8.69],
            [1061, 'Guardanapo 32x33', 'DESCARTAVEL', 'UN', 100, 10, 15, 0.80, 0.80],
            [1120, 'Sobremesa tampa Desc. 100ml', 'DESCARTAVEL', 'UN', 50, 1, 1, 5.00, 5.00],
            [1121, 'Sobremesa pote Desc. 100ml', 'DESCARTAVEL', 'UN', 30, 1, 1, 5.00, 5.00],
            [1144, 'Copo Desc. 300ml', 'DESCARTAVEL', 'UN', 20, 1, 2, 10.50, 10.50],
            [1173, 'Copo Bio Nescafé unid', 'DESCARTAVEL', 'UN', 400, 50, 250, 0.35, 0.35],
            [299, '* Acúcar - Kg', 'DISPENSA', 'UN', 45, 22, 82, 4.00, 4.00],
            [300, '- Adoçante', 'DISPENSA', 'UN', 3, 1, 3, 4.00, 4.00],
            [302, 'Amido milho - 200g', 'DISPENSA', 'UN', 4, 1, 30, 4.00, 4.00],
            [303, '* Arroz', 'DISPENSA', 'UN', 15, 10, 11, 6.00, 6.00],
            [305, 'Aveia - 170g', 'DISPENSA', 'UN', 15, 3, 22, 3.80, 3.80],
            [306, '- Azeite - garrafa', 'DISPENSA', 'UN', 1, 1, 1, 21.00, 21.00],
            [308, 'B. Cream Cracker', 'DISPENSA', 'UN', 15, 3, 14, 4.20, 4.20],
            [309, 'B. Maisena', 'DISPENSA', 'UN', 5, 1, 13, 4.50, 4.50],
            [310, 'B. Wafer', 'DISPENSA', 'UN', 10, 2, 12, 1.30, 1.30],
            [311, '* Café', 'DISPENSA', 'UN', 35, 14, 30, 15.99, 15.99],
            [312, '- Catchup', 'DISPENSA', 'UN', 5, 1, 13, 6.00, 6.00],
            [313, 'Chá Camomila', 'DISPENSA', 'UN', 3, 1, 2, 4.50, 4.50],
            [314, 'Conf- Coco ralado', 'DISPENSA', 'UN', 1, 1, 1, 1.50, 1.50],
            [316, 'Conf- Marshmallow', 'DISPENSA', 'UN', 1, 1, 1, 12.50, 12.50],
            [317, 'Conf- Paçoca', 'DISPENSA', 'UN', 15, 3, 46, 1.00, 1.00],
            [318, '- Extrato Tomate - 340g', 'DISPENSA', 'UN', 3, 1, 22, 3.00, 3.00],
            [319, '* F. Mandioca', 'DISPENSA', 'UN', 10, 2, 4, 4.70, 4.70],
            [320, '* F. Trigo c/ ferm', 'DISPENSA', 'UN', 15, 6, 12, 5.35, 5.35],
            [321, '* F. Trigo s/ ferm', 'DISPENSA', 'UN', 15, 6, 9, 5.00, 5.00],
            [322, '* Feijão de corda', 'DISPENSA', 'UN', 10, 4, 12, 8.00, 8.00],
            [323, '* Feijão Carioca', 'DISPENSA', 'UN', 20, 0, 5, 9.00, 9.00],
            [324, '* Feijão Preto', 'DISPENSA', 'UN', 10, 3, 12, 10.00, 10.00],
            [325, '* Flocão', 'DISPENSA', 'UN', 60, 24, 99, 1.90, 1.90],
            [326, 'Granola - 250g', 'DISPENSA', 'UN', 1, 1, 36, 11.00, 11.00],
            [327, '/Lac. Leite em pó', 'DISPENSA', 'UN', 15, 3, 32, 6.40, 6.40],
            [329, '- M. Barbucue- 500ml', 'DISPENSA', 'UN', 2, 1, 2, 7.00, 7.00],
            [331, '- M. Inglês - 1lt', 'DISPENSA', 'UN', 2, 1, 2, 10.00, 10.00],
            [333, '- M. Shoyo - 1lt', 'DISPENSA', 'UN', 2, 1, 3, 6.50, 6.50],
            [334, '* Macarrão - 400g', 'DISPENSA', 'UN', 75, 6, 28, 2.90, 2.90],
            [335, '- Maionese - kg', 'DISPENSA', 'UN', 10, 2, 5, 12.00, 12.00],
            [336, 'Milho', 'DISPENSA', 'UN', 10, 2, 24, 4.00, 4.00],
            [337, '- M. Mostarda - 500ml', 'DISPENSA', 'UN', 2, 1, 2, 10.00, 10.00],
            [338, 'Munguzá - 500g', 'DISPENSA', 'UN', 5, 1, 13, 2.50, 2.50],
            [339, 'Nescau - 400g', 'DISPENSA', 'UN', 5, 1, 6, 7.00, 7.00],
            [340, '- Oleo - garrafa', 'DISPENSA', 'UN', 5, 1, 20, 11.00, 11.00],
            [341, 'Queijo ralado- 100g', 'DISPENSA', 'UN', 15, 3, 40, 6.00, 6.00],
            [343, 'Sachê-Açúcar (100)', 'DISPENSA', 'UN', 20, 4, 7, 6.00, 6.00],
            [344, 'Sachê- Geléia(12)', 'DISPENSA', 'UN', 5, 1, 15, 13.00, 13.00],
            [345, 'Sachê-Mel(12)', 'DISPENSA', 'UN', 5, 1, 15, 14.00, 14.00],
            [346, '* Sal - kg', 'DISPENSA', 'UN', 15, 3, 5, 1.00, 1.00],
            [349, '- Vinagre - 500ml', 'DISPENSA', 'UN', 10, 2, 24, 1.70, 1.70],
            [361, 'Margarina - 1kg', 'DISPENSA', 'UN', 20, 4, 38, 10.00, 10.00],
            [369, '/Lac. Achocolatado', 'DISPENSA', 'UN', 150, 6, 12, 4.90, 4.90],
            [370, '/Lac. Creme de Leite', 'DISPENSA', 'UN', 150, 5, 25, 2.00, 2.00],
            [371, '/Lac. Leite Condensado', 'DISPENSA', 'UN', 150, 5, 19, 5.80, 5.80],
            [372, '/Lac. Leite de Caixa', 'DISPENSA', 'UN', 150, 20, 74, 5.20, 5.20],
            [373, 'xRefrigerante', 'DISPENSA', 'UN', 0, 0, 25, 10.00, 10.00],
            [374, 'xGordura vegetal', 'DISPENSA', 'UN', 3, 1, 1, 220.00, 220.00],
            [610, '- Vinho carreteiro 1lt', 'DISPENSA', 'UN', 1, 1, 2, 10.00, 10.00],
            [630, 'Sachê-Sal (100)', 'DISPENSA', 'UN', 20, 1, 15, 1.60, 1.60],
            [705, 'Sachê- Pimenta(50)', 'DISPENSA', 'UN', 5, 2, 7, 8.00, 8.00],
            [726, 'Chá Boldo', 'DISPENSA', 'UN', 3, 1, 5, 4.50, 4.50],
            [727, 'Chá Cidreira', 'DISPENSA', 'UN', 3, 1, 5, 4.50, 4.50],
            [728, 'Chá Erva Doce', 'DISPENSA', 'UN', 3, 1, 7, 4.50, 4.50],
            [745, 'Sardinha', 'DISPENSA', 'UN', 10, 2, 9, 4.00, 4.00],
            [894, '* Farinha de rosca 500g', 'DISPENSA', 'UN', 15, 3, 6, 6.00, 6.00],
            [981, 'Chá Hortelã', 'DISPENSA', 'UN', 3, 1, 7, 4.50, 4.50],
            [1040, '- Azeitona balde', 'DISPENSA', 'UN', 1, 1, 2, 50.00, 50.00],
            [1062, '* Farinha panko 500g', 'DISPENSA', 'UN', 15, 3, 3, 16.99, 16.99],
            [1135, 'Chocolate ao leite', 'DISPENSA', 'UN', 3, 1, 1, 40.00, 40.00],
            [1136, 'Chocolate meio amargo', 'DISPENSA', 'UN', 3, 1, 1, 40.00, 40.00],
            [1137, 'Picles', 'DISPENSA', 'UN', 15, 1, 1, 6.00, 6.00],
            [1180, 'Atum ralado lata', 'DISPENSA', 'UN', 10, 2, 7, 7.39, 7.39],
            [430, 'Borrifador', 'EQUIPAMENTO', 'UN', 3, 1, 0, 8.00, 8.00],
            [431, 'Lixeira Cesto', 'EQUIPAMENTO', 'UN', 10, 1, 3, 10.00, 10.00],
            [432, 'Porta papel higienico', 'EQUIPAMENTO', 'UN', 2, 1, 4, 35.00, 35.00],
            [433, 'Porta sabonete', 'EQUIPAMENTO', 'UN', 1, 1, 3, 35.00, 35.00],
            [434, 'Porta papel toalha', 'EQUIPAMENTO', 'UN', 2, 1, 1, 35.00, 35.00],
            [435, 'Reservatório sabonete', 'EQUIPAMENTO', 'UN', 1, 1, 3, 10.00, 10.00],
            [543, 'Espanador', 'EQUIPAMENTO', 'UN', 1, 1, 0, 10.00, 10.00],
            [732, 'Tapete', 'EQUIPAMENTO', 'UN', 10, 1, 3, 10.00, 10.00],
            [921, 'Lixeira com Pedal', 'EQUIPAMENTO', 'UN', 10, 1, 5, 20.00, 20.00],
            [1085, 'Cabide', 'EQUIPAMENTO', 'UN', 10, 1, 30, 1.10, 1.10],
            [396, 'Lacre', 'ESCRITORIO', 'UN', 10, 2, 19, 15.80, 15.80],
            [398, 'Papel A4', 'ESCRITORIO', 'UN', 10, 4, 8, 26.00, 26.00],
            [399, 'Pulseira', 'ESCRITORIO', 'UN', 10, 1, 8, 106.39, 106.39],
            [421, 'Rede ecoparque', 'ESCRITORIO', 'UN', 0, 0, 2, 63.00, 63.00],
            [533, 'Rede funcionário', 'ESCRITORIO', 'UN', 10, 0, 10, 15.00, 15.00],
            [538, 'Bobina impressora', 'ESCRITORIO', 'UN', 20, 10, 114, 4.00, 4.00],
            [539, 'Caneta Bic', 'ESCRITORIO', 'UN', 4, 1, 62, 1.50, 1.50],
            [540, 'Cartão Fidel- 50', 'ESCRITORIO', 'UN', 10, 2, 13, 12.00, 12.00],
            [541, 'Controle Ar', 'ESCRITORIO', 'UN', 2, 1, 1, 25.00, 25.00],
            [544, 'Etiqueta (1000)', 'ESCRITORIO', 'UN', 10, 0, 4, 45.00, 45.00],
            [545, 'Fita Durex fino', 'ESCRITORIO', 'UN', 2, 1, 3, 2.00, 2.00],
            [546, 'Fita Durex gross', 'ESCRITORIO', 'UN', 2, 1, 3, 5.00, 5.00],
            [548, 'Grampo', 'ESCRITORIO', 'UN', 5, 1, 2, 5.00, 5.00],
            [549, 'Guarda- chuva', 'ESCRITORIO', 'UN', 1, 1, 2, 30.00, 30.00],
            [550, 'Isqueiro', 'ESCRITORIO', 'UN', 12, 2, 5, 4.00, 4.00],
            [666, 'Caneta Piloto', 'ESCRITORIO', 'UN', 4, 1, 8, 2.00, 2.00],
            [1194, 'Pilha palito', 'ESCRITORIO', 'UN', 4, 1, 12, 2.00, 2.00],
            [1195, 'Cola quente bastão', 'ESCRITORIO', 'UN', 4, 1, 30, 2.00, 2.00],
            [359, 'Ling. fininha', 'FREEZER', 'UN', 20, 20, 161, 3.50, 3.50],
            [380, 'Carne isca kg', 'FREEZER', 'UN', 20, 3, 10, 30.00, 30.00],
            [381, 'Carne picada kg', 'FREEZER', 'UN', 20, 3, 7, 30.00, 30.00],
            [382, 'Carne cupim bola 3,5kg', 'FREEZER', 'UN', 20, 0, 2, 115.00, 115.00],
            [384, 'Frango coxa 1,7kg', 'FREEZER', 'UN', 40, 0, 4, 17.00, 17.00],
            [385, 'Frango int - unid', 'FREEZER', 'UN', 20, 3, 7, 15.00, 15.00],
            [386, 'Frango peito kg', 'FREEZER', 'UN', 30, 5, 24, 15.00, 15.00],
            [387, 'Ling. salsicha- 500g', 'FREEZER', 'UN', 50, 10, 42, 6.50, 6.50],
            [390, 'Polpa acerola', 'FREEZER', 'UN', 100, 8, 26, 0.80, 0.80],
            [391, 'Polpa cajá', 'FREEZER', 'UN', 100, 8, 18, 1.10, 1.10],
            [392, 'Polpa cajú', 'FREEZER', 'UN', 100, 8, 8, 0.60, 0.60],
            [393, 'Polpa goiaba', 'FREEZER', 'UN', 100, 8, 40, 0.60, 0.60],
            [394, 'Polpa graviola', 'FREEZER', 'UN', 100, 8, 26, 1.10, 1.10],
            [395, 'Polpa manga', 'FREEZER', 'UN', 100, 8, 34, 0.60, 0.60],
            [559, 'Macaxeira kg', 'FREEZER', 'UN', 50, 5, 25, 5.00, 5.00],
            [561, 'Charque 500g', 'FREEZER', 'UN', 30, 5, 8, 18.00, 18.00],
            [562, 'Molho de tomate KG', 'FREEZER', 'UN', 20, 3, 0, 5.00, 5.00],
            [625, 'Peixe Anchova 5kg', 'FREEZER', 'UN', 5, 1, 3, 50.00, 50.00],
            [629, 'Alho 200g', 'FREEZER', 'UN', 20, 2, 50, 5.00, 5.00],
            [676, 'Carne para feijão - 200g', 'FREEZER', 'UN', 0, 0, 65, 6.00, 6.00],
            [744, 'Carne moida 500g', 'FREEZER', 'UN', 20, 3, 14, 15.00, 15.00],
            [986, 'Ling. Toscana', 'FREEZER', 'UN', 20, 6, 23, 7.50, 7.50],
            [1115, 'File de peixe', 'FREEZER', 'UN', 20, 3, 10, 30.00, 30.00],
            [350, 'Bisnaga Chedar', 'GELADEIRA', 'UN', 1, 1, 1, 20.00, 20.00],
            [351, 'Bisnaga Requeijão', 'GELADEIRA', 'UN', 1, 1, 2, 17.00, 17.00],
            [352, 'Fat. Mortadela- 200g', 'GELADEIRA', 'UN', 20, 6, 72, 5.00, 5.00],
            [353, 'Fat. mussarela - kg', 'GELADEIRA', 'UN', 20, 3, 55, 57.00, 57.00],
            [354, 'Fat.Presunto- 200g', 'GELADEIRA', 'UN', 20, 15, 50, 6.00, 6.00],
            [356, 'Iogurte saco', 'GELADEIRA', 'UN', 24, 12, 19, 3.90, 3.90],
            [360, 'Manteiguinha - (12)', 'GELADEIRA', 'UN', 20, 5, 8, 10.00, 10.00],
            [367, 'Requeijão', 'GELADEIRA', 'UN', 12, 4, 8, 5.70, 5.70],
            [1023, 'Manteiga 200g', 'GELADEIRA', 'UN', 12, 2, 10, 5.70, 5.70],
            [400, 'Baralho (par)', 'JOGOS', 'UN', 1, 0, 2, 10.00, 10.00],
            [402, 'Bola ping pong', 'JOGOS', 'UN', 15, 1, 3, 2.00, 2.00],
            [403, 'Bola toto', 'JOGOS', 'UN', 2, 1, 2, 5.00, 5.00],
            [405, 'Raquete ping-pong', 'JOGOS', 'UN', 2, 1, 2, 15.00, 15.00],
            [406, 'Boneco totó (22)', 'JOGOS', 'UN', 1, 0, 1, 60.00, 60.00],
            [663, 'Bola de Futebol', 'JOGOS', 'UN', 2, 1, 1, 100.00, 100.00],
            [664, 'Bola de Vôlei', 'JOGOS', 'UN', 2, 1, 1, 153.00, 153.00],
            [1139, 'Rede de Ping-Pong', 'JOGOS', 'UN', 1, 1, 1, 20.00, 20.00],
            [1174, 'Bomba de encher bola', 'JOGOS', 'UN', 1, 1, 1, 30.00, 30.00],
            [479, 'Alcool Acendedor 5L', 'LIMPEZA', 'UN', 2, 1, 1, 55.00, 55.00],
            [480, 'Alcool líquido 70°-lt', 'LIMPEZA', 'UN', 24, 5, 18, 5.40, 5.40],
            [481, 'Amaciante 3L', 'LIMPEZA', 'UN', 2, 1, 0, 60.00, 60.00],
            [482, 'Aromatizante Coala', 'LIMPEZA', 'UN', 12, 1, 6, 8.00, 8.00],
            [483, 'Aromatizante Bom ar', 'LIMPEZA', 'UN', 2, 1, 5, 13.00, 13.00],
            [484, 'Desinfetante - L', 'LIMPEZA', 'UN', 20, 12, 80, 5.50, 5.50],
            [485, 'Detergente - 500ml', 'LIMPEZA', 'UN', 60, 12, 56, 1.50, 1.50],
            [486, 'Esponja dupla face', 'LIMPEZA', 'UN', 100, 8, 66, 0.60, 0.60],
            [488, 'Hipoclorito(cloro)1L', 'LIMPEZA', 'UN', 8, 4, 55, 4.00, 4.00],
            [489, 'Sache Sabonete - 50', 'LIMPEZA', 'UN', 20, 6, 8, 20.00, 20.00],
            [490, 'Sache Shampoo - 50', 'LIMPEZA', 'UN', 20, 4, 3, 24.00, 24.00],
            [491, 'Lustra Móveis - Cremoso', 'LIMPEZA', 'UN', 3, 2, 11, 5.00, 5.00],
            [492, 'Luva látex amarela M', 'LIMPEZA', 'UN', 6, 2, 3, 5.20, 5.20],
            [493, 'Luva plástica', 'LIMPEZA', 'UN', 75, 0, 61, 6.50, 6.50],
            [494, 'Naftalina', 'LIMPEZA', 'UN', 60, 8, 1, 4.30, 4.30],
            [495, 'Lustra Móveis - Óleo', 'LIMPEZA', 'UN', 3, 2, 15, 11.20, 11.20],
            [496, 'Esponja de aço inox', 'LIMPEZA', 'UN', 15, 2, 11, 5.50, 5.50],
            [497, 'Perfex', 'LIMPEZA', 'UN', 1500, 50, 670, 0.22, 0.22],
            [499, 'Pretinho - 500ml', 'LIMPEZA', 'UN', 10, 1, 11, 3.00, 3.00],
            [500, 'Sabao pedra - unid', 'LIMPEZA', 'UN', 100, 35, 22, 2.40, 2.40],
            [501, 'Sabao po', 'LIMPEZA', 'UN', 100, 26, 88, 3.00, 3.00],
            [502, 'Sabonete Liquido-5l', 'LIMPEZA', 'UN', 5, 1, 1, 20.00, 20.00],
            [503, 'Saco lixo 100L -unid', 'LIMPEZA', 'UN', 500, 66, 173, 0.35, 0.35],
            [504, 'Saco lixo 15L- pac', 'LIMPEZA', 'UN', 20, 6, 1, 7.00, 7.00],
            [505, 'Inseticida', 'LIMPEZA', 'UN', 6, 1, 5, 10.00, 10.00],
            [506, 'Silicone - 500ml', 'LIMPEZA', 'UN', 10, 1, 2, 10.00, 10.00],
            [507, 'z Papel Higiênico - rolo', 'LIMPEZA', 'UN', 480, 240, 92, 1.00, 1.00],
            [508, 'z Papel Higiênico Big', 'LIMPEZA', 'UN', 60, 12, 4, 8.50, 8.50],
            [509, 'z Papel Toalha-pct', 'LIMPEZA', 'UN', 110, 40, 149, 4.00, 4.00],
            [510, 'VAR - Cabo', 'LIMPEZA', 'UN', 24, 2, 26, 1.00, 1.00],
            [512, 'VAR - Ciscador plástico', 'LIMPEZA', 'UN', 3, 0, 2, 15.00, 15.00],
            [514, 'VAR - Pá', 'LIMPEZA', 'UN', 12, 1, 8, 1.20, 1.20],
            [515, 'VAR - Rodo', 'LIMPEZA', 'UN', 12, 1, 4, 1.50, 1.50],
            [516, 'VAR - Vassoura', 'LIMPEZA', 'UN', 24, 2, 1, 3.50, 3.50],
            [517, 'VAR - Vassourão', 'LIMPEZA', 'UN', 1, 0, 1, 3.50, 3.50],
            [584, 'VAR - MOP', 'LIMPEZA', 'UN', 2, 0, 2, 15.00, 15.00],
            [656, 'Alcool Combustível 500ml', 'LIMPEZA', 'UN', 0, 0, 6, 13.90, 13.90],
            [682, 'VAR - Escova sapato', 'LIMPEZA', 'UN', 5, 1, 3, 5.00, 5.00],
            [683, 'VAR - Escova pia', 'LIMPEZA', 'UN', 2, 1, 3, 6.00, 6.00],
            [684, 'Alvejante', 'LIMPEZA', 'UN', 0, 0, 1, 10.00, 10.00],
            [1033, 'Luva látex verde G', 'LIMPEZA', 'UN', 6, 2, 5, 5.20, 5.20],
            [1130, 'VAR - Escova mamadeira', 'LIMPEZA', 'UN', 2, 1, 1, 6.00, 6.00],
            [407, 'Sapato Bota 7 léguas', 'MANUTENÇÃO', 'UN', 10, 1, 1, 40.00, 40.00],
            [410, 'Capa chuva', 'MANUTENÇÃO', 'UN', 5, 2, 2, 10.00, 10.00],
            [411, 'Chuveiro', 'MANUTENÇÃO', 'UN', 2, 1, 6, 25.00, 25.00],
            [412, 'Esponja espuma', 'MANUTENÇÃO', 'UN', 0, 0, 2, 55.50, 55.50],
            [417, '*Lâmpada', 'MANUTENÇÃO', 'UN', 50, 10, 90, 5.00, 5.00],
            [418, 'Luva pedreiro', 'MANUTENÇÃO', 'UN', 1, 1, 1, 5.00, 5.00],
            [554, 'Estribo', 'MANUTENÇÃO', 'UN', 1, 0, 1, 40.00, 40.00],
            [685, 'Lápis Carpinteiro', 'MANUTENÇÃO', 'UN', 0, 0, 36, 4.00, 4.00],
            [687, 'Sapato Bota 44', 'MANUTENÇÃO', 'UN', 0, 1, 0, 60.00, 60.00],
            [688, 'Sapato Bota 42', 'MANUTENÇÃO', 'UN', 0, 1, 1, 60.00, 60.00],
            [734, 'Relógio água', 'MANUTENÇÃO', 'UN', 1, 1, 2, 80.00, 80.00],
            [978, '*Lâmpada Amarela', 'MANUTENÇÃO', 'UN', 1, 1, 0, 0.00, 0.00],
            [979, '*Luminária vela', 'MANUTENÇÃO', 'UN', 1, 1, 51, 0.00, 0.00],
            [980, '*Luminária vela boia', 'MANUTENÇÃO', 'UN', 1, 1, 51, 0.00, 0.00],
            [1005, '*Lâmpada Fluorescente', 'MANUTENÇÃO', 'UN', 5, 2, 6, 10.00, 10.00],
            [1018, 'Mascara Azul', 'MANUTENÇÃO', 'UN', 1, 1, 5, 3.00, 3.00],
            [1019, 'Óculos preto', 'MANUTENÇÃO', 'UN', 1, 1, 7, 5.00, 5.00],
            [1128, 'Borracha frigobar pequena', 'MANUTENÇÃO', 'UN', 1, 0, 1, 40.00, 30.00],
            [1129, 'Borracha frigobar grande', 'MANUTENÇÃO', 'UN', 1, 0, 3, 40.00, 30.00],
            [1133, '*Lâmpada Led Fluorescente', 'MANUTENÇÃO', 'UN', 5, 2, 7, 10.00, 10.00],
            [1134, 'Nylon 1,5m', 'MANUTENÇÃO', 'UN', 50, 10, 87, 2.00, 2.00],
            [1193, '*Lâmpada recepção', 'MANUTENÇÃO', 'UN', 50, 4, 9, 5.00, 5.00],
            [987, 'Pulverizador costa', 'MAQUINARIO', 'UN', 3, 1, 1, 8.00, 0.00],
            [988, 'Roçadeira Carrinho', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [989, 'Refrigeração Lava Jato', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [990, 'z Produto Perfume Refrigeração', 'MAQUINARIO', 'UN', 3, 1, 2, 8.00, 8.00],
            [991, 'z Produto Gasolina - 1lt', 'MAQUINARIO', 'UN', 3, 1, 1, 7.00, 7.00],
            [992, 'Cavador Broca', 'MAQUINARIO', 'UN', 3, 1, 2, 0.00, 0.00],
            [993, 'z Produto Óleo Pneumático', 'MAQUINARIO', 'UN', 3, 1, 1, 50.00, 50.00],
            [994, 'Refrigeração Máquina de Vácuo', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [995, 'Roçadeira Combustão', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [996, 'Roçadeira Energia', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [997, 'z Produto Óleo 2T - 20ml', 'MAQUINARIO', 'UN', 75, 1, 48, 1.60, 1.60],
            [1020, 'z Produto Óleo 4T - 1L', 'MAQUINARIO', 'UN', 3, 1, 1, 40.00, 40.00],
            [1088, 'Motoserra Combustão', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1124, 'z Produto Lauril Espuma 5L', 'MAQUINARIO', 'UN', 3, 1, 3, 53.90, 53.90],
            [1145, 'Motoserra Calça', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1146, 'Motoserra Capacete', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1147, 'Motoserra Luvas', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1148, 'Aspirador Lavagem a seco', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1149, 'Canhão Equipamento', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1150, 'Canhão Tambor', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1151, 'Canhão Suporte', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1152, 'Cavador Combustão', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1153, 'Cavador Alongador', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1154, 'Lixadeira Elétrica Pintura', 'MAQUINARIO', 'UN', 3, 1, 1, 0.00, 0.00],
            [1155, 'Esteira Lona', 'MAQUINARIO', 'UN', 3, 1, 1, 50.00, 50.00],
            [1156, 'z Produto Óleo Fluído de freio', 'MAQUINARIO', 'UN', 3, 1, 0, 50.00, 50.00],
            [1157, 'z Produto Óleo Bomba a Vácuo', 'MAQUINARIO', 'UN', 3, 1, 1, 50.00, 50.00],
            [588, 'MASSA ACRÍLICA LATÃO', 'PINTURA', 'UN', 12, 1, 4, 76.78, 76.78],
            [589, 'MASSA CORRIDA LATÃO', 'PINTURA', 'UN', 4, 1, 3, 39.92, 39.92],
            [590, 'TINTA INTERNA (BRANCA) LATÃO', 'PINTURA', 'UN', 4, 1, 5, 87.48, 87.48],
            [591, 'SELADOR LATÃO', 'PINTURA', 'UN', 1, 1, 1, 67.92, 67.92],
            [592, 'THINNER LATÃO', 'PINTURA', 'UN', 2, 1, 2, 292.84, 292.84],
            [594, 'SELA E PINTA (CAMURÇA) LATÃO', 'PINTURA', 'UN', 1, 1, 1, 198.45, 198.45],
            [595, 'SELA E PINTA (TAPACURÁ) LATÃO', 'PINTURA', 'UN', 1, 1, 1, 198.45, 198.45],
            [597, '*VERNIZ (INCOLOR) 1/4', 'PINTURA', 'UN', 6, 1, 6, 89.25, 89.25],
            [598, 'DIALINE SECA RAPIDO (BRANCO FOSCO) GALÃO', 'PINTURA', 'UN', 4, 1, 1, 99.75, 99.75],
            [599, 'DIALINE ANTIFERRUGEM (BRANCO BRILHO) GALÃO', 'PINTURA', 'UN', 4, 1, 1, 110.25, 110.25],
            [600, 'DIALINE ANTIFERRUGEM (MARROM TABACO) GALÃO', 'PINTURA', 'UN', 16, 3, 4, 110.25, 110.25],
            [601, 'ESMALTE ( VERDE LIMÃO 750ml)', 'PINTURA', 'UN', 10, 1, 2, 25.93, 25.93],
            [602, 'ESMALTE ( VERMELHO 750ml)', 'PINTURA', 'UN', 6, 1, 17, 25.93, 25.93],
            [603, 'ESMALTE ( AMAREL0 750ml)', 'PINTURA', 'UN', 10, 1, 6, 25.93, 25.93],
            [604, 'ESMALTE ( AZUL FRANÇA 750ml)', 'PINTURA', 'UN', 10, 1, 7, 25.93, 25.93],
            [605, 'ESMALTE ( PRETO 750ml)', 'PINTURA', 'UN', 10, 1, 5, 25.93, 25.93],
            [606, 'ESMALTE ( LARANJA 750ml)', 'PINTURA', 'UN', 10, 1, 11, 25.93, 25.93],
            [607, '*AUTOMOTIVO (BRANCO) 1/4', 'PINTURA', 'UN', 2, 1, 2, 60.00, 60.00],
            [608, '*COLA CONTATO 1/4', 'PINTURA', 'UN', 6, 1, 0, 35.00, 35.00],
            [622, 'FUNDO PREPARADOR', 'PINTURA', 'UN', 1, 1, 1, 186.27, 186.27],
            [657, 'ESMALTE ( AZUL MAR 750ml )', 'PINTURA', 'UN', 10, 1, 4, 25.93, 25.93],
            [658, 'ESMALTE ( AMARELO TRATOR 750ml )', 'PINTURA', 'UN', 10, 3, 10, 25.93, 29.93],
            [659, 'ESMALTE ( VERDE FOLHA 750ML )', 'PINTURA', 'UN', 10, 1, 6, 25.93, 25.93],
            [1024, '*COLA BRANCA', 'PINTURA', 'UN', 6, 1, 1, 35.00, 35.00],
            [1025, '*AUTOMOTIVO (ENDURECEDOR)', 'PINTURA', 'UN', 2, 1, 2, 26.84, 26.84],
            [1027, 'ESMALTE ( CERAMICA 750ml)', 'PINTURA', 'UN', 10, 3, 7, 25.92, 25.93],
            [1112, '*SELADOR PLÁSTICO', 'PINTURA', 'UN', 6, 1, 1, 35.00, 35.00],
            [1123, '#MANTA TERMICA 10M', 'PINTURA', 'UN', 2, 1, 1, 60.00, 60.00],
            [1126, '#MANTA PREMIER', 'PINTURA', 'UN', 0, 0, 1, 60.00, 60.00],
            [413, 'Filtro', 'PISCINA', 'UN', 1, 0, 1, 50.00, 50.00],
            [465, 'Acido - 5L', 'PISCINA', 'UN', 12, 4, 4, 37.50, 37.50],
            [466, 'Algicida choque', 'PISCINA', 'UN', 3, 2, 8, 36.60, 36.60],
            [467, 'Algicida manutenção', 'PISCINA', 'UN', 4, 4, 2, 21.66, 21.66],
            [468, 'Limpa Borda', 'PISCINA', 'UN', 1, 0, 1, 20.00, 20.00],
            [469, 'Clarificante - lt', 'PISCINA', 'UN', 15, 15, 10, 16.50, 16.50],
            [470, 'Cloro Pastilha (4)', 'PISCINA', 'UN', 1, 0, 4, 12.00, 12.00],
            [471, 'Fita medição', 'PISCINA', 'UN', 1, 1, 1, 40.28, 40.28],
            [472, 'Solução Cloro - A', 'PISCINA', 'UN', 1, 1, 0, 7.50, 7.50],
            [473, 'Solução Indicador', 'PISCINA', 'UN', 1, 1, 1, 7.50, 7.50],
            [474, 'Solução PH - V', 'PISCINA', 'UN', 1, 1, 0, 7.50, 7.50],
            [475, 'Solução Titulante', 'PISCINA', 'UN', 1, 1, 2, 7.50, 7.50],
            [476, 'x Elevador kg', 'PISCINA', 'UN', 25, 24, 4, 13.00, 13.00],
            [477, 'zCl. pó - Dicloro', 'PISCINA', 'UN', 1, 1, 1, 330.00, 330.00],
            [478, 'zCl. pó - Hipoclorito', 'PISCINA', 'UN', 5, 5, 2, 300.00, 300.00],
            [735, 'Gel cubinho', 'PISCINA', 'UN', 1, 0, 8, 5.90, 5.90],
            [627, 'Gás Maçarico', 'REFRIGERAÇÃO', 'UN', 1, 1, 1, 50.00, 50.00],
            [665, 'Fita pvc', 'REFRIGERAÇÃO', 'UN', 10, 1, 5, 10.00, 10.00],
            [680, 'Maçarico', 'REFRIGERAÇÃO', 'UN', 1, 0, 1, 180.00, 180.00],
            [700, 'Desengraxante 50ml', 'REFRIGERAÇÃO', 'UN', 25, 2, 18, 2.50, 2.50],
            [833, 'Gás azul R32', 'REFRIGERAÇÃO', 'UN', 1, 1, 1, 649.29, 649.29],
            [1140, 'Tubo isolante', 'REFRIGERAÇÃO', 'UN', 25, 2, 9, 2.50, 2.50],
            [1141, 'Capacitor', 'REFRIGERAÇÃO', 'UN', 10, 1, 2, 10.00, 10.00],
            [420, 'Pano multiuso', 'UTENSILIOS COZINHA', 'UN', 13, 1, 0, 20.00, 20.00],
            [518, 'Borr panela 7lt', 'UTENSILIOS COZINHA', 'UN', 1, 0, 1, 5.00, 5.00],
            [519, 'Coador', 'UTENSILIOS COZINHA', 'UN', 4, 1, 1, 5.00, 5.00],
            [521, 'Coqueteleira', 'UTENSILIOS COZINHA', 'UN', 2, 0, 2, 105.00, 105.00],
            [522, 'Faca', 'UTENSILIOS COZINHA', 'UN', 1, 1, 1, 2.00, 2.00],
            [523, 'Frigideira', 'UTENSILIOS COZINHA', 'UN', 2, 0, 2, 15.00, 15.00],
            [524, 'Talher Garfo', 'UTENSILIOS COZINHA', 'UN', 30, 6, 90, 2.00, 2.00],
            [525, 'Garrafa café', 'UTENSILIOS COZINHA', 'UN', 3, 1, 3, 60.00, 60.00],
            [526, 'Garrafa leite', 'UTENSILIOS COZINHA', 'UN', 1, 1, 0, 60.00, 60.00],
            [528, 'AA Elet. Liquidificação', 'UTENSILIOS COZINHA', 'UN', 1, 0, 2, 150.00, 150.00],
            [529, 'Pano de prato', 'UTENSILIOS COZINHA', 'UN', 12, 6, 8, 2.00, 2.00],
            [531, 'Pote Papa', 'UTENSILIOS COZINHA', 'UN', 35, 5, 29, 5.00, 5.00],
            [532, 'Ralo inox', 'UTENSILIOS COZINHA', 'UN', 10, 0, 3, 10.00, 10.00],
            [534, 'AA Elet. Sanduicheira', 'UTENSILIOS COZINHA', 'UN', 2, 1, 1, 80.00, 80.00],
            [535, 'Travessa tampa', 'UTENSILIOS COZINHA', 'UN', 4, 0, 1, 35.00, 35.00],
            [536, '* Xícara café', 'UTENSILIOS COZINHA', 'UN', 48, 6, 35, 14.00, 14.00],
            [537, 'Abridor vinho', 'UTENSILIOS COZINHA', 'UN', 1, 0, 4, 13.00, 13.00],
            [557, 'Peneira', 'UTENSILIOS COZINHA', 'UN', 1, 1, 1, 10.00, 10.00],
            [618, '* Taça Paulista (24unid)', 'UTENSILIOS COZINHA', 'UN', 10, 5, 10, 120.00, 120.00],
            [619, 'Jarra', 'UTENSILIOS COZINHA', 'UN', 9, 1, 2, 20.00, 20.00],
            [620, 'Jarrinha de vidro', 'UTENSILIOS COZINHA', 'UN', 3, 1, 2, 15.00, 15.00],
            [628, 'Abridor coco', 'UTENSILIOS COZINHA', 'UN', 2, 1, 2, 15.00, 15.00],
            [661, 'Suporte Refrigerante', 'UTENSILIOS COZINHA', 'UN', 4, 1, 4, 10.00, 10.00],
            [733, 'Garrafa caldinho', 'UTENSILIOS COZINHA', 'UN', 8, 1, 3, 40.00, 40.00],
            [922, 'Talher Colher', 'UTENSILIOS COZINHA', 'UN', 30, 6, 6, 2.00, 2.00],
            [923, 'Pote vidro', 'UTENSILIOS COZINHA', 'UN', 35, 1, 3, 15.00, 15.00],
            [924, 'Pote Tempero', 'UTENSILIOS COZINHA', 'UN', 35, 1, 18, 5.00, 5.00],
            [926, '* Prato Almoço (12unid)', 'UTENSILIOS COZINHA', 'UN', 10, 2, 4, 120.00, 120.00],
            [927, '* Prato Sobremesa (12unid)', 'UTENSILIOS COZINHA', 'UN', 10, 2, 12, 72.00, 72.00],
            [985, 'Travessa de Plástico', 'UTENSILIOS COZINHA', 'UN', 4, 0, 5, 35.00, 35.00],
            [1004, 'Travessa Inox', 'UTENSILIOS COZINHA', 'UN', 4, 0, 40, 10.00, 10.00],
            [1006, 'Avental', 'UTENSILIOS COZINHA', 'UN', 30, 2, 7, 17.60, 17.60],
            [1047, 'Porta gelo', 'UTENSILIOS COZINHA', 'UN', 1, 1, 1, 45.00, 45.00],
            [1048, '* Taça vinho (6 und)', 'UTENSILIOS COZINHA', 'UN', 12, 1, 2, 90.00, 90.00],
            [1049, '* Molheira', 'UTENSILIOS COZINHA', 'UN', 24, 12, 71, 5.00, 5.00],
            [1113, 'Travessa pequena vidro', 'UTENSILIOS COZINHA', 'UN', 1, 0, 2, 20.00, 20.00],
            [1131, 'Talher Espeto', 'UTENSILIOS COZINHA', 'UN', 30, 6, 43, 2.00, 2.00],
            [1132, 'Peneira inox', 'UTENSILIOS COZINHA', 'UN', 1, 1, 3, 10.00, 10.00],
            [1138, 'Galheteiro', 'UTENSILIOS COZINHA', 'UN', 2, 0, 2, 15.00, 15.00],
        ];
    }
};
