<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Bar\BarHomeController;
use App\Services\Bar\MesaService;
use App\Services\MovimentacaoEstoqueService;

echo "🔍 Testando rota /admin/bar...\n";

try {
    // Simular um Request
    $request = Request::create('/admin/bar', 'GET');
    
    echo "✅ Request criado\n";
    
    // Testar se conseguimos instanciar o MovimentacaoEstoqueService
    echo "Testando MovimentacaoEstoqueService...\n";
    $movimentacaoService = app(MovimentacaoEstoqueService::class);
    echo "✅ MovimentacaoEstoqueService OK\n";
    
    // Testar se conseguimos instanciar o MesaService
    echo "Testando MesaService...\n";
    $mesaService = app(MesaService::class);
    echo "✅ MesaService OK\n";
    
    // Testar se conseguimos instanciar o BarHomeController
    echo "Testando BarHomeController...\n";
    $controller = app(BarHomeController::class);
    echo "✅ BarHomeController OK\n";
    
    // Testar o método index
    echo "Testando método index...\n";
    $response = $controller->index($request);
    echo "✅ Método index executado com sucesso!\n";
    
    echo "\n🎉 Todos os testes passaram! A rota deveria funcionar.\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "\n📍 Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    
    // Verificar se é um problema de dependência
    if (strpos($e->getMessage(), 'Class') !== false && strpos($e->getMessage(), 'not found') !== false) {
        echo "\n💡 Possível problema: Classe não encontrada. Verifique se todas as dependências estão instaladas.\n";
    }
    
    if (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'Connection') !== false) {
        echo "\n💡 Possível problema: Conexão com banco de dados. Verifique as configurações do .env\n";
    }
}

echo "\n🔍 Verificando configurações do ambiente...\n";
echo "APP_ENV: " . env('APP_ENV', 'não definido') . "\n";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION', 'não definido') . "\n";
echo "DB_HOST: " . env('DB_HOST', 'não definido') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE', 'não definido') . "\n";