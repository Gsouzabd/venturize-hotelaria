<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\EscposImage;

class PrinterService
{
    /**
     * Obter configurações das impressoras
     * Prioriza banco de dados, com fallback para .env
     */
    private function getPrinterConfigs()
    {
        // Primeiro tenta buscar do banco de dados
        try {
            if (class_exists(\App\Models\Impressora::class)) {
                $impressoras = \App\Models\Impressora::ativas()->ordenadas()->get();
                
                if ($impressoras->isNotEmpty()) {
                    return $impressoras->map(function($imp) {
                        return [
                            'ip' => $imp->ip,
                            'name' => $imp->nome,
                            'port' => $imp->porta,
                            'tipo' => $imp->tipo
                        ];
                    })->toArray();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar impressoras do banco de dados: ' . $e->getMessage());
        }
        
        // Fallback para .env (compatibilidade)
        $printers = [];
        $i = 1;
        while (env("PRINTER_{$i}_IP")) {
            $printers[] = [
                'ip' => env("PRINTER_{$i}_IP"),
                'name' => env("PRINTER_{$i}_NAME", "Impressora {$i}"),
                'port' => env("PRINTER_{$i}_PORT", 9100),
                'tipo' => 'termica'
            ];
            $i++;
        }
        
        return $printers;
    }

    /**
     * Imprimir PDF em todas as impressoras configuradas
     */
    public function printPdfToAllPrinters($pdfContent)
    {
        $printers = $this->getPrinterConfigs();
        $results = [];
        
        if (empty($printers)) {
            Log::warning('Nenhuma impressora configurada no .env');
            return ['success' => false, 'message' => 'Nenhuma impressora configurada'];
        }
        
        foreach ($printers as $printer) {
            try {
                $result = $this->printToPrinter($printer, $pdfContent);
                $results[] = [
                    'printer' => $printer['name'],
                    'ip' => $printer['ip'],
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
                
                Log::info("Impressão enviada para {$printer['name']} ({$printer['ip']}): {$result['message']}");
                
            } catch (Exception $e) {
                $results[] = [
                    'printer' => $printer['name'],
                    'ip' => $printer['ip'],
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                
                Log::error("Erro ao imprimir em {$printer['name']} ({$printer['ip']}): {$e->getMessage()}");
            }
        }
        
        return $results;
    }
    
    /**
     * Imprimir usando HTML renderizado diretamente (método preferido)
     */
    public function printHtmlToAllPrinters($pedidoId)
    {
        $printers = $this->getPrinterConfigs();
        $results = [];
        
        if (empty($printers)) {
            Log::warning('Nenhuma impressora configurada no .env');
            return ['success' => false, 'message' => 'Nenhuma impressora configurada'];
        }
        
        // Buscar o pedido com todos os relacionamentos necessários
        $pedido = \App\Models\Bar\Pedido::with(['mesa', 'reserva.quarto', 'cliente', 'itens.produto'])->find($pedidoId);
        if (!$pedido) {
            return ['success' => false, 'message' => 'Pedido não encontrado'];
        }
        
        // Gerar conteúdo formatado diretamente (sem HTML)
        $cupomContent = $this->generateCupomContent($pedido);
        
        foreach ($printers as $printer) {
            try {
                $result = $this->printTextToThermalPrinter($printer, $cupomContent);
                $results[] = [
                    'printer' => $printer['name'],
                    'ip' => $printer['ip'],
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
                
                Log::info("Cupom impresso para {$printer['name']} ({$printer['ip']}): {$result['message']}");
                
            } catch (Exception $e) {
                $results[] = [
                    'printer' => $printer['name'],
                    'ip' => $printer['ip'],
                    'success' => false,
                    'message' => $e->getMessage()
                ];
                
                Log::error("Erro ao imprimir cupom em {$printer['name']} ({$printer['ip']}): {$e->getMessage()}");
            }
        }
        
        return $results;
     }

     /**
      * Imprimir HTML em uma impressora específica
      */
     private function printHtmlToPrinter($printer, $htmlContent)
     {
         try {
             // Verificar se é uma impressora térmica (porta 9100 disponível)
             $isThermalPrinter = $this->checkThermalPrinter($printer['ip']);
             
             if ($isThermalPrinter) {
                 // Para impressoras térmicas, converter HTML para texto simples
                 $textContent = $this->convertHtmlToText($htmlContent);
                 return $this->printTextToThermalPrinter($printer, $textContent);
             }
             
             // Para impressoras convencionais, usar comando do sistema com HTML
             return $this->printHtmlViaSystemCommand($printer, $htmlContent);
             
         } catch (Exception $e) {
             return [
                 'success' => false,
                 'message' => 'Erro na impressão HTML: ' . $e->getMessage()
             ];
         }
     }
     
     /**
      * Converter HTML para texto simples mantendo o layout
      */
     private function convertHtmlToText($htmlContent)
     {
         // Primeiro, remover completamente as tags <style> e seu conteúdo
         $htmlContent = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $htmlContent);
         
         // Remover comentários HTML
         $htmlContent = preg_replace('/<!--.*?-->/s', '', $htmlContent);
         
         // Adicionar quebras de linha antes de elementos de bloco
         $htmlContent = preg_replace('/<(div|p|h[1-6]|br|hr|table|tr)([^>]*)>/i', "\n$0", $htmlContent);
         
         // Adicionar quebras de linha após elementos de fechamento
         $htmlContent = preg_replace('/<\/(div|p|h[1-6]|tr|table)>/i', "$0\n", $htmlContent);
         
         // Converter <br> em quebras de linha
         $htmlContent = preg_replace('/<br[^>]*>/i', "\n", $htmlContent);
         
         // Remover todas as tags HTML
         $text = strip_tags($htmlContent);
         
         // Decodificar entidades HTML
         $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
         
         // Limpar múltiplas quebras de linha
         $text = preg_replace('/\n\s*\n/', "\n", $text);
         
         // Remover espaços no início e fim de cada linha
         $lines = explode("\n", $text);
         $lines = array_map('trim', $lines);
         
         // Remover linhas vazias
         $lines = array_filter($lines, function($line) {
             return !empty($line);
         });
         
         $text = implode("\n", $lines);
         
         // Formatar para impressora térmica
         return $this->formatTextForThermalPrinter($text);
     }
     
     /**
      * Imprimir texto em impressora térmica
      */
     private function printTextToThermalPrinter($printer, $textContent)
    {
        Log::info("Iniciando impressão para {$printer['name']} ({$printer['ip']})");
        Log::info("Conteúdo a imprimir: " . strlen($textContent) . " caracteres");
        
        // Testar conectividade com múltiplas tentativas
        $maxAttempts = 3;
        $connected = false;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            Log::info("Tentativa {$attempt}/{$maxAttempts} de conectividade para {$printer['ip']}");
            
            $socket = @fsockopen($printer['ip'], 9100, $errno, $errstr, 3);
            if ($socket) {
                fclose($socket);
                $connected = true;
                Log::info("Conectividade OK na tentativa {$attempt}");
                break;
            } else {
                Log::warning("Tentativa {$attempt} falhou: {$errno} - {$errstr}");
                if ($attempt < $maxAttempts) {
                    sleep(1); // Aguardar 1 segundo antes da próxima tentativa
                }
            }
        }
        
        if (!$connected) {
            Log::error("Impressora {$printer['ip']} não acessível após {$maxAttempts} tentativas");
            return ['success' => false, 'message' => "Impressora não acessível após {$maxAttempts} tentativas"];
        }
        
        // Tentar primeiro com mike42/escpos-php
        try {
            Log::info("Tentando impressão via NetworkPrintConnector...");
            $connector = new NetworkPrintConnector($printer['ip'], 9100, 8); // timeout maior
            $printerObj = new Printer($connector);
            
            Log::info("Inicializando impressora...");
            $printerObj->initialize();
            usleep(200000); // 0.2 segundos após inicialização
            
            Log::info("Enviando conteúdo para impressão em chunks...");
            // Dividir o conteúdo em linhas menores para evitar problemas de buffer
            $lines = explode("\n", $textContent);
            $totalLines = count($lines);
            
            foreach ($lines as $index => $line) {
                $printerObj->text($line . "\n");
                
                // Pausa pequena a cada 5 linhas para dar tempo à impressora processar
                if (($index + 1) % 5 === 0) {
                    usleep(100000); // 0.1 segundos
                    Log::info("Processadas " . ($index + 1) . "/{$totalLines} linhas");
                }
            }
            
            Log::info("Finalizando impressão...");
            usleep(200000); // 0.2 segundos antes de finalizar
            $printerObj->feed(3);
            usleep(200000); // 0.2 segundos após feed
            $printerObj->cut();
            usleep(200000); // 0.2 segundos após corte
            $printerObj->close();
            
            Log::info("Impressão concluída com sucesso via mike42/escpos-php");
            return ['success' => true, 'message' => 'Impressão enviada com sucesso via mike42/escpos-php'];
            
        } catch (\Exception $e) {
            Log::error("Erro na impressão térmica com mike42: " . $e->getMessage());
            
            // Fallback para método socket direto
            Log::info("Tentando fallback com socket direto...");
            return $this->printTextToThermalPrinterFallback($printer, $textContent);
        }
    }
     
     /**
     * Método fallback usando socket direto
     */
    private function printTextToThermalPrinterFallback($printer, $textContent)
    {
        Log::info("Iniciando método fallback para {$printer['ip']}");
        
        try {
            Log::info("Criando socket TCP...");
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$socket) {
                throw new \Exception('Não foi possível criar socket');
            }

            Log::info("Configurando timeouts do socket...");
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 5, 'usec' => 0));
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 5, 'usec' => 0));

