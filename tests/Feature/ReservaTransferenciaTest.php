<?php

namespace Tests\Feature;

use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaTransferenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfere_com_sucesso(): void
    {
        $this->adminLogin();

        $quartoDestino = Quarto::factory()->create();
        $reserva = Reserva::factory()->create([
            'data_checkin'  => '2026-07-01',
            'data_checkout' => '2026-07-10',
        ]);

        $response = $this->post("/admin/reservas/{$reserva->id}/transferir", [
            'quarto_id'          => $quartoDestino->id,
            'data_transferencia' => '2026-07-05',
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('notice');

        $this->assertDatabaseHas('reservas', [
            'id'           => $reserva->id,
            'quarto_id'    => $quartoDestino->id,
            'data_checkin' => '2026-07-05',
        ]);
    }

    public function test_rejeita_quarto_ocupado(): void
    {
        $this->adminLogin();

        $quartoDestino = Quarto::factory()->create();
        $checkoutOriginal = '2026-07-10';

        // Reserva que ocupa o quarto destino no período da transferência
        Reserva::factory()->create([
            'quarto_id'        => $quartoDestino->id,
            'data_checkin'     => '2026-07-05',
            'data_checkout'    => '2026-07-12',
            'situacao_reserva' => 'RESERVADO',
        ]);

        $reserva = Reserva::factory()->create([
            'data_checkin'  => '2026-07-01',
            'data_checkout' => $checkoutOriginal,
        ]);

        $response = $this->post("/admin/reservas/{$reserva->id}/transferir", [
            'quarto_id'          => $quartoDestino->id,
            'data_transferencia' => '2026-07-05',
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('error');
    }

    public function test_validacao_sem_quarto_id(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();

        $response = $this->post("/admin/reservas/{$reserva->id}/transferir", [
            // quarto_id omitido propositalmente
            'data_transferencia' => '2026-07-05',
        ]);

        $response->assertRedirect(); // redireciona de volta com erros de validação
    }

    public function test_preserva_data_checkout_original(): void
    {
        $this->adminLogin();

        $quartoDestino  = Quarto::factory()->create();
        $checkoutOriginal = '2026-07-10';

        $reserva = Reserva::factory()->create([
            'data_checkin'  => '2026-07-01',
            'data_checkout' => $checkoutOriginal,
        ]);

        $this->post("/admin/reservas/{$reserva->id}/transferir", [
            'quarto_id'          => $quartoDestino->id,
            'data_transferencia' => '2026-07-05',
        ]);

        // data_checkout não deve ser alterado pela transferência
        $this->assertDatabaseHas('reservas', [
            'id'            => $reserva->id,
            'data_checkout' => $checkoutOriginal,
        ]);
    }
}
