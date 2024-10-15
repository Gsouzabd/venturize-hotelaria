<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'status',
        'ativa',
    ];


    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}