            Log::info("Conectando ao socket {$printer['ip']}:9100...");
            $result = socket_connect($socket, $printer['ip'], 9100);
            if (!$result) {
                $error = socket_strerror(socket_last_error($socket));
                throw new \Exception("Não foi possível conectar à impressora: {$error}");
            }

            Log::info("Preparando comandos ESC/POS...");
            // Comandos ESC/POS básicos
            $escPos = "\x1B\x40"; // ESC @ - Inicializar
            $escPos .= $textContent;
            $escPos .= "\x0A\x0A\x0A"; // 3 quebras de linha
            $escPos .= "\x1D\x56\x41\x03"; // Corte parcial

            Log::info("Enviando " . strlen($escPos) . " bytes para impressora em chunks...");
            
            // Enviar dados em pequenos chunks para evitar problemas de buffer
            $chunkSize = 64; // 64 bytes por chunk
            $totalBytes = strlen($escPos);
            $totalSent = 0;
            
            for ($offset = 0; $offset < $totalBytes; $offset += $chunkSize) {
                $chunk = substr($escPos, $offset, $chunkSize);
                $chunkLength = strlen($chunk);
                
                $bytesWritten = socket_write($socket, $chunk, $chunkLength);
                
                if ($bytesWritten === false) {
                    $error = socket_strerror(socket_last_error($socket));
                    throw new \Exception("Erro ao enviar chunk: {$error}");
                }
                
                $totalSent += $bytesWritten;
                Log::info("Chunk enviado: {$bytesWritten}/{$chunkLength} bytes (total: {$totalSent}/{$totalBytes})");
                
                // Pequena pausa entre chunks para dar tempo à impressora processar
                usleep(50000); // 0.05 segundos
            }
            
