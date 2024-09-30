<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'tipo_reserva',
        'tipo_solicitante',
        'situacao_reserva',
        'previsao_chegada',
        'previsao_saida',
        'data_checkin', // Novo campo
        'data_checkout', // Novo campo
        'cliente_solicitante_id',
        'cliente_responsavel_id',
        'quarto_id',
        'tipo_acomodacao',
        'usuario_operador_id',
        'email_solicitante',
        'celular',
        'email_faturamento',
        'empresa_faturamento_id',
        'empresa_solicitante_id',
        'observacoes',
        'observacoes_internas',
    ];


    const TIPOSRESERVA = [
        'INDIVIDUAL' => 'Individual',
        'GRUPO' => 'Grupo',
    ];

    const SITUACOESRESERVA = [
        'PRÉ RESERVA' => [
            'label' => 'Pré Reserva',
            'background' => '#b2b2b2',
        ],
        'CONFIRMADA' => [
            'label' => 'Confirmada',
            'background' => 'green',
        ],
        'CANCELADA' => [
            'label' => 'Cancelada',
            'background' => 'red',
        ]
    ];

    // Relacionamentos
    public function clienteSolicitante()
    {
        return $this->belongsTo(Cliente::class, 'cliente_solicitante_id');
    
    }

    public function clienteResponsavel()
    {
        return $this->belongsTo(Cliente::class, 'cliente_responsavel_id');
    
    }

    public function quarto()
    {
        return $this->belongsTo(Quarto::class, 'quarto_id');
    
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_operador_id');
    }

    public function empresaFaturamento()
    {
        return $this->belongsTo(Empresa::class, 'empresa_faturamento_id');
    }

    public function empresaSolicitante()
    {
        return $this->belongsTo(Empresa::class, 'empresa_solicitante_id');
    }


    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

}
