<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Reserva;
use Illuminate\Support\Facades\Auth;

class ReservaService
{
    public function criarOuAtualizarReserva(array $data)
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
        $clienteSolicitante = Cliente::firstOrCreate(
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
            $clienteResponsavel = Cliente::firstOrCreate(
                ['cpf' => $quartoData['responsavel_cpf']],
                ['nome' => $quartoData['responsavel_nome']]
            );

            // Preparar os dados da reserva
            $reservaData = [
                'tipo_reserva' => $data['tipo_reserva'],
                'situacao_reserva' => $data['situacao_reserva'] ?? 'PRÉ RESERVA', // ou outro valor padrão
                'data_checkin' => Carbon::createFromFormat('d/m/Y', $data['data_entrada'])->format('Y-m-d'),
                'data_checkout' => Carbon::createFromFormat('d/m/Y', $data['data_saida'])->format('Y-m-d'),
               
                'cliente_solicitante_id' => $clienteSolicitante->id,
                'cliente_responsavel_id' => $clienteResponsavel->id,
                'quarto_id' => $quartoId,
                'usuario_operador_id' => Auth::id(), // ou outro valor apropriado
                'email_solicitante' => $data['email'],
                'celular' => $data['celular'],
                'email_faturamento' => $data['email_faturamento'],
                'empresa_faturamento_id' => $data['empresa_faturamento_id'] ?? null,
                'empresa_solicitante_id' => $data['empresa_solicitante_id'] ?? null,
                'observacoes' => $data['observacoes'],
                'observacoes_internas' => $data['observacoes_internas'],
            ];

            // Criar ou atualizar a reserva
            if (isset($data['id'])) {
                Reserva::findOrFail($data['id'])->update($reservaData);
            } else {
                Reserva::create($reservaData);
            }
        }
    }
}