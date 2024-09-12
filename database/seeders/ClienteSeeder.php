<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        Cliente::create([
            'tipo' => 'Pessoa Física',
            'estrangeiro' => 'Não',
            'sexo' => 'M',
            'nome' => 'Gabriel Souza',
            'data_nascimento' => '1990-05-12',
            'cpf' => '123.456.789-00',
            'rg' => '12345678',
            'passaporte' => null,
            'orgao_expedidor' => 'SSP',
            'estado_civil' => 'Solteiro',
            'inscricao_estadual_pf' => '1234567890',
            'cep' => '12345-678',
            'cidade' => 'Cidade Exemplo',
            'endereco' => 'Rua Exemplo, 123',
            'numero' => '123',
            'complemento' => 'Apto 101',
            'bairro' => 'Bairro Exemplo',
            'pais' => 'Brasil',
            'email' => 'gabriel@example.com',
            'email_alternativo' => 'gabriel.alternativo@example.com',
            'telefone' => '(11) 1234-5678',
            'celular' => '(11) 91234-5678',
            'profissao' => 'Desenvolvedor',
        ]);
    }
}
