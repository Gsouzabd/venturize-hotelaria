<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estoque;
use App\Models\LocalEstoque;
use App\Models\Pagamento;
use App\Models\Produto;
use App\Models\Quarto;
use App\Models\Reserva;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RelatorioController extends Controller
{
    public function estoque(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosEstoque($request);
        $estoques = $this->colecaoEstoqueParaRelatorio($filters);
        $locaisEstoque = LocalEstoque::orderBy('nome')->get();

        return view('admin.relatorios.estoque', compact('estoques', 'filters', 'locaisEstoque'));
    }

    public function exportarEstoque(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosEstoque($request);
        $estoques = $this->colecaoEstoqueParaRelatorio($filters);

        $unidades = Produto::UNIDADES;
        $dadosExcel = [];
        $dadosExcel[] = ['Relatório de estoque de produtos'];
        $dadosExcel[] = ['Gerado em: ' . Carbon::now()->format('d/m/Y H:i')];
        $dadosExcel[] = [];
        $dadosExcel[] = [
            'ID',
            'Produto',
            'Código interno',
            'Categoria',
            'Local',
            'Quantidade',
            'Unidade',
            'Estoque mínimo',
            'Estoque máximo',
        ];

        foreach ($estoques as $row) {
            $p = $row->produto;
            if (!$p) {
                continue;
            }
            $dadosExcel[] = [
                $row->id,
                $p->descricao,
                $p->codigo_interno ?? '',
                $p->categoria->nome ?? '',
                $row->localEstoque->nome ?? '',
                $row->quantidade,
                $unidades[$p->unidade] ?? $p->unidade,
                $p->estoque_minimo ?? '',
                $p->estoque_maximo ?? '',
            ];
        }

        $filename = 'relatorio_estoque_' . Carbon::now()->format('Y-m-d_His') . '.xls';
        $tempFile = ExcelExportService::criarExcel($dadosExcel, $filename, 'Relatório de estoque');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }

    public function exportarEstoquePdf(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosEstoque($request);
        $estoques = $this->colecaoEstoqueParaRelatorio($filters);
        $unidades = Produto::UNIDADES;
        $geradoEm = Carbon::now()->format('d/m/Y H:i');

        $nomeLocalFiltro = null;
        if ($filters['local_estoque_id'] !== '') {
            $nomeLocalFiltro = LocalEstoque::find($filters['local_estoque_id'])?->nome;
        }

        $html = view('pdf.relatorio_estoque', compact(
            'estoques',
            'unidades',
            'filters',
            'geradoEm',
            'nomeLocalFiltro'
        ))->render();

        $filename = 'relatorio_estoque_' . Carbon::now()->format('Y-m-d') . '.pdf';

        return $this->respostaPdf($html, $filename, 'landscape');
    }

    public function cafe(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $request->all();
        $filters['data'] ??= Carbon::now()->format('d/m/Y');

        $dataRef = Carbon::createFromFormat('d/m/Y', $filters['data'])->startOfDay();
        $reservas = $this->queryReservasCafe($dataRef)->get();
        $linhas = $this->montarLinhasCafe($reservas);

        $totalAdultos  = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Adulto'));
        $totalCriancas = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Criança'));
        $totalGeral    = count($linhas);

        return view('admin.relatorios.cafe', compact('linhas', 'filters', 'totalAdultos', 'totalCriancas', 'totalGeral'));
    }

    public function exportarCafe(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $request->all();
        $filters['data'] ??= Carbon::now()->format('d/m/Y');

        $dataRef = Carbon::createFromFormat('d/m/Y', $filters['data'])->startOfDay();
        $reservas = $this->queryReservasCafe($dataRef)->get();
        $linhas = $this->montarLinhasCafe($reservas);

        $dadosExcel = [];
        $totalAdultos  = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Adulto'));
        $totalCriancas = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Criança'));
        $totalGeral    = count($linhas);

        $dadosExcel[] = ['Listagem de café — hóspedes por quarto'];
        $dadosExcel[] = ['Data de referência: ' . $dataRef->format('d/m/Y')];
        $dadosExcel[] = [];
        $dadosExcel[] = ['Quarto', 'Tipo', 'Adulto/Criança', 'Qtd./Quarto', 'Nome', 'CPF'];

        foreach ($linhas as $linha) {
            $dadosExcel[] = [
                $linha['quarto'],
                $linha['tipo'],
                $linha['tipo_pessoa'],
                $linha['qtd_pessoas_quarto'],
                $linha['nome'],
                $linha['cpf'],
            ];
        }

        $dadosExcel[] = [];
        $dadosExcel[] = ['TOTAL ADULTOS', $totalAdultos, '', '', '', ''];
        $dadosExcel[] = ['TOTAL CRIANÇAS', $totalCriancas, '', '', '', ''];
        $dadosExcel[] = ['TOTAL GERAL', $totalGeral, '', '', '', ''];

        $filename = 'listagem_cafe_' . $dataRef->format('Y-m-d') . '.xls';
        $tempFile = ExcelExportService::criarExcel($dadosExcel, $filename, 'Listagem de café');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }

    public function exportarCafePdf(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $request->all();
        $filters['data'] ??= Carbon::now()->format('d/m/Y');

        $dataRef = Carbon::createFromFormat('d/m/Y', $filters['data'])->startOfDay();
        $reservas = $this->queryReservasCafe($dataRef)->get();
        $linhas = $this->montarLinhasCafe($reservas);

        $totalAdultos  = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Adulto'));
        $totalCriancas = count(array_filter($linhas, fn ($l) => $l['tipo_pessoa'] === 'Criança'));
        $totalGeral    = count($linhas);

        $dataReferencia = $dataRef->format('d/m/Y');
        $html = view('pdf.relatorio_cafe', compact('linhas', 'dataReferencia', 'totalAdultos', 'totalCriancas', 'totalGeral'))->render();

        $filename = 'listagem_cafe_' . $dataRef->format('Y-m-d') . '.pdf';

        return $this->respostaPdf($html, $filename, 'portrait');
    }

    public function pagamentos(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosPagamentos($request->all());
        $reservas = $this->buildPagamentosQuery($filters)->get();
        $tiposPagamento = Pagamento::METODOS_PAGAMENTO;
        $tiposQuarto = Quarto::distinct()->orderBy('classificacao')->pluck('classificacao')->filter()->values();

        return view('admin.relatorios.pagamentos', compact('reservas', 'filters', 'tiposPagamento', 'tiposQuarto'));
    }

    public function exportarPagamentos(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosPagamentos($request->all());
        $reservas = $this->buildPagamentosQuery($filters)->get();

        $dadosExcel = [];
        $dadosExcel[] = ['Relatório de Pagamentos de Reservas'];
        $dadosExcel[] = ['Gerado em: ' . Carbon::now()->format('d/m/Y H:i')];
        $dadosExcel[] = [];
        $dadosExcel[] = ['ID Reserva', 'Hóspede', 'Quarto', 'Tipo Quarto', 'Check-in', 'Check-out', 'Tipo Pagamento', 'Valor Pago', 'Status'];

        $totalValor = 0;
        foreach ($reservas as $reserva) {
            $cliente = $reserva->clienteResponsavel ?? $reserva->clienteSolicitante;
            $pagamento = $reserva->pagamentos->first();
            $metodosLabel = $this->extrairMetodosPagamento($pagamento);
            $valorPago = $reserva->pagamentos->sum('valor_pago');
            $totalValor += $valorPago;

            $dadosExcel[] = [
                $reserva->id,
                $cliente->nome ?? '—',
                $reserva->quarto->numero ?? '—',
                $reserva->quarto->classificacao ?? '—',
                $reserva->data_checkin ? Carbon::parse($reserva->data_checkin)->format('d/m/Y') : '—',
                $reserva->data_checkout ? Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '—',
                $metodosLabel ?: '—',
                $valorPago,
                Pagamento::STATUS_PAGAMENTO[$pagamento->status_pagamento ?? ''] ?? ($pagamento->status_pagamento ?? '—'),
            ];
        }

        $dadosExcel[] = [];
        $dadosExcel[] = ['TOTAL', '', '', '', '', '', '', $totalValor, ''];

        $filename = 'relatorio_pagamentos_' . Carbon::now()->format('Y-m-d_His') . '.xls';
        $tempFile = ExcelExportService::criarExcel($dadosExcel, $filename, 'Relatório de Pagamentos');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }

    public function exportarPagamentosPdf(Request $request)
    {
        $this->authorize('visualizar_relatorios');

        $filters = $this->normalizarFiltrosPagamentos($request->all());
        $reservas = $this->buildPagamentosQuery($filters)->get();
        $tiposPagamento = Pagamento::METODOS_PAGAMENTO;
        $geradoEm = Carbon::now()->format('d/m/Y H:i');
        $totalValor = $reservas->sum(fn ($r) => $r->pagamentos->sum('valor_pago'));

        $html = view('pdf.relatorio_pagamentos', compact('reservas', 'filters', 'tiposPagamento', 'geradoEm', 'totalValor'))->render();

        $filename = 'relatorio_pagamentos_' . Carbon::now()->format('Y-m-d') . '.pdf';

        return $this->respostaPdf($html, $filename, 'landscape');
    }

    private function normalizarFiltrosPagamentos(array $filters): array
    {
        $filters['tipo_pagamento'] ??= null;
        $filters['tipo_quarto'] ??= null;
        $filters['nome'] ??= null;
        $filters['data_checkin_inicial'] ??= null;
        $filters['data_checkin_final'] ??= null;
        $filters['data_checkout_inicial'] ??= null;
        $filters['data_checkout_final'] ??= null;

        return $filters;
    }

    private function buildPagamentosQuery(array $filters)
    {
        $query = Reserva::with(['pagamentos', 'quarto', 'clienteResponsavel', 'clienteSolicitante'])
            ->whereHas('pagamentos');

        if (!empty($filters['tipo_pagamento'])) {
            $query->whereHas('pagamentos', fn ($q) => $q->where('valores_recebidos', 'like', '%' . $filters['tipo_pagamento'] . '%'));
        }

        if (!empty($filters['tipo_quarto'])) {
            $query->whereHas('quarto', fn ($q) => $q->where('classificacao', $filters['tipo_quarto']));
        }

        if (!empty($filters['nome'])) {
            $nome = $filters['nome'];
            $query->where(function ($q) use ($nome) {
                $q->whereHas('clienteSolicitante', fn ($sq) => $sq->where('nome', 'like', '%' . $nome . '%'))
                  ->orWhereHas('clienteResponsavel', fn ($sq) => $sq->where('nome', 'like', '%' . $nome . '%'));
            });
        }

        if (!empty($filters['data_checkin_inicial'])) {
            $query->where('data_checkin', '>=', Carbon::createFromFormat('d/m/Y', $filters['data_checkin_inicial'])->startOfDay());
        }
        if (!empty($filters['data_checkin_final'])) {
            $query->where('data_checkin', '<=', Carbon::createFromFormat('d/m/Y', $filters['data_checkin_final'])->endOfDay());
        }
        if (!empty($filters['data_checkout_inicial'])) {
            $query->where('data_checkout', '>=', Carbon::createFromFormat('d/m/Y', $filters['data_checkout_inicial'])->startOfDay());
        }
        if (!empty($filters['data_checkout_final'])) {
            $query->where('data_checkout', '<=', Carbon::createFromFormat('d/m/Y', $filters['data_checkout_final'])->endOfDay());
        }

        return $query->orderBy('data_checkin', 'desc');
    }

    private function extrairMetodosPagamento(?Pagamento $pagamento): string
    {
        if (!$pagamento || !$pagamento->valores_recebidos) {
            return '';
        }

        $valores = is_array($pagamento->valores_recebidos)
            ? $pagamento->valores_recebidos
            : json_decode($pagamento->valores_recebidos, true) ?? [];

        $labels = [];
        foreach (array_keys($valores) as $chave) {
            $key = explode('-', $chave)[0];
            foreach (Pagamento::METODOS_PAGAMENTO as $catKey => $cat) {
                if ($key === $catKey) {
                    $labels[] = $cat['label'];
                    break;
                }
                foreach ($cat['submetodos'] as $subKey => $subLabel) {
                    if ($key === $subKey) {
                        $labels[] = $subLabel;
                        break 2;
                    }
                }
            }
        }

        return implode(', ', array_unique($labels));
    }

    private function normalizarFiltrosEstoque(Request $request): array
    {
        $filters = $request->all();
        $filters['local_estoque_id'] ??= '';
        $filters['somente_ativos'] ??= '1';

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Estoque>
     */
    private function colecaoEstoqueParaRelatorio(array $filters): Collection
    {
        $query = Estoque::query()
            ->with(['produto.categoria', 'localEstoque'])
            ->orderBy('local_estoque_id')
            ->orderBy('id');

        if ($filters['local_estoque_id'] !== '') {
            $query->where('local_estoque_id', $filters['local_estoque_id']);
        }

        if (($filters['somente_ativos'] ?? '') === '1') {
            $query->whereHas('produto', fn ($q) => $q->where('ativo', 1));
        }

        return $query->get();
    }

    private function respostaPdf(string $html, string $filename, string $orientation = 'portrait')
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Café ~9h: check-in do hotel é às 15h, logo no dia D só contam quem já pernoitou
     * (data de check-in &lt; D) e ainda está ou sai nesse dia (data_checkout &gt;= D).
     */
    private function queryReservasCafe(Carbon $dataRef)
    {
        $refStr = $dataRef->format('Y-m-d');

        return Reserva::query()
            ->select('reservas.*')
            ->leftJoin('quartos', 'quartos.id', '=', 'reservas.quarto_id')
            ->with(['quarto', 'clienteResponsavel', 'clienteSolicitante', 'acompanhantes.cliente'])
            ->where('reservas.situacao_reserva', 'HOSPEDADO')
            ->where('reservas.data_checkin', '<', $refStr)
            ->where('reservas.data_checkout', '>=', $refStr)
            ->orderBy('quartos.numero')
            ->orderBy('reservas.id');
    }

    /**
     * @param  Collection<int, Reserva>  $reservas
     * @return array<int, array{quarto: string, tipo: string, tipo_pessoa: string, qtd_pessoas_quarto: int, nome: string, cpf: string, ordem: int}>
     */
    private function montarLinhasCafe(Collection $reservas): array
    {
        $linhas = [];

        foreach ($reservas as $reserva) {
            $numeroQuarto = $reserva->quarto->numero ?? (string) ($reserva->quarto_id ?? '-');
            $titular = $reserva->clienteResponsavel ?? $reserva->clienteSolicitante;
            $nomeTitular = $titular->nome ?? '—';
            $cpfTitular = $titular->cpf ?? '';
            $qtdPessoas = 1 + $reserva->acompanhantes->count();

            $linhas[] = [
                'quarto'             => (string) $numeroQuarto,
                'tipo'               => 'Titular',
                'tipo_pessoa'        => 'Adulto',
                'qtd_pessoas_quarto' => $qtdPessoas,
                'nome'               => $nomeTitular,
                'cpf'                => $cpfTitular,
                'ordem'              => 0,
            ];

            foreach ($reserva->acompanhantes as $ac) {
                $nome = $ac->cliente->nome ?? $ac->nome ?? '—';
                $cpf = $ac->cliente->cpf ?? $ac->cpf ?? '';
                $linhas[] = [
                    'quarto'             => (string) $numeroQuarto,
                    'tipo'               => 'Acompanhante',
                    'tipo_pessoa'        => $ac->tipo ?? 'Adulto',
                    'qtd_pessoas_quarto' => $qtdPessoas,
                    'nome'               => $nome,
                    'cpf'                => $cpf,
                    'ordem'              => 1,
                ];
            }
        }

        usort($linhas, function ($a, $b) {
            $cmp = strcmp($a['quarto'], $b['quarto']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['ordem'] <=> $b['ordem'];
        });

        return $linhas;
    }
}
