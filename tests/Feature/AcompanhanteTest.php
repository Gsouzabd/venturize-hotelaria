<?php

namespace Tests\Feature;

use App\Models\Acompanhante;
use App\Models\Cliente;
use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcompanhanteTest extends TestCase
{
    use RefreshDatabase;

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function criarReserva(array $attrs = []): Reserva
    {
        $cliente = Cliente::factory()->create();
        $quarto  = Quarto::factory()->create();

        return Reserva::factory()->create(array_merge([
            'cliente_solicitante_id' => $cliente->id,
            'cliente_responsavel_id' => $cliente->id,
            'quarto_id'              => $quarto->id,
        ], $attrs));
    }

    private function payloadValido(array $extra = []): array
    {
        return array_merge([
            'nome'            => 'João Silva',
            'cpf'             => '111.222.333-44',
            'tipo'            => 'Adulto',
            'data_nascimento' => '1990-05-20',
            'email'           => 'joao@exemplo.com',
            'telefone'        => '(62) 99999-0000',
        ], $extra);
    }

    // ─── POST /admin/reservas/{id}/acompanhantes ─────────────────────────────

    public function test_adicionar_acompanhante_adulto_completo(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido()
        );

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('acompanhante.nome', 'João Silva')
                 ->assertJsonPath('acompanhante.tipo', 'Adulto');

        $this->assertDatabaseHas('acompanhantes', [
            'reserva_id' => $reserva->id,
            'nome'       => 'João Silva',
            'tipo'       => 'Adulto',
        ]);
    }

    public function test_adicionar_acompanhante_crianca_mais_7(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['tipo' => 'Criança mais de 7 anos', 'email' => null, 'telefone' => null])
        );

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('acompanhantes', [
            'reserva_id' => $reserva->id,
            'tipo'       => 'Criança mais de 7 anos',
        ]);
    }

    public function test_adicionar_acompanhante_crianca_ate_7(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['tipo' => 'Criança até 7 anos', 'email' => null, 'telefone' => null])
        );

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_adicionar_acompanhante_sem_campos_opcionais(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson("/admin/reservas/{$reserva->id}/acompanhantes", [
            'nome' => 'Maria',
            'tipo' => 'Adulto',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_adicionar_acompanhante_sem_nome_retorna_422(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['nome' => ''])
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nome']);
    }

    public function test_adicionar_acompanhante_sem_tipo_retorna_422(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['tipo' => ''])
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tipo']);
    }

    public function test_adicionar_acompanhante_tipo_invalido_retorna_422(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['tipo' => 'INVALIDO'])
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tipo']);
    }

    public function test_adicionar_acompanhante_email_invalido_retorna_422(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido(['email' => 'nao-e-email'])
        );

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_adicionar_acompanhante_reserva_inexistente_retorna_404(): void
    {
        $this->adminLogin();

        $response = $this->postJson(
            '/admin/reservas/999999/acompanhantes',
            $this->payloadValido()
        );

        $response->assertStatus(404);
    }

    public function test_adicionar_acompanhante_vincula_cliente_por_cpf(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $cliente = Cliente::factory()->create(['cpf' => '55566677788']);

        $response = $this->postJson("/admin/reservas/{$reserva->id}/acompanhantes", [
            'nome' => 'Cliente Existente',
            'cpf'  => '555.666.777-88',
            'tipo' => 'Adulto',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('cliente_id', $cliente->id);

        $this->assertDatabaseHas('acompanhantes', [
            'reserva_id' => $reserva->id,
            'cliente_id' => $cliente->id,
        ]);
    }

    public function test_adicionar_acompanhante_sem_match_de_cpf_nao_vincula_cliente(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->postJson("/admin/reservas/{$reserva->id}/acompanhantes", [
            'nome' => 'Desconhecido',
            'cpf'  => '000.000.000-00',
            'tipo' => 'Adulto',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'cliente_id' => null]);

        $this->assertDatabaseHas('acompanhantes', [
            'reserva_id' => $reserva->id,
            'cliente_id' => null,
        ]);
    }

    public function test_adicionar_acompanhante_sem_autenticacao_redireciona(): void
    {
        $reserva = $this->criarReserva();

        $response = $this->postJson(
            "/admin/reservas/{$reserva->id}/acompanhantes",
            $this->payloadValido()
        );

        $response->assertStatus(401);
    }

    // ─── DELETE /admin/reservas/{id}/acompanhantes/{aid} ─────────────────────

    public function test_remover_acompanhante_existente(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $acomp = Acompanhante::factory()->create([
            'reserva_id' => $reserva->id,
            'tipo'       => 'Adulto',
        ]);

        $response = $this->deleteJson(
            "/admin/reservas/{$reserva->id}/acompanhantes/{$acomp->id}"
        );

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseMissing('acompanhantes', ['id' => $acomp->id]);
    }

    public function test_remover_acompanhante_de_outra_reserva_retorna_404(): void
    {
        $this->adminLogin();

        $reservaA = $this->criarReserva();
        $reservaB = $this->criarReserva();

        $acompDaB = Acompanhante::factory()->create([
            'reserva_id' => $reservaB->id,
            'tipo'       => 'Adulto',
        ]);

        // Tenta remover acompanhante de B usando o ID de A → deve ser 404
        $response = $this->deleteJson(
            "/admin/reservas/{$reservaA->id}/acompanhantes/{$acompDaB->id}"
        );

        $response->assertStatus(404);

        // Registro de B deve permanecer intacto
        $this->assertDatabaseHas('acompanhantes', ['id' => $acompDaB->id]);
    }

    public function test_remover_acompanhante_inexistente_retorna_404(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $response = $this->deleteJson(
            "/admin/reservas/{$reserva->id}/acompanhantes/999999"
        );

        $response->assertStatus(404);
    }

    public function test_remover_acompanhante_sem_autenticacao_redireciona(): void
    {
        $reserva = $this->criarReserva();
        $acomp   = Acompanhante::factory()->create([
            'reserva_id' => $reserva->id,
            'tipo'       => 'Adulto',
        ]);

        $response = $this->deleteJson(
            "/admin/reservas/{$reserva->id}/acompanhantes/{$acomp->id}"
        );

        $response->assertStatus(401);
    }

    // ─── Multiplos acompanhantes ──────────────────────────────────────────────

    public function test_pode_adicionar_multiplos_acompanhantes_na_mesma_reserva(): void
    {
        $this->adminLogin();
        $reserva = $this->criarReserva();

        $this->postJson("/admin/reservas/{$reserva->id}/acompanhantes", [
            'nome' => 'Adulto 1', 'tipo' => 'Adulto',
        ])->assertStatus(200);

        $this->postJson("/admin/reservas/{$reserva->id}/acompanhantes", [
            'nome' => 'Criança 1', 'tipo' => 'Criança até 7 anos',
        ])->assertStatus(200);

        $this->assertDatabaseCount('acompanhantes', 2);
        $this->assertSame(2, $reserva->fresh()->acompanhantes()->count());
    }
}
