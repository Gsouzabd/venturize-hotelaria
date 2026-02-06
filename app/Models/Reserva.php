<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\Pagamento;
use App\Models\Bar\Pedido;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'tipo_reserva',
        'tipo_solicitante',
        'situacao_reserva',
        'previsao_chegada',
        'previsao_saida',
        'data_checkin',
        'data_checkout',
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
        'veiculo_modelo',
        'veiculo_cor',
        'veiculo_placa',
        'adultos',
        'criancas_ate_7',
        'criancas_mais_7',
        'cart_serialized', // Novo campo
        'total', // Novo campo
        'remover_taxa_servico', // Nova coluna
        'com_cafe',
        'valor_cafe',

    ];

    protected $casts = [
        'com_cafe' => 'boolean',
    ];

    const TIPOSRESERVA = [
        'INDIVIDUAL' => 'Individual',
        'GRUPO' => 'Grupo',
        'DAY_USE' => 'Day Use',
    ];

    const SITUACOESRESERVA = [
        'PRÉ RESERVA' => [
            'label' => 'Pré Reserva',
            'background' => '#417A86',
        ],
        'RESERVADO' => [
            'label' => 'Reservado',
            'background' => 'green',
        ],
        'CANCELADA' => [
            'label' => 'Cancelada',
            'background' => 'red',
        ],
        'HOSPEDADO' => [
            'label' => 'Hospedado',
            'background' => '#326dd7',
        ], 
        'NO SHOW' => [
            'label' => 'No Show',
            'background' => 'orange',
        ],
        'FINALIZADO' => [
            'label' => 'Finalizado',
            'background' => 'gray',
        ],
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

    public function acompanhantes()
    {
        return $this->hasMany(Acompanhante::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    // Novo método para transformar cart_serialized em array
    public function getCartSerializedAttribute()
    {
        $value = $this->attributes['cart_serialized'] ?? '[]';
        return json_decode($value, true);
    }
    
    // Método para obter a relação data/preço
    public function getPrecosDiarios()
    {
        $precos = $this->getCartSerializedAttribute()['precosDiarios'] ?? [];
        //precisamos verificar se o array[0] vem no formato ["2024-10-08":"789.00"]

        return $precos;
    }

    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut()
    {
        return $this->hasOne(CheckOut::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Indica se a reserva é do tipo Day Use.
     */
    public function isDayUse(): bool
    {
        return $this->tipo_reserva === 'DAY_USE';
    }
    
}
