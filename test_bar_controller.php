<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Http\Controllers\Admin\Bar\BarHomeController;
use App\Services\Bar\MesaService;
use App\Services\MovimentacaoEstoqueService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testando BarHomeController...\n";
    
    // Instanciar as dependências
    $movimentacaoEstoqueService = new MovimentacaoEstoqueService();
    $mesaService = new MesaService($movimentacaoEstoqueService);
    $controller = new BarHomeController($mesaService);
    
    echo "Controller instanciado com sucesso!\n";
    
    // Testar o método statusMesaNoDia
    echo "Testando statusMesaNoDia...\n";
    $status = $mesaService->statusMesaNoDia();
    echo "statusMesaNoDia executado com sucesso! Total de mesas: " . count($status) . "\n";
    
    // Testar outros dados necessários
    echo "Testando contagem de usuários...\n";
    $totalUsuarios = \App\Models\Usuario::count();
    echo "Total de usuários: $totalUsuarios\n";
    
    echo "Testando contagem de clientes...\n";
    $totalClientes = \App\Models\Cliente::count();
    echo "Total de clientes: $totalClientes\n";
    
    echo "Testando quartos...\n";
    $quartos = \App\Models\Quarto::all();
    echo "Total de quartos: " . $quartos->count() . "\n";
    
    echo "Testando reservas...\n";
    $reservas = \App\Models\Reserva::where('situacao_reserva', 'HOSPEDADO')->get();
    echo "Total de reservas hospedadas: " . $reservas->count() . "\n";
    
    echo "\nTodos os testes passaram! O controller deveria funcionar normalmente.\n";
    
} catch (Exception $e) {
    echo "ERRO ENCONTRADO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}