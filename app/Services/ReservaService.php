<?php

namespace App\Services;

use Log;
use Exception;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Reserva;
use App\Models\Acompanhante;
use Illuminate\Support\Facades\Auth;

class ReservaService
{
    public function criarOuAtualizarReserva(array $data): Array
    {
        $reservas = [];
        // dd($data);

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
        if (!empty($data['cpf']) && !empty($data['nome'])) {
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
        }
 

        try {
            foreach ($data['quartos'] as $quartoId => $quartoData) {
                $cartSerialized = json_decode($data['cart_serialized'], true);

                $quartoCartSerialized = null;
                foreach ($cartSerialized as $quarto) {
                    // var_dump($quartoId);
                    // dd($quarto);
                    if ($quarto['quartoId'] == $quartoId) {
                        $quartoCartSerialized = json_encode($quarto);
                        break;
                    }
                }
                                // Buscar ou criar o cliente responsável pelo quarto
                $clienteResponsavel = null;
        
                if (!empty($quartoData['responsavel_cpf']) && !empty($quartoData['responsavel_nome'])) {
                    $clienteResponsavel = Cliente::firstOrCreate(
                        ['cpf' => $quartoData['responsavel_cpf']],
                        ['nome' => $quartoData['responsavel_nome']]
                    );
                }
        
                // Preparar os dados da reserva
                $reservaData = [
                    'tipo_reserva' => $data['tipo_reserva'] ?? null,
                    'tipo_solicitante' => $data['tipo_solicitante'],
                    'situacao_reserva' => $data['situacao_reserva'] ?? 'PRÉ RESERVA',
                    'data_checkin' => $this->formatCheckinDate($quartoData['data_checkin']),
                    'data_checkout' => $this->formatCheckoutDate($quartoData['data_checkout']),
                    'estrangeiro' => 'Não',
                    'cliente_solicitante_id' => $clienteSolicitante->id,
                    'cliente_responsavel_id' => $clienteResponsavel ? $clienteResponsavel->id : null,
                    'quarto_id' => $quartoId,
                    'adultos' => $quartoData['adultos'],
                    'criancas_ate_7' => $quartoData['criancas_ate_7'],
                    'criancas_mais_7' => $quartoData['criancas_mais_7'],
                    'tipo_acomodacao' => $quartoData['tipo_acomodacao'] ?? null,
                    'usuario_operador_id' => Auth::id(),
                    'email_solicitante' => $data['email'],
                    'celular' => $data['celular'],
                    'email_faturamento' => $data['email_faturamento'] ?? null,
                    'empresa_faturamento_id' => $data['empresa_faturamento_id'] ?? null,
                    'empresa_solicitante_id' => $data['empresa_solicitante_id'] ?? null,
                    'observacoes' => $data['observacoes'],
                    'observacoes_internas' => $data['observacoes_internas'],
                    'cart_serialized' => $quartoCartSerialized ?? null,
                    'total' => $quartoData['total'] ?? 0,
                ];
        
                // Criar ou atualizar a reserva
                if (isset($quartoData['reserva_id']) && $quartoData['reserva_id'] != '') {
                    $reserva = Reserva::findOrFail($quartoData['reserva_id']);
                    $reserva->update($reservaData);
                } else {
                    $reserva = Reserva::create($reservaData);
                }

        
                // Extrair dados dos acompanhantes e associá-los à reserva
                if (isset($quartoData['acompanhantes'])) {
                    // Obter a lista atual de acompanhantes da reserva
                    $acompanhantesAtuais = Acompanhante::where('reserva_id', $reserva->id)->get();
                    $acompanhantesAtuaisMap = $acompanhantesAtuais->keyBy(function ($item) {
                        return $item->cpf . '-' . $item->tipo;
                    });
        
                    foreach ($quartoData['acompanhantes'] as $tipo => $listaAcompanhantes) {
                        foreach ($listaAcompanhantes as $index => $acompanhanteData) {
                            $cliente = null;
                            if (strtolower($tipo) === 'adulto') {
                                if (!empty($acompanhanteData['cpf']) && !empty($acompanhanteData['nome'])) {
                                    $cliente = Cliente::updateOrCreate(
                                        ['cpf' => $acompanhanteData['cpf']],
                                        [
                                            'nome' => $acompanhanteData['nome'],
                                            'data_nascimento' => $acompanhanteData['data_nascimento'],
                                        ]
                                    );
                                }
                            }
        
                            $acompanhante = Acompanhante::updateOrCreate(
                                [
                                    'reserva_id' => $reserva->id,
                                    'cpf' => $acompanhanteData['cpf'],
                                    'tipo' => $tipo,
                                ],
                                [
                                    'cliente_id' => $cliente->id ?? null,
                                    'nome' => $acompanhanteData['nome'],
                                    'data_nascimento' => $acompanhanteData['data_nascimento'] ?? null,
                                ]
                            );
        
                            // Remover o acompanhante atualizado da lista de acompanhantes atuais
                            $acompanhantesAtuaisMap->forget($acompanhanteData['cpf'] . '-' . $tipo);
                        }
                    }
        
                    // Excluir acompanhantes que não estão presentes na nova lista
                    foreach ($acompanhantesAtuaisMap as $acompanhante) {
                        $acompanhante->delete();
                    }
                }
        
                $reservas[] = $reserva;
            }
        } catch (\Exception $e) {
            dd('Error processing reserva: ' . $e->getMessage());
        
            return redirect()->route('admin.reservas.index')->with('error', 'An error occurred while processing the reserva.');
        }
        // dd($reservas);
        
        return $reservas;
    }

    private function formatDate($date, $formats, $time)
    {
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)
                    ->setTime($time[0], $time[1])
                    ->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Continuar tentando com o próximo formato
            }
        }
        throw new \Exception("Erro ao formatar a data: Formato inválido.");
    }

    private function formatCheckinDate($date)
    {
        $formats = ['d-m-Y', 'd/m/Y'];
        return $this->formatDate($date, $formats, [14, 0]);
    }

    private function formatCheckoutDate($date)
    {
        $formats = ['d-m-Y', 'd/m/Y'];
        return $this->formatDate($date, $formats, [12, 0]);
    }
}