            Log::info("Todos os dados enviados: {$totalSent}/{$totalBytes} bytes");
            
            // Aguardar um pouco mais para garantir que todos os dados foram processados
            usleep(1000000); // 1 segundo
            
            Log::info("Fechando socket...");
            socket_close($socket);

            Log::info("Impressão fallback concluída com sucesso");
            return ['success' => true, 'message' => 'Impressão enviada com sucesso via fallback'];
        } catch (\Exception $e) {
            if (isset($socket)) {
                socket_close($socket);
            }
            Log::error("Erro no fallback de impressão: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro na impressão: ' . $e->getMessage()];
        }
    }
     
     /**
      * Imprimir HTML via comando do sistema
      */
     private function printHtmlViaSystemCommand($printer, $htmlContent)
     {
         try {
             // Converter HTML para PDF temporário usando DomPDF
             $options = new \Dompdf\Options();
             $options->set('defaultFont', 'Courier');
             $options->set('isHtml5ParserEnabled', true);
             $dompdf = new \Dompdf\Dompdf($options);
             
             // Definir tamanho do papel para impressora térmica
             $customPaper = array(0, 0, 226.77, 841.89); // 80mm x 297mm
             $dompdf->setPaper($customPaper);
             
             $dompdf->loadHtml($htmlContent);
             $dompdf->render();
             $pdfContent = $dompdf->output();
             
             // Usar o método existente para imprimir PDF
             return $this->printViaSystemCommand($printer, $pdfContent);
             
         } catch (Exception $e) {
             return [
                 'success' => false,
                 'message' => 'Erro ao converter HTML para PDF: ' . $e->getMessage()
             ];
         }
     }

    /**
     * Imprimir PDF em uma impressora específica
     */
    private function printToPrinter($printer, $pdfContent)
    {
        try {
            // Sempre tentar primeiro via socket (porta 9100) - método mais confiável e não depende de exec()
            $isThermalPrinter = $this->checkThermalPrinter($printer['ip']);
            
            if ($isThermalPrinter) {
                // Para impressoras térmicas, usar comandos ESC/POS via socket
                $socketResult = $this->printToThermalPrinter($printer, $pdfContent);
                
                if ($socketResult['success']) {
                    return $socketResult;
                }
                
                Log::warning('PrinterService: Falha na impressão via socket, tentando método alternativo', [
                    'printer_ip' => $printer['ip'],
                    'erro' => $socketResult['message'] ?? 'N/A'
                ]);
            }
            
            // Fallback: tentar via comando do sistema (só se exec() estiver disponível)
            if (function_exists('exec')) {
                return $this->printViaSystemCommand($printer, $pdfContent);
            } else {
                // Se exec() não estiver disponível e socket falhou, retornar erro
                return [
                    'success' => false,
                    'message' => 'Impressão via socket falhou e exec() não está disponível. Verifique se a impressora está acessível na porta 9100.'
                ];
            }
            
        } catch (Exception $e) {
            Log::error('PrinterService: Erro ao imprimir', [
                'printer_ip' => $printer['ip'] ?? 'N/A',
                'erro' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro na impressão: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar se é uma impressora térmica
     */
    private function checkThermalPrinter($ip)
    {
        $socket = @fsockopen($ip, 9100, $errno, $errstr, 2);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    }
    
    /**
     * Método alternativo usando comando do sistema
     */
    private function printViaSystemCommand($printer, $pdfContent)
    {
        try {
            // Verificar se exec() está disponível
            if (!function_exists('exec')) {
                Log::warning('PrinterService: Função exec() não está disponível no servidor');
                return [
                    'success' => false,
                    'message' => 'Função exec() não está disponível. Use impressão via socket (porta 9100) para impressoras térmicas.'
                ];
            }

            // Salvar o PDF temporariamente
            $tempFile = tempnam(sys_get_temp_dir(), 'cupom_') . '.pdf';
            file_put_contents($tempFile, $pdfContent);
            
            // Detectar sistema operacional
            $os = PHP_OS_FAMILY;
            $success = false;
            $message = '';
            
            if ($os === 'Windows') {
                // Windows: tentar diferentes métodos
                // Método 1: Impressora compartilhada
                $printerPath = "\\\\{$printer['ip']}\\printer";
                $command = "copy /B \"$tempFile\" \"$printerPath\" 2>&1";
                
                $output = [];
                $returnCode = 0;
                \exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    // Método 2: Usar print command direto
                    $command = "print /D:\\\\{$printer['ip']}\\printer \"$tempFile\" 2>&1";
                    \exec($command, $output, $returnCode);
                }
                
                $success = ($returnCode === 0);
                $message = $success ? 'Documento enviado via Windows' : 'Erro: ' . implode(' ', $output);
                
            } elseif ($os === 'Darwin') {
                // macOS: usar lp command com diferentes opções
                $commands = [
                    "lp -d {$printer['ip']} -o fit-to-page \"$tempFile\" 2>&1",
                    "lp -h {$printer['ip']}:631 -o fit-to-page \"$tempFile\" 2>&1",
                    "lpr -P {$printer['ip']} \"$tempFile\" 2>&1"
                ];
                
                foreach ($commands as $command) {
                    $output = [];
                    $returnCode = 0;
                    \exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $success = true;
                        $message = 'Documento enviado via macOS';
                        break;
                    }
                }
                
                if (!$success) {
                    $message = 'Erro em todos os métodos: ' . implode(' ', $output);
                }
                
            } else {
                // Linux: usar lp command
                $commands = [
                    "lp -h {$printer['ip']}:631 -o fit-to-page \"$tempFile\" 2>&1",
                    "lpr -H {$printer['ip']}:631 \"$tempFile\" 2>&1"
                ];
                
                foreach ($commands as $command) {
                    $output = [];
                    $returnCode = 0;
                    \exec($command, $output, $returnCode);
                    
                    if ($returnCode === 0) {
                        $success = true;
                        $message = 'Documento enviado via Linux';
                        break;
                    }
                }
                
                if (!$success) {
                    $message = 'Erro em todos os métodos: ' . implode(' ', $output);
                }
            }
            
            // Limpar arquivo temporário
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            return [
                'success' => $success,
                'message' => $message
            ];
            
        } catch (Exception $e) {
            // Garantir limpeza do arquivo temporário
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            
            Log::error('PrinterService: Erro no comando do sistema', [
                'erro' => $e->getMessage(),
                'printer_ip' => $printer['ip'] ?? 'N/A'
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro no comando do sistema: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Método usando Raw Socket para impressoras térmicas
     */
    private function printToThermalPrinter($printer, $pdfContent)
    {
        try {
            // Tentar conectar via socket na porta 9100 (padrão para impressoras de rede)
            $socket = @fsockopen($printer['ip'], 9100, $errno, $errstr, 5);
            
            if (!$socket) {
                // Se não conseguir conectar na porta 9100, tentar porta 515 (LPD)
                $socket = @fsockopen($printer['ip'], 515, $errno, $errstr, 5);
                
                if (!$socket) {
                    return [
                        'success' => false,
                        'message' => "Não foi possível conectar à impressora {$printer['ip']} (Erro: $errno - $errstr)"
                    ];
                }
            }
            
            // Converter PDF para texto simples e comandos ESC/POS
            $escPosContent = $this->convertPdfToEscPos($pdfContent);
            
            // Enviar comandos ESC/POS para impressora térmica
            $bytesWritten = fwrite($socket, $escPosContent);
            fclose($socket);
            
            if ($bytesWritten > 0) {
                return [
                    'success' => true,
                    'message' => "Documento enviado via socket ({$bytesWritten} bytes)"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha ao enviar dados via socket'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na impressão via socket: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Converter PDF para comandos ESC/POS usando o HTML original
     */
    private function convertPdfToEscPos($pdfContent)
    {
        // Comandos ESC/POS para impressora térmica
        $escPos = "\x1B\x40"; // Inicializar impressora
        
        // Converter o PDF de volta para texto simples mantendo o layout original
        $textContent = $this->convertPdfToText($pdfContent);
        
        // Adicionar o conteúdo convertido
        $escPos .= $textContent;
        
        // Cortar papel
        $escPos .= "\x1D\x56\x42\x00";
        
        return $escPos;
    }
    
    /**
     * Converter PDF para texto simples mantendo o layout
     */
    private function convertPdfToText($pdfContent)
    {
        // Para uma implementação mais robusta, seria ideal usar uma biblioteca
        // de parsing de PDF, mas por enquanto vamos extrair o texto básico
        
        // Tentar extrair texto do PDF usando regex simples
        $text = '';
        
        // Método básico de extração de texto de PDF
        if (preg_match_all('/\((.*?)\)/', $pdfContent, $matches)) {
            $text = implode(' ', $matches[1]);
        }
        
        // Se não conseguir extrair do PDF, usar o método de fallback
        if (empty($text)) {
            $text = $this->generateFallbackText($pdfContent);
        }
        
        return $this->formatTextForThermalPrinter($text);
    }
    
    /**
     * Gerar texto de fallback baseado no layout original
     */
    private function generateFallbackText($pdfContent)
    {
        $pdfInfo = $this->extractPdfInfo($pdfContent);
        
        $text = "";
        
        // Cabeçalho centralizado
        if (isset($pdfInfo['tipo']) && $pdfInfo['tipo'] === 'Apartamento') {
            $text .= "         Apartamento          \n";
        } else {
            $text .= "            Bar              \n";
        }
        $text .= "    Parcial Atual do Pedido   \n";
        $text .= "Data e Hora: " . date('d/m/Y H:i:s') . "\n";
        $text .= "\n";
        
        // Informações do pedido
        $text .= "N° Pedido: " . ($pdfInfo['pedido'] ?? 'N/A') . "\n";
        if (isset($pdfInfo['mesa']) && $pdfInfo['mesa'] !== 'N/A') {
            $text .= "N° Mesa: " . $pdfInfo['mesa'] . "\n";
        }
        if (isset($pdfInfo['reserva'])) {
            $text .= "N° Reserva: " . $pdfInfo['reserva'] . "\n";
        }
        if (isset($pdfInfo['quarto'])) {
            $text .= "N° Quarto: " . $pdfInfo['quarto'] . "\n";
        }
        if (isset($pdfInfo['cliente'])) {
            $text .= "Cliente: " . $pdfInfo['cliente'] . "\n";
        }
        
        // Linha separadora
        $text .= "................................\n";
        $text .= "Qtde    Produto         Preço\n";
        
        // Itens
        if (isset($pdfInfo['itens']) && is_array($pdfInfo['itens'])) {
            foreach ($pdfInfo['itens'] as $item) {
                $qtde = str_pad($item['quantidade'] ?? '1', 4, ' ', STR_PAD_RIGHT);
                $produto = substr($item['produto'] ?? 'Produto', 0, 15);
                $produto = str_pad($produto, 15, ' ', STR_PAD_RIGHT);
                $preco = $item['preco'] ?? 'R$ 0,00';
                $text .= $qtde . "    " . $produto . " " . $preco . "\n";
            }
        }
        
        // Totais
        if (isset($pdfInfo['total_consumo'])) {
            $text .= "Total Consumo: " . $pdfInfo['total_consumo'] . "\n";
        }
        if (isset($pdfInfo['taxa_servico'])) {
            $text .= "Taxa Serviço (10%): " . $pdfInfo['taxa_servico'] . "\n";
        }
        if (isset($pdfInfo['total_com_taxa'])) {
            $text .= "Total com Taxa de Serviço:\n" . $pdfInfo['total_com_taxa'] . "\n";
        }
        
        $text .= "\n";
        $text .= "    Assinatura do Cliente:    \n";
        $text .= "\n";
        $text .= "    _______________________    \n";
        
        return $text;
    }
    
    /**
     * Formatar texto para impressora térmica
     */
    private function formatTextForThermalPrinter($text)
    {
        $lines = explode("\n", $text);
        $formattedLines = [];
        $tableHeaderAdded = false;
        $inTableData = false;
        $currentItem = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                // Centralizar títulos
                if (in_array($line, ['Cupom Parcial', 'Bar', 'Apartamento', 'Parcial Atual do Pedido'])) {
                    $formattedLines[] = $this->centerText($line, 32);
                }
                // Detectar início da tabela e adicionar cabeçalho apenas uma vez
                else if ($line === 'Qtde' && !$tableHeaderAdded) {
                    $formattedLines[] = str_repeat('-', 32);
                    $formattedLines[] = sprintf("%-4s %-20s %6s", 'Qtde', 'Produto', 'Preço');
                    $formattedLines[] = str_repeat('-', 32);
                    $tableHeaderAdded = true;
                    $inTableData = true;
                }
                // Pular linhas de cabeçalho duplicadas
                else if (in_array($line, ['Qtde', 'Produto', 'Preço']) && $tableHeaderAdded) {
                    continue;
                }
                // Detectar fim da tabela (quando encontrar totais)
                else if (strpos($line, 'Total Consumo:') !== false) {
                    // Processar item pendente se houver
                    if (!empty($currentItem)) {
                        $formattedLines[] = $this->formatTableItem($currentItem);
                        $currentItem = [];
                    }
                    if ($inTableData) {
                        $formattedLines[] = str_repeat('-', 32);
                        $inTableData = false;
                    }
                    $formattedLines[] = $line;
                }
                // Se estamos na tabela, coletar dados do item
                else if ($inTableData) {
                    // Se é um número (quantidade)
                    if (is_numeric($line)) {
                        // Processar item anterior se houver
                        if (!empty($currentItem)) {
                            $formattedLines[] = $this->formatTableItem($currentItem);
                        }
                        $currentItem = ['qtde' => $line];
                    }
                    // Se contém R$ (preço)
                    else if (strpos($line, 'R$') !== false) {
                        $currentItem['preco'] = $line;
                    }
                    // Senão é o nome do produto
                    else {
                        $currentItem['produto'] = isset($currentItem['produto']) ? $currentItem['produto'] . ' ' . $line : $line;
                    }
                }
                // Formatar linhas de dados e totais
                else if (strpos($line, 'R$') !== false) {
                    $formattedLines[] = $line;
                }
                // Quebrar linhas longas
                else if (strlen($line) > 32) {
                    $chunks = str_split($line, 32);
                    foreach ($chunks as $chunk) {
                        $formattedLines[] = $chunk;
                    }
                } else {
                    $formattedLines[] = $line;
                }
            }
        }
        
        // Processar último item se houver
        if (!empty($currentItem)) {
            $formattedLines[] = $this->formatTableItem($currentItem);
        }
        
        // Adicionar separador final apenas se não foi adicionado
        if (!in_array('Assinatura do Cliente:', $formattedLines)) {
            $formattedLines[] = '';
            $formattedLines[] = 'Assinatura do Cliente:';
            $formattedLines[] = '';
            $formattedLines[] = str_repeat('-', 32);
        }
        
        return implode("\n", $formattedLines) . "\n";
    }
    
    /**
     * Formatar item da tabela
     */
    private function formatTableItem($item)
    {
        $qtde = $item['qtde'] ?? '';
        $produto = $item['produto'] ?? '';
        $preco = $item['preco'] ?? '';
        
        // Truncar produto se muito longo
        if (strlen($produto) > 20) {
            $produto = substr($produto, 0, 17) . '...';
        }
        
        return sprintf("%-4s %-20s %6s", $qtde, $produto, $preco);
    }
    
    /**
     * Gerar conteúdo do cupom formatado diretamente
     */
    private function generateCupomContent($pedido)
    {
        $content = "";
        
        // Cabeçalho centralizado
        $content .= str_repeat(" ", 14) . "Bar\n";
        $content .= "    Parcial Atual do Pedido\n";
        $content .= "Data e Hora: " . now()->format('d/m/Y H:i:s') . "\n\n";
        
        // Informações do pedido
        $content .= "N° Pedido: " . $pedido->id . "\n";
        
        if ($pedido->mesa) {
            $content .= "N° Mesa: " . $pedido->mesa->numero . "\n";
        }
        
        if ($pedido->reserva) {
            $content .= "N° Reserva: " . $pedido->reserva->id . "\n";
            if ($pedido->reserva->quarto) {
                $content .= "N° Quarto: " . $pedido->reserva->quarto->numero . "\n";
            }
        }
        
        if ($pedido->cliente) {
            $clienteNome = mb_strtoupper($pedido->cliente->nome);
            if (strlen($clienteNome) > 32) {
                $clienteNome = substr($clienteNome, 0, 29) . '...';
            }
            $content .= "Cliente: " . $clienteNome . "\n";
        }
        
        // Separador pontilhado
        $content .= str_repeat(".", 32) . "\n";
        
        // Cabeçalho da tabela
        $content .= "Qtde Produto              Preço\n";
        
        // Itens do pedido
         foreach ($pedido->itens as $item) {
             $qtde = str_pad($item->quantidade, 4, ' ', STR_PAD_RIGHT);
             $produto = $item->produto->descricao;
             if (strlen($produto) > 17) {
                 $produto = substr($produto, 0, 14) . '...';
             }
             $produto = str_pad($produto, 17, ' ', STR_PAD_RIGHT);
             $preco = 'R$ ' . number_format($item->preco, 2, ',', '.');
             
             $content .= $qtde . ' ' . $produto . ' ' . $preco . "\n";
         }
        
        // Separador pontilhado
        $content .= str_repeat(".", 32) . "\n";
        
        // Totais
         $totalConsumo = $pedido->itens->sum(function($item) {
             return $item->quantidade * $item->preco;
         });
        
        $taxaServico = $totalConsumo * 0.10;
        $totalComTaxa = $totalConsumo + $taxaServico;
        
        $content .= "Total Consumo: R$ " . number_format($totalConsumo, 2, ',', '.') . "\n";
        $content .= "Taxa Serviço (10%): R$ " . number_format($taxaServico, 2, ',', '.') . "\n";
        $content .= "Total com Taxa de Serviço:\n";
        $content .= "R$ " . number_format($totalComTaxa, 2, ',', '.') . "\n\n";
        
        // Assinatura
        $content .= "     Assinatura do Cliente:\n\n";
        $content .= str_repeat("-", 32) . "\n";
        
        return $content;
    }
    
    /**
     * Centralizar texto
     */
    private function centerText($text, $width = 32)
    {
        $textLength = strlen($text);
        if ($textLength >= $width) {
            return $text;
        }
        
        $padding = ($width - $textLength) / 2;
        $leftPadding = floor($padding);
        
        return str_repeat(' ', $leftPadding) . $text;
    }
    
    /**
     * Extrair informações básicas do PDF
     */
    private function extractPdfInfo($pdfContent)
    {
        $info = [
            'tipo' => 'Bar',
            'pedido' => 'N/A',
            'mesa' => 'N/A',
            'reserva' => 'N/A',
            'quarto' => 'N/A',
            'cliente' => 'N/A',
            'itens' => [],
            'total_consumo' => 'R$ 0,00',
            'taxa_servico' => 'R$ 0,00',
            'total_com_taxa' => 'R$ 0,00'
        ];
        
        // Extrair tipo (Bar ou Apartamento)
        if (preg_match('/(Apartamento|Bar)/', $pdfContent, $matches)) {
            $info['tipo'] = $matches[1];
        }
        
        // Extrair número do pedido
        if (preg_match('/N°\s*Pedido[:\s]+(\d+)/', $pdfContent, $matches)) {
            $info['pedido'] = $matches[1];
        }
        
        // Extrair número da mesa (se não for apartamento)
        if (preg_match('/N°\s*Mesa[:\s]+(\d+)/', $pdfContent, $matches)) {
            $info['mesa'] = $matches[1];
        }
        
        // Extrair número da reserva
        if (preg_match('/N°\s*Reserva[:\s]+(\d+)/', $pdfContent, $matches)) {
            $info['reserva'] = $matches[1];
        }
        
        // Extrair número do quarto
        if (preg_match('/N°\s*Quarto[:\s]+(\d+)/', $pdfContent, $matches)) {
            $info['quarto'] = $matches[1];
        }
        
        // Extrair nome do cliente
        if (preg_match('/Cliente[:\s]+([^\n\r]+)/', $pdfContent, $matches)) {
            $info['cliente'] = trim($matches[1]);
        }
        
        // Extrair totais
        if (preg_match('/Total\s+Consumo[:\s]+R\$\s*([\d,\.]+)/', $pdfContent, $matches)) {
            $info['total_consumo'] = 'R$ ' . $matches[1];
        }
        
        if (preg_match('/Taxa\s+Serviço[^:]*[:\s]+R\$\s*([\d,\.]+)/', $pdfContent, $matches)) {
            $info['taxa_servico'] = 'R$ ' . $matches[1];
        }
        
        if (preg_match('/Total\s+com\s+Taxa[^:]*[:\s]+R\$\s*([\d,\.]+)/', $pdfContent, $matches)) {
            $info['total_com_taxa'] = 'R$ ' . $matches[1];
        }
        
        // Extrair itens da tabela
        // Procurar por padrões de quantidade, produto e preço
        $pattern = '/(\d+)\s+([^\n\r]+?)\s+R\$\s*([\d,\.]+)/';
        if (preg_match_all($pattern, $pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $info['itens'][] = [
                    'quantidade' => $match[1],
                    'produto' => trim($match[2]),
                    'preco' => 'R$ ' . $match[3]
                ];
            }
        }
        
        // Para uma implementação mais robusta, seria necessário
        // usar uma biblioteca de parsing de PDF como TCPDF ou similar
        
        return $info;
    }

    /**
     * Verificar se as impressoras estão acessíveis
     */
    public function checkPrintersStatus()
    {
        $printers = $this->getPrinterConfigs();
        $status = [];
        
        foreach ($printers as $printer) {
            $isOnline = $this->pingPrinter($printer['ip']);
            $status[] = [
                'name' => $printer['name'],
                'ip' => $printer['ip'],
                'online' => $isOnline
            ];
        }
        
        return $status;
    }

    /**
     * Verificar se uma impressora está online
     */
    private function pingPrinter($ip)
    {
        $timeout = 3;
        $socket = @fsockopen($ip, 9100, $errno, $errstr, $timeout);
        
        if ($socket) {
            fclose($socket);
            return true;
        }
        
        return false;
    }
}