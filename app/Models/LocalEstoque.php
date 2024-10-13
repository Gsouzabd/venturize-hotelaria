<?php

namespace App\Models;

use App\Models\Estoque;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocalEstoque extends Model
{
    use HasFactory;

    protected $table = 'locais_estoque';

    protected $fillable = ['nome'];

    public function estoques()
    {
        return $this->hasMany(Estoque::class, 'local_estoque_id');
    }

    public function movimentacoesOrigem()
    {
        return $this->hasMany(MovimentacaoEstoque::class, 'local_estoque_origem_id');
    }

    // Movimentações onde este local é o destino
    public function movimentacoesDestino()
    {
        return $this->hasMany(MovimentacaoEstoque::class, 'local_estoque_destino_id');
    }

    // Combinar ambas as movimentações (origem e destino)
    public function movimentacoes()
    {
        return $this->movimentacoesOrigem->merge($this->movimentacoesDestino);
    }

}
