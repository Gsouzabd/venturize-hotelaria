<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaViewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cria uma reserva com cliente_responsavel_id populado (necessário para o
     * controller evitar acesso a null->id ao criar o Pedido de apartamento).
     */
    private function criarReservaCompleta(array $atributos = []): Reserva
    {
        $cliente = Cliente::factory()->create();
        $quarto  = Quarto::factory()->create();

        return Reserva::factory()->create(array_merge([
            'cliente_solicitante_id' => $cliente->id,
            'cliente_responsavel_id' => $cliente->id,
            'quarto_id'              => $quarto->id,
        ], $atributos));
    }

    public function test_formulario_nova_reserva(): void
    {
        $this->adminLogin();

        $response = $this->get('/admin/reservas/create');

        $response->assertStatus(200);
    }

    public function test_formulario_edicao_reserva_reservado(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'RESERVADO',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200);
    }

    public function test_formulario_reserva_hospedado_exibe_abas_extras(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'HOSPEDADO',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200)
                 ->assertSee('Refeições')
                 ->assertSee('Transferência');
    }

    public function test_mapa_ocupacao(): void
    {
        $this->adminLogin();

        $response = $this->get('/admin/reservas/mapa');

        $response->assertStatus(200);
    }

    public function test_ficha_nacional_pdf(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'HOSPEDADO',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}/gerar-ficha-nacional");

        // DomPDF stream() escreve direto no output buffer do PHP (não no Response do Laravel).
        // Status 200 confirma que a rota existe e o PDF foi gerado sem exceções.
        $response->assertStatus(200);
    }
}
