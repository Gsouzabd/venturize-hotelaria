<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Quarto;
use App\Models\Usuario;

class ReservaSeeder extends Seeder
{
    public function run()
    {
        // Cria um cliente
        $cliente = Cliente::create([
            'tipo' => 'Pessoa Física',
            'estrangeiro' => 'Não',
            'sexo' => 'M',
            'nome' => 'João da Silva',
            'data_nascimento' => '1985-01-01',
            'cpf' => '123.456.389-00',
            'email' => 'joao@example.com',
            'celular' => '(11) 91234-5678',
            'cidade' => 'São Paulo',
            'endereco' => 'Rua Exemplo, 123',
            'numero' => '123',
            'bairro' => 'Bairro Exemplo',
            'pais' => 'Brasil',
        ]);

        // Cria um quarto
        $quarto = Quarto::create([
            'andar' => 'Térreo',
            'numero' => 101,
            'ramal' => '101',
            'posicao_quarto' => 'Frente',
            'quantidade_cama_casal' => 1,
            'quantidade_cama_solteiro' => 1,
            'classificacao' => 'Camará',
            'acessibilidade' => 'Não',
            'inativo' => 'Não',
        ]);

        // Cria um operador (usuário)
        $operador = Usuario::create([
            'nome' => 'Operador 1',
            'email' => 'operador1@example.com',
            'senha' => bcrypt('password'),
            'tipo' => 'operador',
            'fl_ativo' => true,
        ]);

        // Cria uma reserva associada ao cliente, quarto e operador
        Reserva::create([
            'tipo_reserva' => 'INDIVIDUAL',
            'situacao_reserva' => 'PRÉ RESERVA',
            'previsao_chegada' => now(),
            'previsao_saida' => now()->addDays(3),
            'data_checkin' => now(), // Campo obrigatório
            'data_checkout' => now()->addDays(3), // Campo obrigatório
            'cliente_id' => $cliente->id, // Solicitante
            'quarto_id' => $quarto->id, // Quarto reservado
            'usuario_operador_id' => $operador->id, // Operador que fez a reserva
            'email_solicitante' => $cliente->email,
            'celular' => $cliente->celular,
            'email_faturamento' => 'financeiro@example.com',
            'empresa_faturamento_id' => $cliente->id, // Pode ser o mesmo cliente ou uma empresa
            'empresa_solicitante_id' => null,
            'observacoes' => 'Cliente pediu uma cama extra.',
            'observacoes_internas' => 'Não colocar no voucher.',
        ]);
    }
}
