<?php
namespace App\Listeners;

use App\Events\ItemAdicionado;
use App\Services\Bar\MesaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class GeneratePdfForItemAdicionado
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ItemAdicionado  $event
     * @return void
     */
    public function handle(ItemAdicionado $event)
    {
        $pdfContent = app(MesaService::class)->gerarCupomItemAdicionado($event->data['pedido_id'], $event->item);

        // Save the PDF to a temporary file
        $pdfPath = storage_path("app/public/cupom_pedido_{$event->data['pedido_id']}.pdf");
        file_put_contents($pdfPath, $pdfContent);

        // Optionally, you can update the event with the PDF URL
        $event->pdf_url = asset("storage/cupom_pedido_{$event->data['pedido_id']}.pdf");
    }
}