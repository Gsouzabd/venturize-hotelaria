<?php

namespace App\Services;

use Log;
use Exception;
use Carbon\Carbon;
use Dompdf\Dompdf;
use App\Models\Quarto;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Reserva;
use App\Models\Acompanhante;
use App\Models\DayUsePlanoPreco;
use App\Models\QuartoOpcaoExtra;
use Illuminate\Support\Facades\Auth;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class ReservaService
{
    protected $dompdf;
    protected $config;
    protected $files;
    protected $view;
    protected $showWarnings;

    public function __construct(Dompdf $dompdf, ConfigRepository $config, Filesystem $files, ViewFactory $view)
    {
        $this->dompdf = $dompdf;
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;

        $this->showWarnings = $this->config->get('dompdf.show_warnings', false);
    }
    
    public function criarOuAtualizarReserva(array $data): Array
    {
        $reservas = [];
        // dd($data);

        $isDayUse = isset($data['tipo_reserva']) && $data['tipo_reserva'] === 'DAY_USE';

        $reserva_site = $data['reserva_site'] ?? false;
        if($reserva_site && !isset($data['is_edit'])) {
            $data = $this->gerarCartSerializedReservaSite($data);
        }

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
        // dd($data);

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
                    'estrangeiro' => 'Não', // ou outro valor apropriado
                    'cep' => $data['cep'],
                    'endereco' => $data['endereco'],
                    'cidade' => $data['cidade'],
                    'estado' => $data['estado'],
                    'pais' => $data['pais'],
                    'numero' => $data['numero'],
                    'bairro' => $data['bairro'],
                ]
            );
        }

        // Day Use sem quartos: montar um único bloco a partir dos dados do formulário
        if ($isDayUse && (empty($data['quartos']) || !is_array($data['quartos']))) {
            $data['quartos'] = [
                'day_use' => [
                    'data_checkin' => $data['data_entrada'] ?? null,
                    'data_checkout' => $data['data_saida'] ?? null,
                    'adultos' => $data['adultos'] ?? 1,
                    'criancas_ate_7' => $data['criancas_ate_7'] ?? 0,
                    'criancas_mais_7' => $data['criancas_mais_7'] ?? 0,
                    'total' => $data['valor_total'] ?? 0,
                    'reserva_id' => $data['id'] ?? $data['reserva_id'] ?? null,
                ],
            ];
        }

        try {
            foreach ($data['quartos'] as $quartoId => $quartoData) {

                $quartoCartSerialized = null;
                if (!empty($data['cart_serialized'])) {
                    $cartSerialized = json_decode($data['cart_serialized'], true);

                    // Tratar o cart serialized para encontrar do quarto/reserva atual
                    // que iremos criar
                    foreach ($cartSerialized as $quarto) {
                        if (isset($quarto['quartoId']) && $quarto['quartoId'] == $quartoId) {
                            $quartoCartSerialized = json_encode($quarto);
                            break;
                        }
                    }
                }

                // dd($quartoData);
                // dd($quartoCartSerialized);

                // Buscar ou criar o cliente responsável pelo quarto
                $clienteResponsavel = null;
        
                if (!empty($quartoData['responsavel_cpf']) && !empty($quartoData['responsavel_nome'])) {
                    $clienteResponsavel = Cliente::firstOrCreate(
                        ['cpf' => $quartoData['responsavel_cpf']],
                        ['nome' => $quartoData['responsavel_nome']],
                        ['cep' => $quartoData['cep_responsavel'] ?? null],
                        ['endereco' => $quartoData['endereco_responsavel'] ?? null],
                        ['cidade' => $quartoData['cidade_responsavel'] ?? null],
                        ['estado' => $quartoData['estado_responsavel'] ?? null],
                        ['pais' => $quartoData['pais_responsavel'] ?? null],
                        ['numero' => $quartoData['numero_responsavel'] ?? null],
                        ['bairro' => $quartoData['bairro_responsavel'] ?? null],
                    );
                }
        
                // dd($quartoData);

                $dataCheckin = isset($quartoData['data_checkin']) ? $quartoData['data_checkin'] : (isset($quartoData['dataCheckin']) ? $quartoData['dataCheckin'] : null);
                $dataCheckout = isset($quartoData['data_checkout']) ? $quartoData['data_checkout'] : (isset($quartoData['dataCheckout']) ? $quartoData['dataCheckout'] : null);                
                // Preparar os dados da reserva
                $reservaData = [
                    'tipo_reserva' => $data['tipo_reserva'] ?? null,
                    'tipo_solicitante' => $data['tipo_solicitante'],
                    'situacao_reserva' => $data['situacao_reserva'] ?? 'PRÉ RESERVA',
                    'data_checkin' => $this->formatCheckinDate($dataCheckin),
                    'data_checkout' => $this->formatCheckoutDate($dataCheckout),
                    'estrangeiro' => 'Não',
                    'cliente_solicitante_id' => $clienteSolicitante->id,
                    'cliente_responsavel_id' => $clienteResponsavel ? $clienteResponsavel->id : null,
                    'quarto_id' => $isDayUse ? null : $quartoId,
                    'adultos' => $quartoData['adultos'],
                    'criancas_ate_7' => $quartoData['criancas_ate_7'],
                    'criancas_mais_7' => $quartoData['criancas_mais_7'],
                    'tipo_acomodacao' => $quartoData['tipo_acomodacao'] ?? null,
                    'usuario_operador_id' => Auth::id() ?? 2,
                    'email_solicitante' => $data['email'],
                    'celular' => $data['celular'],
                    'email_faturamento' => $data['email_faturamento'] ?? null,
                    'empresa_faturamento_id' => $data['empresa_faturamento_id'] ?? null,
                    'empresa_solicitante_id' => $data['empresa_solicitante_id'] ?? null,
                    'observacoes' => $data['observacoes'],
                    'observacoes_internas' => $data['observacoes_internas'],
                    'cart_serialized' => $quartoCartSerialized ?? null,
                    'com_cafe' => $data['com_cafe'] ?? false,
                    'valor_cafe' => null,
                    'total' => $quartoData['total'] ?? 0,
                    'created_at' => Carbon::now('America/Sao_Paulo'),

                ];

                // Cálculo automático de valor para Day Use (fallback caso o total não venha do front)
                if ($isDayUse && (empty($reservaData['total']) || floatval($reservaData['total']) <= 0)) {
                    $dataUso = $data['data_entrada'] ?? $dataCheckin;
                    $reservaData['total'] = $this->calcularPrecoDayUse(
                        $dataUso,
                        intval($reservaData['adultos']),
                        intval($reservaData['criancas_ate_7']),
                        intval($reservaData['criancas_mais_7']),
                        (bool) $reservaData['com_cafe'],
                        $reservaData
                    );
                }


                // dd($reservaData);
        
                // Criar ou atualizar a reserva
                if (isset($quartoData['reserva_id']) && $quartoData['reserva_id'] != '') {
                    $reserva = Reserva::findOrFail($quartoData['reserva_id']);
                    $reserva->update($reservaData);
                } else {
                    $reserva = Reserva::create($reservaData);
                }

                // dd($reserva);


                // Extrair dados dos acompanhantes e associá-los à reserva
                if (isset($quartoData['acompanhantes'])) {
                    // Obter a lista atual de acompanhantes da reserva
                    $acompanhantesAtuais = Acompanhante::where('reserva_id', $reserva->id)->get();
                    $acompanhantesAtuaisMap = $acompanhantesAtuais->keyBy(function ($item) {
                        return $item->cpf . '-' . $item->tipo;
                    });

                    // dd($acompanhantesAtuaisMap);

        
                    foreach ($quartoData['acompanhantes'] as $tipo => $listaAcompanhantes) {
                        foreach ($listaAcompanhantes as $index => $acompanhanteData) {
                            if (!empty($acompanhanteData['data_nascimento'])) {
                                $acompanhanteData['data_nascimento'] = parseDateVenturize($acompanhanteData['data_nascimento']);
                            }
                            $cliente = null;

                            if (strtolower($tipo) === 'adulto') {
                                if (!empty($acompanhanteData['cpf']) && !empty($acompanhanteData['nome'])) {
                                    $cliente = Cliente::updateOrCreate(
                                        ['cpf' => $acompanhanteData['cpf']],
                                        [
                                            'nome' => $acompanhanteData['nome'],
                                            'data_nascimento' => $acompanhanteData['data_nascimento'],
                                            'telefone' => $acompanhanteData['telefone'] ?? null,
                                            'email' => $acompanhanteData['email'] ?? null,
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
                                    'telefone' => $acompanhanteData['telefone'] ?? null,
                                    'email' => $acompanhanteData['email'] ?? null,
                                ]
                            );

                            // dd($acompanhante);
        
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

    public function gerarFichaNacional($id)
    {
        // Buscar os dados da reserva com base no ID (com acompanhantes, quarto e cliente responsável)
        $reserva = Reserva::with(['acompanhantes', 'quarto', 'clienteResponsavel', 'checkIn'])->findOrFail($id);
        $data = [
            'reserva' => $reserva,
        ];

        $html = $this->view->make('pdf.ficha-nacional', $data)->render();

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        if ($this->showWarnings) {
            $warnings = $this->dompdf->getWarnings();
            foreach ($warnings as $warning) {
                \Log::warning($warning);
            }
        }

        return $this->dompdf->stream('ficha_nacional.pdf');
    }

    protected function gerarCartSerializedReservaSite(array $data)
    {
        $cart = [];

        foreach ($data['quartos'] as $quarto) {



            $precosDiarios = $this->tratarPrecosDiariosSite($quarto['data_checkin'], $quarto['data_checkout'], $quarto['total']);

            $dataQuarto = [
                'quartoId' => $quarto['quarto_id'],
                'quartoNumero' => $quarto['numero'] ,
                'quartoAndar' => $quarto['andar'] ,
                'quartoClassificacao' => $quarto['classificacao'] ,
                'nome' => $quarto['responsavel_nome'] ?? '',
                'cpf' => $quarto['responsavel_cpf'] ?? '',
                'criancas_ate_7' => $quarto['criancas_ate_7'] ?? 0,
                'criancas_mais_7' => $quarto['criancas_mais_7'] ?? 0,
                'adultos' => $quarto['adultos'] ?? 1,
                'dataCheckin' => $quarto['data_checkin'] ?? '',
                'dataCheckout' => $quarto['data_checkout'] ?? '',
                'precosDiarios' => $precosDiarios,
                'total' => $quarto['total'] ?? 0,
                'reservaId' => '',
            ];
            $cart[] = $dataQuarto;
            $quartos[$quarto['quarto_id']] = $dataQuarto;
        }
        // dd($cart);
        $data['cart_serialized'] = json_encode($cart);
        $data['quartos'] = $quartos;

        return $data;
    }

    public function encontrarQuartoDisponível($dataEntrada, $dataSaida, $tipoQuarto)
    {
        $tipoQuarto = strpos($tipoQuarto, 'Camará') !== false ? 'Camará' : 'Embaúba';
        $quartosQuery = Quarto::query();
        $dataEntrada = parseDateVenturize($dataEntrada);
        $dataSaida = parseDateVenturize($dataSaida);
        // var_dump($dataEntrada);
        // var_dump($dataSaida);

        $quarto = $quartosQuery->where('classificacao', $tipoQuarto)
            ->whereDoesntHave('reservas', function ($query) use ($dataEntrada, $dataSaida) {
                $query->where(function ($query) use ($dataEntrada, $dataSaida) {
                    // Verifica se a reserva está dentro do intervalo
                    $query->whereBetween('data_checkin', [$dataEntrada, $dataSaida]);
                })
                ->where('situacao_reserva', '!=', 'CANCELADA');
            })
            ->first();

            // dd($quarto);

        return $quarto;
    }

    public function tratarPrecosDiariosSite($dataEntrada, $dataSaida, $total)
    {
        $precosDiarios = [];
        $dataEntrada = Carbon::createFromFormat('d/m/Y', $dataEntrada);
        $dataSaida = Carbon::createFromFormat('d/m/Y', $dataSaida);
    
        $dias = $dataEntrada->diffInDays($dataSaida); 
        $precoDiaria = $total / $dias; 
        // formato 0.00
        $precoDiaria = number_format($precoDiaria, 2, '.', '');

    
        for ($i = 0; $i < $dias; $i++) {
            $precosDiarios[] = [
                'data' => $dataEntrada->copy()->addDays($i)->format('d/m/Y'),
                'preco' => $precoDiaria, // Replace with actual price logic if needed
            ];
        }
    
        return $precosDiarios;
    }
    
    /**
     * Calcula o preço de um Day Use para uma data específica.
     *
     * @param string $dataUso        Data no formato d/m/Y ou d-m-Y
     * @param int    $adultos
     * @param int    $criancasAte7
     * @param int    $criancasMais7
     * @param bool   $comCafe
     * @param array  $reservaDataRef Referência opcional para preencher valor_cafe
     *
     * @return float
     */
    protected function calcularPrecoDayUse(
        string $dataUso,
        int $adultos,
        int $criancasAte7,
        int $criancasMais7,
        bool $comCafe,
        array &$reservaDataRef = []
    ): float {
        // Normalizar formato da data
        $formatos = ['d/m/Y', 'd-m-Y', 'Y-m-d'];
        $data = null;
        foreach ($formatos as $formato) {
            try {
                $data = Carbon::createFromFormat($formato, $dataUso);
                break;
            } catch (Exception $e) {
                continue;
            }
        }

        if (!$data) {
            throw new Exception('Data de Day Use inválida: ' . $dataUso);
        }

        // Buscar plano vigente (período específico ou plano default)
        $plano = DayUsePlanoPreco::vigente($data, $data)->orderBy('data_inicio', 'desc')->first();

        if (!$plano) {
            // Fallback: tenta pegar qualquer plano default
            $plano = DayUsePlanoPreco::where('is_default', true)->first();
        }

        if (!$plano) {
            throw new Exception('Nenhum plano de Day Use configurado.');
        }

        $precoBase = $plano->getPrecoDia($data) ?? 0.0;

        // Valores extras para crianças (Day Use)
        // Opcionalmente podemos manter até 7 anos grátis para Day Use
        $precoCriancaAte7 = QuartoOpcaoExtra::where('nome', 'Criança (Até 7 anos)')->value('preco') ?? 0.0;

        // Criança 4–12 anos (Day Use) – opção específica; se não existir, usa preço de 07 à 12 anos como fallback
        $precoCrianca4a12 = QuartoOpcaoExtra::where('nome', 'Criança (4 à 12 anos - Day Use)')->value('preco');
        if (is_null($precoCrianca4a12)) {
            $precoCrianca4a12 = QuartoOpcaoExtra::where('nome', 'Criança (07 à 12 anos)')->value('preco') ?? 0.0;
        }

        // Para Day Use, consideramos criancas_mais_7 como faixa 4–12 pagante
        $totalCriancasAte7Pagas = 0; // até 7 anos permanece gratuito aqui
        $valorCriancas = ($totalCriancasAte7Pagas * $precoCriancaAte7) + ($criancasMais7 * $precoCrianca4a12);

        // Café da manhã – valor por pessoa pagante (adultos + crianças pagantes)
        $valorCafe = 0.0;
        if ($comCafe) {
            $pessoasPagantesCafe = max(0, $adultos) + max(0, $criancasMais7);
            $valorCafe = $plano->getPrecoCafeDia($data) * $pessoasPagantesCafe;
        }

        if (array_key_exists('valor_cafe', $reservaDataRef)) {
            $reservaDataRef['valor_cafe'] = $valorCafe;
        }

        return floatval($precoBase + $valorCriancas + $valorCafe);
    }

    /**
     * Calcula o total de uma reserva Day Use (para exibição no front ou API).
     */
    public function calcularTotalDayUse(string $dataUso, int $adultos, int $criancasAte7, int $criancasMais7, bool $comCafe): float
    {
        $ref = [];
        return $this->calcularPrecoDayUse($dataUso, $adultos, $criancasAte7, $criancasMais7, $comCafe, $ref);
    }
}