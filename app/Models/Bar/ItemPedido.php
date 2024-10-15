<?php

namespace App\Models\Bar;

use App\Models\Produto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ItemPedido extends Model
{
    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
