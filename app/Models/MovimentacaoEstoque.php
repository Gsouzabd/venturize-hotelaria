<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoEstoque extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_estoque';

    protected $fillable = [
        'produto_id',
        'local_estoque_origem_id',  // Para transferência
        'local_estoque_destino_id', // Para transferência
        'quantidade',
        'tipo',  // entrada, saida, transferencia
        'usuario_id', // Quem operou a movimentação
        'data_movimentacao',
        'valor_unitario_custo', // Novo campo
        'valor_unitario_venda', // Novo campo
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function localOrigem()
    {
        return $this->belongsTo(LocalEstoque::class, 'local_estoque_origem_id');
    }

    public function localDestino()
    {
        return $this->belongsTo(LocalEstoque::class, 'local_estoque_destino_id');
    }
}