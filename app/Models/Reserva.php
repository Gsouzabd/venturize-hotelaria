<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'tipo_reserva',
        'situacao_reserva',
        'previsao_chegada',
        'previsao_saida',
        'data_checkin', // Novo campo
        'data_checkout', // Novo campo
        'cliente_id',
        'quarto_id',
        'usuario_operador_id',
        'email_solicitante',
        'celular',
        'email_faturamento',
        'empresa_faturamento_id',
        'empresa_solicitante_id',
        'observacoes',
        'observacoes_internas',
    ];

    // Relacionamentos
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function quarto()
    {
        return $this->belongsTo(Quarto::class);
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_operador_id');
    }
}
