<?php

namespace App\Models\Bar;

use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Bar\Mesa;
use App\Models\Bar\ItemPedido;
use App\Models\Bar\ImpressaoPedido;
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
        'observacoes', // Novo campo para observações

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

    /**
     * Relacionamento com impressões
     */
    public function impressoes()
    {
        return $this->hasMany(ImpressaoPedido::class);
    }

    /**
     * Última impressão do pedido
     */
    public function ultimaImpressao()
    {
        return $this->hasOne(ImpressaoPedido::class)->latest();
    }

    /**
     * Verifica se o pedido foi impresso com sucesso
     */
    public function foiImpresso()
    {
        return $this->impressoes()->where('status_impressao', 'sucesso')->exists();
    }

    /**
     * Verifica se tem impressão pendente
     */
    public function temImpressaoPendente()
    {
        return $this->impressoes()->where('status_impressao', 'pendente')->exists();
    }

    /**
     * Conta total de impressões bem-sucedidas
     */
    public function totalImpressoes()
    {
        return $this->impressoes()->where('status_impressao', 'sucesso')->count();
    }

    /**
     * Scope para pedidos não impressos
     */
    public function scopeNaoImpressos($query)
    {
        return $query->whereDoesntHave('impressoes', function($q) {
            $q->where('status_impressao', 'sucesso');
        });
    }

    /**
     * Scope para pedidos impressos
     */
    public function scopeImpressos($query)
    {
        return $query->whereHas('impressoes', function($q) {
            $q->where('status_impressao', 'sucesso');
        });
    }
    
}
