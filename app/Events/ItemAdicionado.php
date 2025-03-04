<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemAdicionado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $item;
    public $data;
    public $pdf_url;

    /**
     * Cria uma nova instância do evento.
     *
     * @param array $data Informações adicionais do pedido.
     * @param mixed $item Informações do item adicionado.
     * @param string|null $pdf_url URL do PDF, se disponível.
     */
    public function __construct($data, $item, $pdf_url = null)
    {
        $this->item = $item;
        $this->data = $data;
        $this->pdf_url = $pdf_url;
    }

    /**
     * Define o canal de transmissão.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel('itens');
    }

    /**
     * Define o nome do evento.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'item.adicionado';
    }

    /**
     * Define os dados adicionais transmitidos com o evento.
     *
     * @return array
     */
    public function broadcastWith()
    {
        
        return [
            'pedido_id' => $this->data['pedido_id'],
            'itens' => $this->item,
            'pdf_url' => $this->pdf_url,
        ];
    }
}
