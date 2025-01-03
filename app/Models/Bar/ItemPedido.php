<?php

namespace App\Models\Bar;

use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ItemPedido extends Model
{

    protected $table = 'itens_pedidos';
    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'preco',
        'operador_id', // Novo campo para operador

    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_id');
    }
}
