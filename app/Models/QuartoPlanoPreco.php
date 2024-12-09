<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuartoPlanoPreco extends Model
{
    protected $fillable = [
        'quarto_id', 'data_inicio', 'data_fim', 'is_default', 'preco_segunda', 
        'preco_terca', 'preco_quarta', 'preco_quinta', 'preco_sexta', 'preco_sabado', 
        'preco_domingo', 'is_duplo', 'is_triplo', 'is_individual'
    ];

    // Relação com Quarto
    public function quarto()
    {
        return $this->belongsTo(Quarto::class);
    }
}