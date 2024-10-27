<?php

namespace App\Models;

use App\Models\Bar\ItemPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'descricao',
        'valor_unitario',
        'categoria_produto',
        'codigo_barras_produto',
        'codigo_interno',
        'impressora',
        'unidade',
        'ativo',
        'criado_por',
        'complemento',
        'produto_servico',
        'preco_custo',
        'preco_venda',
        'estoque_minimo',
        'estoque_maximo',
    ];

    const UNIDADES = [
        'UN' => 'Unidade',
        'KG' => 'Quilograma',
        'LT' => 'Litro',
        'CX' => 'Caixa',
        'PC' => 'Peça',
        'MT' => 'Metro',
        'FD' => 'Fardo',
        'SC' => 'Saco',
        'BD' => 'Balde',
        'DS' => 'Dose',
        'CP' => 'Copo',
        'TC' => 'Taça',
    ];

    const IMPRESSORA = [
        'COZINHA',
        'BAR',
        'RECEPCAO',
    ];

    public function usuarioCriador()
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    public function estoques()
    {
        return $this->hasMany(Estoque::class, 'produto_id');
    }
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_produto');
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function composicoes()
    {
        return $this->hasMany(ProdutoComposicao::class, 'produto_id');
    }
}
