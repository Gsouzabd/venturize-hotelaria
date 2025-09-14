<?php

// Teste específico para o erro "Target class [config] does not exist"

echo "<h1>🔍 Teste do Helper config() - Laravel</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Carregar Laravel
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    echo "<h3>✅ Autoload e bootstrap carregados</h3>";
    
    // Verificar se a aplicação foi criada
    if ($app instanceof Illuminate\Foundation\Application) {
        echo "<p style='color: green;'>✅ Application instance criada corretamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Application instance não é válida</p>";
        var_dump($app);
        exit(1);
    }
    
    // Fazer bootstrap da aplicação
    echo "<h3>🚀 Fazendo bootstrap da aplicação...</h3>";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<p style='color: green;'>✅ Bootstrap realizado com sucesso</p>";
    
    // Verificar se o container tem o binding 'config'
    echo "<h3>🔍 Verificando bindings do container:</h3>";
    
    if ($app->bound('config')) {
        echo "<p style='color: green;'>✅ Binding 'config' existe no container</p>";
    } else {
        echo "<p style='color: red;'>❌ Binding 'config' NÃO existe no container</p>";
    }
    
    // Verificar se a facade Config está registrada
    if (class_exists('Illuminate\Support\Facades\Config')) {
        echo "<p style='color: green;'>✅ Facade Config existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Facade Config NÃO existe</p>";
    }
    
    // Testar o helper config() diretamente
    echo "<h3>🎯 Testando helper config():</h3>";
    
    try {
        $appName = config('app.name');
        echo "<p style='color: green;'>✅ config('app.name'): {$appName}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao chamar config('app.name'): " . $e->getMessage() . "</p>";
        echo "<p style='color: red;'>📍 Arquivo: " . $e->getFile() . "</p>";
        echo "<p style='color: red;'>📍 Linha: " . $e->getLine() . "</p>";
    }
    
    try {
        $appEnv = config('app.env');
        echo "<p style='color: green;'>✅ config('app.env'): {$appEnv}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao chamar config('app.env'): " . $e->getMessage() . "</p>";
    }
    
    try {
        $dbDefault = config('database.default');
        echo "<p style='color: green;'>✅ config('database.default'): {$dbDefault}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao chamar config('database.default'): " . $e->getMessage() . "</p>";
    }
    
    // Testar usando a facade diretamente
    echo "<h3>🎯 Testando Facade Config diretamente:</h3>";
    
    try {
        $appNameFacade = \Illuminate\Support\Facades\Config::get('app.name');
        echo "<p style='color: green;'>✅ Config::get('app.name'): {$appNameFacade}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao chamar Config::get(): " . $e->getMessage() . "</p>";
    }
    
    // Testar usando o container diretamente
    echo "<h3>🎯 Testando container diretamente:</h3>";
    
    try {
        $configService = $app->make('config');
        $appNameContainer = $configService->get('app.name');
        echo "<p style='color: green;'>✅ app('config')->get('app.name'): {$appNameContainer}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao usar container: " . $e->getMessage() . "</p>";
    }
    
    // Verificar providers carregados
    echo "<h3>📋 Providers carregados:</h3>";
    
    $providers = $app->getLoadedProviders();
    echo "<p>Total de providers carregados: " . count($providers) . "</p>";
    
    $importantProviders = [
        'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
        'Illuminate\\Config\\ConfigServiceProvider',
        'App\\Providers\\AppServiceProvider'
    ];
    
    foreach ($importantProviders as $provider) {
        if (isset($providers[$provider])) {
            echo "<p style='color: green;'>✅ {$provider} carregado</p>";
        } else {
            echo "<p style='color: red;'>❌ {$provider} NÃO carregado</p>";
        }
    }
    
    echo "<h2 style='color: green;'>🎉 TESTE CONCLUÍDO!</h2>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO CRÍTICO!</h2>";
    echo "<p style='color: red;'><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: red;'><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>📍 Stack Trace:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>