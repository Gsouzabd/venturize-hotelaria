<?php

namespace Tests\Feature;

use App\Models\Quarto;
use App\Models\QuartoPlanoPreco;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisponibilidadeTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'data_entrada'      => '20/06/2026',
            'data_saida'        => '23/06/2026',
            'adultos'           => 2,
            'criancas_ate_7'    => 0,
            'criancas_mais_7'   => 0,
            'composicao_quarto' => 'Duplo',
            'reserva_id'        => null,
        ], $overrides);
    }

    private function criarQuartoComPlano(array $quartoAtributos = []): Quarto
    {
        $quarto = Quarto::factory()->create($quartoAtributos);

        QuartoPlanoPreco::create([
            'quarto_id'     => $quarto->id,
            'is_default'    => true,
            'is_duplo'      => true,
            'is_triplo'     => false,
            'is_individual' => false,
            'preco_segunda' => 200.00,
            'preco_terca'   => 200.00,
            'preco_quarta'  => 200.00,
            'preco_quinta'  => 200.00,
            'preco_sexta'   => 250.00,
            'preco_sabado'  => 300.00,
            'preco_domingo' => 300.00,
        ]);

        return $quarto;
    }

    public function test_retorna_quartos_disponiveis(): void
    {
        $this->adminLogin();
        $this->criarQuartoComPlano();

        $response = $this->postJson('/admin/verificar-disponibilidade', $this->basePayload());

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['success', 'quartos']);
    }

    public function test_retorna_vazio_quando_quarto_ocupado(): void
    {
        $this->adminLogin();
        $quarto = $this->criarQuartoComPlano();

        Reserva::factory()->create([
            'quarto_id'        => $quarto->id,
            'data_checkin'     => '2026-06-20',
            'data_checkout'    => '2026-06-23',
            'situacao_reserva' => 'RESERVADO',
        ]);

        $response = $this->postJson('/admin/verificar-disponibilidade', $this->basePayload());

        $response->assertStatus(200)
                 ->assertJson(['success' => false]);
    }

    public function test_exclui_propria_reserva_ao_editar(): void
    {
        $this->adminLogin();
        $quarto = $this->criarQuartoComPlano();

        $reserva = Reserva::factory()->create([
            'quarto_id'        => $quarto->id,
            'data_checkin'     => '2026-06-20',
            'data_checkout'    => '2026-06-23',
            'situacao_reserva' => 'RESERVADO',
        ]);

        // Passando o reserva_id, a própria reserva deve ser excluída da checagem de conflito
        $response = $this->postJson('/admin/verificar-disponibilidade', $this->basePayload([
            'reserva_id' => $reserva->id,
        ]));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_validacao_campos_obrigatorios(): void
    {
        $this->adminLogin();

        $response = $this->postJson('/admin/verificar-disponibilidade', [
            // data_entrada omitida propositalmente
            'data_saida'     => '23/06/2026',
            'adultos'        => 2,
            'criancas_ate_7' => 0,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonStructure(['errors']);
    }

    public function test_requer_autenticacao(): void
    {
        // Sem actingAs → redireciona para login
        $response = $this->postJson('/admin/verificar-disponibilidade', $this->basePayload());

        // JSON requests sem auth retornam 401 no Laravel
        $response->assertStatus(401);
    }
}
