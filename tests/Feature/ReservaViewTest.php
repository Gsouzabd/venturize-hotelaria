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

    public function test_formulario_edicao_reserva_pre_reserva(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'PRÉ RESERVA',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200);
    }

    public function test_formulario_edicao_reserva_cancelada(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'CANCELADA',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200);
    }

    public function test_formulario_edicao_reserva_finalizado(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta([
            'situacao_reserva' => 'FINALIZADO',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200);
    }

    public function test_aba_acompanhantes_visivel_em_edicao(): void
    {
        $this->adminLogin();

        // Para qualquer status de reserva, a aba deve aparecer em modo de edição
        foreach (['RESERVADO', 'PRÉ RESERVA', 'HOSPEDADO', 'FINALIZADO'] as $status) {
            $reserva = $this->criarReservaCompleta(['situacao_reserva' => $status]);

            $response = $this->get("/admin/reservas/{$reserva->id}");

            $response->assertStatus(200)
                     ->assertSee('Acompanhantes')
                     ->assertSee('acompanhantes-tab', false);
        }
    }

    public function test_aba_acompanhantes_nao_aparece_em_nova_reserva(): void
    {
        $this->adminLogin();

        $response = $this->get('/admin/reservas/create');

        $response->assertStatus(200)
                 ->assertDontSee('acompanhantes-tab', false);
    }

    public function test_acompanhantes_existentes_sao_listados_na_aba(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta(['situacao_reserva' => 'RESERVADO']);

        \App\Models\Acompanhante::factory()->create([
            'reserva_id' => $reserva->id,
            'nome'       => 'Hóspede Teste Acompanhante',
            'tipo'       => 'Adulto',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200)
                 ->assertSee('Hóspede Teste Acompanhante');
    }

    public function test_link_edicao_acompanhante_exibido_quando_vinculado_a_cliente(): void
    {
        $this->adminLogin();

        $cliente = \App\Models\Cliente::factory()->create(['nome' => 'Cliente Vinculado']);
        $reserva = $this->criarReservaCompleta(['situacao_reserva' => 'RESERVADO']);

        \App\Models\Acompanhante::factory()->create([
            'reserva_id' => $reserva->id,
            'cliente_id' => $cliente->id,
            'nome'       => 'Cliente Vinculado',
            'tipo'       => 'Adulto',
        ]);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200)
                 // Link para edição do cliente deve estar presente
                 ->assertSee("/admin/clientes/{$cliente->id}", false);
    }

    public function test_aba_editar_periodo_visivel_em_hospedado(): void
    {
        $this->adminLogin();

        $reserva = $this->criarReservaCompleta(['situacao_reserva' => 'HOSPEDADO']);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200)
                 ->assertSee('Editar Período da Reserva')
                 ->assertSee('periodo_checkin', false)
                 ->assertSee('periodo_checkout', false);
    }

    public function test_aba_editar_periodo_nao_visivel_em_reservado(): void
    {
        $this->adminLogin();

        // Para RESERVADO o tab Transferência (e portanto editar período) não aparece
        $reserva = $this->criarReservaCompleta(['situacao_reserva' => 'RESERVADO']);

        $response = $this->get("/admin/reservas/{$reserva->id}");

        $response->assertStatus(200)
                 ->assertDontSee('periodo_checkin', false);
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
