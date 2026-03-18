<?php

namespace Tests\Feature;

use App\Models\Acompanhante;
use App\Models\Reserva;
use App\Models\ReservaRefeicao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaRefeicoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_salva_refeicoes_titular(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();

        $response = $this->post("/admin/reservas/{$reserva->id}/refeicoes", [
            'refeicoes' => [
                [
                    'hospede_nome' => 'João',
                    'hospede_tipo' => 'titular',
                    'cafe'         => '1',
                    'jantar'       => '1',
                ],
            ],
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('notice');

        $this->assertDatabaseHas('reserva_refeicoes', [
            'reserva_id'   => $reserva->id,
            'hospede_nome' => 'João',
            'hospede_tipo' => 'titular',
            'cafe'         => 1,
            'jantar'       => 1,
            'almoco'       => 0,
        ]);

        $this->assertSame(1, ReservaRefeicao::where('reserva_id', $reserva->id)->count());
    }

    public function test_salva_titular_e_acompanhante(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();
        $acompanhante = Acompanhante::factory()->create(['reserva_id' => $reserva->id]);

        $response = $this->post("/admin/reservas/{$reserva->id}/refeicoes", [
            'refeicoes' => [
                [
                    'hospede_nome' => 'João',
                    'hospede_tipo' => 'titular',
                    'cafe'         => '1',
                ],
                [
                    'hospede_nome'    => 'Maria',
                    'hospede_tipo'    => 'acompanhante',
                    'acompanhante_id' => $acompanhante->id,
                    'almoco'          => '1',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertSame(2, ReservaRefeicao::where('reserva_id', $reserva->id)->count());

        $this->assertDatabaseHas('reserva_refeicoes', [
            'reserva_id'      => $reserva->id,
            'hospede_nome'    => 'Maria',
            'acompanhante_id' => $acompanhante->id,
            'almoco'          => 1,
        ]);
    }

    public function test_sobrescreve_refeicoes_existentes(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();

        // Salva primeira vez
        $this->post("/admin/reservas/{$reserva->id}/refeicoes", [
            'refeicoes' => [
                ['hospede_nome' => 'João', 'hospede_tipo' => 'titular', 'cafe' => '1'],
                ['hospede_nome' => 'Maria', 'hospede_tipo' => 'acompanhante'],
            ],
        ]);

        $this->assertSame(2, ReservaRefeicao::where('reserva_id', $reserva->id)->count());

        // Salva novamente com dados diferentes (deve sobrescrever)
        $this->post("/admin/reservas/{$reserva->id}/refeicoes", [
            'refeicoes' => [
                ['hospede_nome' => 'João', 'hospede_tipo' => 'titular', 'jantar' => '1'],
            ],
        ]);

        // Sem duplicatas: apenas 1 linha
        $this->assertSame(1, ReservaRefeicao::where('reserva_id', $reserva->id)->count());

        // Valores atualizados
        $this->assertDatabaseHas('reserva_refeicoes', [
            'reserva_id'   => $reserva->id,
            'hospede_nome' => 'João',
            'jantar'       => 1,
            'cafe'         => 0,
        ]);
    }

    public function test_checkboxes_nao_marcados_salvam_false(): void
    {
        $this->adminLogin();

        $reserva = Reserva::factory()->create();

        // Nenhum checkbox marcado
        $this->post("/admin/reservas/{$reserva->id}/refeicoes", [
            'refeicoes' => [
                ['hospede_nome' => 'Carlos', 'hospede_tipo' => 'titular'],
            ],
        ]);

        $this->assertDatabaseHas('reserva_refeicoes', [
            'reserva_id'   => $reserva->id,
            'hospede_nome' => 'Carlos',
            'cafe'         => 0,
            'almoco'       => 0,
            'jantar'       => 0,
        ]);
    }

    public function test_reserva_inexistente_retorna_404(): void
    {
        $this->adminLogin();

        $response = $this->post('/admin/reservas/999999/refeicoes', [
            'refeicoes' => [
                ['hospede_nome' => 'João', 'hospede_tipo' => 'titular'],
            ],
        ]);

        $response->assertStatus(404);
    }
}
