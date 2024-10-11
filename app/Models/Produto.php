<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    const CATEGORIAS = [
        'ALIMENTO' => 'Alimento',
        'BEBIDA' => 'Bebida',
        'OUTROS' => 'Outros',
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
}
