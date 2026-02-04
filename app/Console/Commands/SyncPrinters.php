<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class SyncPrinters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printers:sync 
                            {--path= : Caminho do arquivo printers.json}
                            {--api-url= : URL da API (padrão: APP_URL/api/print/impressoras)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza impressoras do banco de dados para o arquivo printers.json do agente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Sincronizando impressoras...');

        // Determinar URL da API
        $apiUrl = $this->option('api-url') 
            ?: config('app.url') . '/api/print/impressoras';

        // Determinar caminho do arquivo
        $defaultPath = base_path('printingAgent/Agentimpressao/Agentimpressao/src/config/printers.json');
        $printersJsonPath = $this->option('path') ?: $defaultPath;

        $this->line("   API URL: $apiUrl");
        $this->line("   Arquivo: $printersJsonPath");

        // Buscar impressoras da API
        try {
            $response = Http::timeout(10)->get($apiUrl);
            
            if (!$response->successful()) {
                $this->error("❌ Erro ao buscar impressoras. Código HTTP: " . $response->status());
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                $message = $data['message'] ?? 'Erro desconhecido';
                $this->error("❌ Erro na API: $message");
                return Command::FAILURE;
            }

            if (empty($data['printers'])) {
                $this->warn("⚠️  Nenhuma impressora encontrada na API.");
                $this->line("   Verifique se há impressoras ativas no painel admin.");
                
                // Criar arquivo vazio
                $printersData = ['printers' => []];
            } else {
                // Formatar dados
                $printersData = [
                    'printers' => array_map(function($printer) {
                        return [
                            'name' => $printer['name'] ?? 'Impressora',
                            'ip' => $printer['ip'] ?? '127.0.0.1',
                            'port' => $printer['port'] ?? 9100
                        ];
                    }, $data['printers'])
                ];
            }

            // Garantir que o diretório existe
            $directory = dirname($printersJsonPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Salvar arquivo
            $jsonContent = json_encode($printersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            File::put($printersJsonPath, $jsonContent);

            $this->info("✅ Impressoras sincronizadas com sucesso!");
            $this->newLine();
            
            if (!empty($printersData['printers'])) {
                $this->line("📋 Impressoras configuradas (" . count($printersData['printers']) . "):");
                foreach ($printersData['printers'] as $index => $printer) {
                    $num = $index + 1;
                    $this->line("   $num. {$printer['name']} - {$printer['ip']}:{$printer['port']}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro ao sincronizar: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
