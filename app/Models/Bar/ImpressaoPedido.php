<?php

namespace App\Models\Bar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImpressaoPedido extends Model
{
    use HasFactory;

    protected $table = 'impressoes_pedidos';
    
    protected $fillable = [
        'pedido_id',
        'agente_impressao',
        'ip_origem',
        'status_impressao',
        'detalhes_erro',
        'dados_impressao'
    ];

    protected $casts = [
        'dados_impressao' => 'array'
    ];

    /**
     * Relacionamento com pedido
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Scope para impressões pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status_impressao', 'pendente');
    }

    /**
     * Scope para impressões bem-sucedidas
     */
    public function scopeSucesso($query)
    {
        return $query->where('status_impressao', 'sucesso');
    }

    /**
     * Scope para impressões com erro
     */
    public function scopeComErro($query)
    {
        return $query->where('status_impressao', 'erro');
    }

    /**
     * Scope para impressões de hoje
     */
    public function scopeHoje($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope por agente de impressão
     */
    public function scopePorAgente($query, $agente)
    {
        return $query->where('agente_impressao', $agente);
    }

    /**
     * Marcar como processando
     */
    public function marcarComoProcessando()
    {
        $this->update(['status_impressao' => 'processando']);
    }

    /**
     * Marcar como sucesso
     */
    public function marcarComoSucesso($dadosExtras = [])
    {
        $this->update([
            'status_impressao' => 'sucesso',
            'dados_impressao' => array_merge($this->dados_impressao ?? [], $dadosExtras)
        ]);
    }

    /**
     * Marcar como erro
     */
    public function marcarComoErro($detalhesErro, $dadosExtras = [])
    {
        $this->update([
            'status_impressao' => 'erro',
            'detalhes_erro' => $detalhesErro,
            'dados_impressao' => array_merge($this->dados_impressao ?? [], $dadosExtras)
        ]);
    }
}