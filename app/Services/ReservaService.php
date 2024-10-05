<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;

class ReservaService
{
    public function criarOuAtualizarReserva(array $data): Reserva
    {
        // Verificar e processar CNPJ do solicitante
        if (!empty($data['cnpj_solicitante'])) {
            $empresaSolicitante = Empresa::where('cnpj', $data['cnpj_solicitante'])->first();

            if (!$empresaSolicitante) {
                $empresaSolicitante = Empresa::create([
                    'nome_fantasia' => $data['nome_fantasia_solicitante'] ?? '',
                    'razao_social' => $data['razao_social'] ?? '',
                    'cnpj' => $data['cnpj_solicitante'],
                    'inscricao_estadual' => $data['inscricao_estadual'] ?? '',
                    'email' => $data['email_solicitante'] ?? '',
                    'telefone' => $data['telefone_solicitante'] ?? '',
                ]);
            }

            $data['empresa_solicitante_id'] = $empresaSolicitante->id;
        }

        // Verificar e processar CNPJ de faturamento
        if (!empty($data['cnpj_faturamento'])) {
            $empresaFaturamento = Empresa::where('cnpj', $data['cnpj_faturamento'])->first();

            if (!$empresaFaturamento) {
                $empresaFaturamento = Empresa::create([
                    'nome_fantasia' => $data['nome_fantasia_faturamento'] ?? '',
                    'razao_social' => $data['razao_social'] ?? '',
                    'cnpj' => $data['cnpj_faturamento'],
                    'cep' => $data['cep_faturamento'] ?? '',
                    'inscricao_estadual' => $data['inscricao_estadual'] ?? '',
                    'email' => $data['email_empresa_faturamento'] ?? '',
                    'telefone' => $data['telefone_faturamento'] ?? '',
                ]);
            }

            $data['empresa_faturamento_id'] = $empresaFaturamento->id;
        }

        if (!empty($data['data_nascimento'])) {
            $data['data_nascimento'] = Carbon::createFromFormat('d/m/Y', $data['data_nascimento'])->format('Y-m-d');
        }

        // Buscar ou criar o cliente solicitante
        $clienteSolicitante = Cliente::updateOrCreate(
            ['cpf' => $data['cpf']],
            [
                'nome' => $data['nome'],
                'email' => $data['email'],
                'telefone' => $data['telefone'],
                'celular' => $data['celular'],
                'data_nascimento' => $data['data_nascimento'],
                'rg' => $data['rg'],
                'estrangeiro' => 'Não' // ou outro valor apropriado
            ]
        );

        // Iterar sobre cada quarto e criar uma reserva
        foreach ($data['quartos'] as $quartoId => $quartoData) {
            // Buscar ou criar o cliente responsável pelo quarto
            $clienteResponsavel = null;

            if (!empty($quartoData['responsavel_cpf']) && !empty($quartoData['responsavel_nome'])) {
                $clienteResponsavel = Cliente::firstOrCreate(
                    ['cpf' => $quartoData['responsavel_cpf']],
                    ['nome' => $quartoData['responsavel_nome']]
                );
            }

            $dataCheckin = Carbon::createFromFormat('d-m-Y', $quartoData['data_checkin'])
                ->setTime(14, 0)
                ->format('Y-m-d H:i:s');
            $dataCheckout = Carbon::createFromFormat('d-m-Y', $quartoData['data_checkout'])
                ->setTime(12, 0)
                ->format('Y-m-d H:i:s');

            // Preparar os dados da reserva
            $reservaData = [
                'tipo_reserva' => $data['tipo_reserva'] ?? null,
                'tipo_solicitante' => $data['tipo_solicitante'],
                'situacao_reserva' => $data['situacao_reserva'] ?? 'PRÉ RESERVA', // ou outro valor padrão
                'data_checkin' => $dataCheckin,
                'data_checkout' => $dataCheckout,
                'estrangeiro' => 'Não',
                'cliente_solicitante_id' => $clienteSolicitante->id,
                'cliente_responsavel_id' => $clienteResponsavel ? $clienteResponsavel->id : null,
                'quarto_id' => $quartoId,
                'adultos' => $quartoData['adultos'],
                'criancas_ate_7' => $quartoData['criancas_ate_7'],
                'criancas_mais_7' => $quartoData['criancas_mais_7'],
                'tipo_acomodacao' => $quartoData['tipo_acomodacao'],
                'usuario_operador_id' => Auth::id(), // ou outro valor apropriado
                'email_solicitante' => $data['email'],
                'celular' => $data['celular'],
                'email_faturamento' => $data['email_faturamento'] ?? null,
                'empresa_faturamento_id' => $data['empresa_faturamento_id'] ?? null,
                'empresa_solicitante_id' => $data['empresa_solicitante_id'] ?? null,
                'observacoes' => $data['observacoes'],
                'observacoes_internas' => $data['observacoes_internas'],
            ];

            // Criar ou atualizar a reserva
            if (isset($data['reserva_id'])) {
                $reserva = Reserva::findOrFail($data['reserva_id']);
                $reserva->update($reservaData);
            } else {
                $reserva = Reserva::create($reservaData);
            }

            return $reserva;
        }
    }
}