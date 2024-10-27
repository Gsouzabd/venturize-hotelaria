<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoComposicao extends Model
{
    use HasFactory;

    protected $table = 'produto_composicoes';

    protected $fillable = [
        'produto_id',
        'insumo_id', // Relaciona com outro produto que atua como insumo
        'quantidade', // Quantidade do insumo
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Produto::class, 'insumo_id'); // Aqui insumo_id se refere a outro produto
    }
}
