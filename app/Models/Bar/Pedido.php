<?php

namespace App\Models\Bar;

use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Bar\Mesa;
use App\Models\Bar\ItemPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Pedido extends Model
{
    protected $fillable = [
        'mesa_id',
        'reserva_id',
        'cliente_id',
        'status', // aberto, fechado, pago
        'total',
        'taxa_servico',
        'remover_taxa',
        'total_com_taxa',
        'pedido_apartamento',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function quarto()
    {
        return $this->hasOneThrough(Quarto::class, Reserva::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }
    
}
