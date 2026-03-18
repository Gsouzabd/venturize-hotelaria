<?php

namespace Tests\Feature;

use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaMoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_move_reserva_com_sucesso(): void
    {
        $this->adminLogin();

        $quartoDestino = Quarto::factory()->create();
        $reserva = Reserva::factory()->create([
            'data_checkin'  => '2026-07-01',
            'data_checkout' => '2026-07-05',
        ]);

        $response = $this->patchJson("/admin/reservas/{$reserva->id}/mover", [
            'quarto_id'    => $quartoDestino->id,
            'data_checkin' => '2026-07-10',
            'data_checkout'=> '2026-07-14',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('reservas', [
            'id'           => $reserva->id,
            'quarto_id'    => $quartoDestino->id,
            'data_checkin' => '2026-07-10',
            'data_checkout'=> '2026-07-14',
        ]);
    }

    public function test_rejeita_quarto_ocupado(): void
    {
        $this->adminLogin();

        $quarto = Quarto::factory()->create();

        // Reserva existente que ocupa o quarto destino no mesmo período
        Reserva::factory()->create([
            'quarto_id'        => $quarto->id,
            'data_checkin'     => '2026-07-10',
            'data_checkout'    => '2026-07-14',
            'situacao_reserva' => 'RESERVADO',
        ]);

        $reservaParaMover = Reserva::factory()->create([
            'data_checkin'  => '2026-07-01',
            'data_checkout' => '2026-07-05',
        ]);

        $response = $this->patchJson("/admin/reservas/{$reservaParaMover->id}/mover", [
            'quarto_id'    => $quarto->id,
            'data_checkin' => '2026-07-10',
            'data_checkout'=> '2026-07-14',
        ]);

        $response->assertStatus(409)
                 ->assertJson(['success' => false]);
    }

    public function test_permite_mover_para_mesmo_quarto_nova_data(): void
    {
        $this->adminLogin();

        $quarto = Quarto::factory()->create();
        $reserva = Reserva::factory()->create([
            'quarto_id'    => $quarto->id,
            'data_checkin' => '2026-07-01',
            'data_checkout'=> '2026-07-05',
        ]);

        // Move para o mesmo quarto mas em data diferente (sem conflito)
        $response = $this->patchJson("/admin/reservas/{$reserva->id}/mover", [
            'quarto_id'    => $quarto->id,
            'data_checkin' => '2026-08-01',
            'data_checkout'=> '2026-08-05',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_validacao_campos_obrigatorios(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();

        $response = $this->patchJson("/admin/reservas/{$reserva->id}/mover", [
            // quarto_id omitido propositalmente
            'data_checkin' => '2026-07-10',
            'data_checkout'=> '2026-07-14',
        ]);

        $response->assertStatus(422);
    }

    public function test_reserva_nao_encontrada(): void
    {
        $this->adminLogin();

        $quarto = Quarto::factory()->create();

        $response = $this->patchJson('/admin/reservas/999999/mover', [
            'quarto_id'    => $quarto->id,
            'data_checkin' => '2026-07-10',
            'data_checkout'=> '2026-07-14',
        ]);

        $response->assertStatus(404);
    }
}
