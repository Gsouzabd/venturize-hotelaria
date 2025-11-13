<?php

require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';

// Bootstrap da aplicação
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->bootstrap();

echo "<h1>🔍 Teste Específico da Rota /admin/bar</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    echo "<h3>✅ Laravel inicializado com sucesso</h3>";
    
    // Testar se as classes existem
    echo "<h3>📋 Verificação de Classes:</h3>";
    
    if (class_exists('App\\Http\\Controllers\\Admin\\Bar\\BarHomeController')) {
        echo "<p style='color: green;'>✅ BarHomeController existe</p>";
    } else {
        echo "<p style='color: red;'>❌ BarHomeController NÃO existe</p>";
        exit(1);
    }
    
    if (class_exists('App\\Services\\Bar\\MesaService')) {
        echo "<p style='color: green;'>✅ MesaService existe</p>";
    } else {
        echo "<p style='color: red;'>❌ MesaService NÃO existe</p>";
        exit(1);
    }
    
    if (class_exists('App\\Services\\MovimentacaoEstoqueService')) {
        echo "<p style='color: green;'>✅ MovimentacaoEstoqueService existe</p>";
    } else {
        echo "<p style='color: red;'>❌ MovimentacaoEstoqueService NÃO existe</p>";
        exit(1);
    }
    
    // Testar instanciação dos serviços
    echo "<h3>🔧 Teste de Instanciação:</h3>";
    
    try {
        $movimentacaoService = app('App\\Services\\MovimentacaoEstoqueService');
        echo "<p style='color: green;'>✅ MovimentacaoEstoqueService instanciado</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao instanciar MovimentacaoEstoqueService: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    try {
        $mesaService = app('App\\Services\\Bar\\MesaService');
        echo "<p style='color: green;'>✅ MesaService instanciado</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao instanciar MesaService: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    try {
        $controller = app('App\\Http\\Controllers\\Admin\\Bar\\BarHomeController');
        echo "<p style='color: green;'>✅ BarHomeController instanciado</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao instanciar BarHomeController: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    // Testar dados necessários
    echo "<h3>📊 Teste de Dados:</h3>";
    
    $totalUsuarios = \App\Models\Usuario::count();
    echo "<p style='color: green;'>✅ Usuario::count(): {$totalUsuarios}</p>";
    
    $totalClientes = \App\Models\Cliente::count();
    echo "<p style='color: green;'>✅ Cliente::count(): {$totalClientes}</p>";
    
    $totalMesas = \App\Models\Bar\Mesa::count();
    echo "<p style='color: green;'>✅ Mesa::count(): {$totalMesas}</p>";
    
    $totalQuartos = \App\Models\Quarto::count();
    echo "<p style='color: green;'>✅ Quarto::count(): {$totalQuartos}</p>";
    
    $totalReservas = \App\Models\Reserva::where('situacao_reserva', 'HOSPEDADO')->count();
    echo "<p style='color: green;'>✅ Reservas HOSPEDADO: {$totalReservas}</p>";
    
    // Testar o método statusMesaNoDia
    echo "<h3>🎯 Teste do Método statusMesaNoDia:</h3>";
    
    try {
        $statusMesaNoDia = $mesaService->statusMesaNoDia();
        echo "<p style='color: green;'>✅ statusMesaNoDia executado com sucesso! Total: " . count($statusMesaNoDia) . "</p>";
        
        // Mostrar algumas informações sobre as mesas
        $totalOcupadas = collect($statusMesaNoDia)->where('status', 'Ocupada')->count();
        $totalLivres = collect($statusMesaNoDia)->where('status', 'Livre')->count();
        echo "<p style='color: blue;'>📊 Mesas Ocupadas: {$totalOcupadas}</p>";
        echo "<p style='color: blue;'>📊 Mesas Livres: {$totalLivres}</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro no statusMesaNoDia: " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    // Testar o método index do controller
    echo "<h3>🎯 Teste do Método index do Controller:</h3>";
    
    try {
        $request = \Illuminate\Http\Request::create('/admin/bar', 'GET');
        $response = $controller->index($request);
        echo "<p style='color: green;'>✅ Controller->index() executado com sucesso!</p>";
        echo "<p style='color: blue;'>📄 Tipo de resposta: " . get_class($response) . "</p>";
        
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if (strlen($content) > 100) {
                echo "<p style='color: green;'>✅ Conteúdo HTML gerado (" . strlen($content) . " caracteres)</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Conteúdo muito pequeno: " . htmlspecialchars($content) . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro no Controller->index(): " . $e->getMessage() . "</p>";
        echo "<p style='color: red;'>📍 Arquivo: " . $e->getFile() . "</p>";
        echo "<p style='color: red;'>📍 Linha: " . $e->getLine() . "</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . $e->getTraceAsString() . "</pre>";
        throw $e;
    }
    
    echo "<h2 style='color: green;'>🎉 TODOS OS TESTES PASSARAM!</h2>";
    echo "<p style='color: green; font-weight: bold;'>A rota /admin/bar deveria funcionar perfeitamente.</p>";
    
    echo "<hr>";
    echo "<h3>🔗 Links para Teste:</h3>";
    echo "<p><a href='/admin/bar' target='_blank' style='color: blue; font-weight: bold;'>🔗 Testar Rota /admin/bar</a></p>";
    echo "<p><a href='/limpar-cache-laravel.php' target='_blank' style='color: orange;'>🧹 Limpar Cache Laravel</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO ENCONTRADO!</h2>";
    echo "<p style='color: red;'><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: red;'><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>📍 Stack Trace:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>" . $e->getTraceAsString() . "</pre>";
    
    echo "<hr>";
    echo "<h3>💡 Possíveis Soluções:</h3>";
    echo "<ul>";
    echo "<li>Verificar se todas as dependências estão instaladas</li>";
    echo "<li>Limpar cache do Laravel</li>";
    echo "<li>Verificar permissões de arquivos</li>";
    echo "<li>Verificar configuração do banco de dados</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>