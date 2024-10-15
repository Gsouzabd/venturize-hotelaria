<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pedido extends Model
{
    protected $fillable = [
        'mesa_id',
        'status', // aberto, fechado, pago
        'total',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }
}
