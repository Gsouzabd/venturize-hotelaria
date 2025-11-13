<?php

// Teste final da rota /admin/bar com simulação completa

echo "<h1>🎯 Teste Final - Rota /admin/bar</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Carregar Laravel
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    // Bootstrap completo
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<h3>✅ Laravel inicializado</h3>";
    
    // Verificar se as rotas estão carregadas
    echo "<h3>🛣️ Verificando rotas:</h3>";
    
    $router = $app->make('router');
    $routes = $router->getRoutes();
    
    $adminBarRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'admin/bar') !== false) {
            $adminBarRoutes[] = [
                'uri' => $uri,
                'methods' => implode('|', $route->methods()),
                'action' => $route->getActionName()
            ];
        }
    }
    
    if (empty($adminBarRoutes)) {
        echo "<p style='color: red;'>❌ Nenhuma rota admin/bar encontrada!</p>";
        
        // Listar algumas rotas para debug
        echo "<h4>📋 Algumas rotas disponíveis:</h4>";
        $count = 0;
        foreach ($routes as $route) {
            if ($count >= 10) break;
            echo "<p>" . implode('|', $route->methods()) . " " . $route->uri() . " → " . $route->getActionName() . "</p>";
            $count++;
        }
    } else {
        echo "<p style='color: green;'>✅ Rotas admin/bar encontradas:</p>";
        foreach ($adminBarRoutes as $route) {
            echo "<p>• {$route['methods']} {$route['uri']} → {$route['action']}</p>";
        }
    }
    
    // Testar o controller diretamente (sem middleware)
    echo "<h3>🎯 Testando Controller Diretamente:</h3>";
    
    try {
        $controller = $app->make('App\\Http\\Controllers\\Admin\\Bar\\BarHomeController');
        echo "<p style='color: green;'>✅ BarHomeController instanciado</p>";
        
        // Criar request simulado
        $request = Illuminate\Http\Request::create('/admin/bar', 'GET');
        
        // Chamar o método index diretamente
        $response = $controller->index($request);
        
        echo "<p style='color: green;'>✅ Controller->index() executado!</p>";
        echo "<p style='color: blue;'>📄 Tipo: " . get_class($response) . "</p>";
        
        if (method_exists($response, 'getStatusCode')) {
            echo "<p style='color: blue;'>📊 Status: " . $response->getStatusCode() . "</p>";
        }
        
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            echo "<p style='color: blue;'>📏 Tamanho do conteúdo: " . strlen($content) . " caracteres</p>";
            
            // Verificar se contém elementos esperados
            if (strpos($content, 'admin.master') !== false || strpos($content, 'bar') !== false) {
                echo "<p style='color: green;'>✅ Conteúdo parece válido (contém referências esperadas)</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Conteúdo pode não estar completo</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro no controller: " . $e->getMessage() . "</p>";
        echo "<p style='color: red;'>📍 " . $e->getFile() . ":" . $e->getLine() . "</p>";
        throw $e;
    }
    
    // Testar via roteamento completo (com middleware)
    echo "<h3>🌐 Testando via Roteamento Completo:</h3>";
    
    try {
        // Criar request para /admin/bar
        $request = Illuminate\Http\Request::create('/admin/bar', 'GET');
        
        // Adicionar headers necessários
        $request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Test Script)');
        
        // Processar via kernel (inclui middleware)
        $response = $kernel->handle($request);
        
        echo "<p style='color: green;'>✅ Rota processada via kernel!</p>";
        echo "<p style='color: blue;'>📊 Status: " . $response->getStatusCode() . "</p>";
        
        if ($response->getStatusCode() == 200) {
            echo "<p style='color: green;'>🎉 SUCESSO! Rota /admin/bar funcionando!</p>";
            
            $content = $response->getContent();
            echo "<p style='color: blue;'>📏 Conteúdo: " . strlen($content) . " caracteres</p>";
            
        } elseif ($response->getStatusCode() == 302) {
            echo "<p style='color: orange;'>🔄 Redirecionamento (provavelmente para login)</p>";
            $location = $response->headers->get('Location');
            if ($location) {
                echo "<p style='color: blue;'>📍 Redirecionando para: {$location}</p>";
            }
            
        } elseif ($response->getStatusCode() == 500) {
            echo "<p style='color: red;'>❌ Erro 500 - Internal Server Error</p>";
            $content = $response->getContent();
            if (strlen($content) > 0) {
                echo "<p style='color: red;'>📄 Conteúdo do erro:</p>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>" . htmlspecialchars(substr($content, 0, 2000)) . "</pre>";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ Status inesperado: " . $response->getStatusCode() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro no roteamento: " . $e->getMessage() . "</p>";
        echo "<p style='color: red;'>📍 " . $e->getFile() . ":" . $e->getLine() . "</p>";
        
        // Mostrar stack trace resumido
        $trace = $e->getTrace();
        echo "<h4>📍 Stack Trace (primeiras 5 chamadas):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        for ($i = 0; $i < min(5, count($trace)); $i++) {
            $item = $trace[$i];
            echo ($i + 1) . ". ";
            if (isset($item['file'])) {
                echo basename($item['file']) . ":" . $item['line'] . " ";
            }
            if (isset($item['class'])) {
                echo $item['class'] . $item['type'];
            }
            echo $item['function'] . "()\n";
        }
        echo "</pre>";
    }
    
    echo "<h2 style='color: green;'>🏁 DIAGNÓSTICO COMPLETO!</h2>";
    
    echo "<hr>";
    echo "<h3>🔗 Links para Teste Manual:</h3>";
    echo "<p><a href='/admin/bar' target='_blank' style='color: blue; font-weight: bold; font-size: 18px;'>🎯 TESTAR ROTA /admin/bar</a></p>";
    echo "<p><a href='/admin' target='_blank' style='color: green;'>🏠 Painel Admin</a></p>";
    echo "<p><a href='/login' target='_blank' style='color: orange;'>🔐 Login</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>💥 ERRO CRÍTICO!</h2>";
    echo "<p style='color: red;'><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: red;'><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>📍 Stack Trace Completo:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>" . $e->getTraceAsString() . "</pre>";
    
    echo "<hr>";
    echo "<h3>🔧 Ações Recomendadas:</h3>";
    echo "<ul>";
    echo "<li>Verificar se o composer install foi executado</li>";
    echo "<li>Verificar se o arquivo .env está configurado</li>";
    echo "<li>Verificar permissões das pastas storage/ e bootstrap/cache/</li>";
    echo "<li>Executar: php artisan config:clear</li>";
    echo "<li>Verificar logs em storage/logs/laravel.log</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Diagnóstico executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>