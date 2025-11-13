<?php

use Illuminate\Support\Facades\Artisan;

// Script para limpar TODOS os caches do Laravel e testar a rota /admin/bar

echo "<h1>🧹 Limpeza Completa de Cache - Laravel</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Carregar Laravel
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    // Bootstrap da aplicação
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<h3>✅ Laravel inicializado com sucesso</h3>";
    
    // Limpar todos os caches
    echo "<h3>🧹 Limpando caches...</h3>";
    
    try {
        Artisan::call('config:clear');
        echo "<p style='color: green;'>✅ Cache de configuração limpo</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao limpar config cache: " . $e->getMessage() . "</p>";
    }
    
    try {
        Artisan::call('cache:clear');
        echo "<p style='color: green;'>✅ Cache geral limpo</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao limpar cache geral: " . $e->getMessage() . "</p>";
    }
    
    try {
        Artisan::call('route:clear');
        echo "<p style='color: green;'>✅ Cache de rotas limpo</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao limpar route cache: " . $e->getMessage() . "</p>";
    }
    
    try {
        Artisan::call('view:clear');
        echo "<p style='color: green;'>✅ Cache de views limpo</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao limpar view cache: " . $e->getMessage() . "</p>";
    }
    
    try {
        Artisan::call('optimize:clear');
        echo "<p style='color: green;'>✅ Otimizações limpas</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Erro ao limpar otimizações: " . $e->getMessage() . "</p>";
    }
    
    // Verificar se os arquivos de cache foram removidos
    echo "<h3>📁 Verificando arquivos de cache:</h3>";
    
    $cacheFiles = [
        '../bootstrap/cache/config.php' => 'Config Cache',
        '../bootstrap/cache/routes-v7.php' => 'Routes Cache',
        '../bootstrap/cache/services.php' => 'Services Cache',
        '../storage/framework/cache/data' => 'Data Cache Dir',
        '../storage/framework/views' => 'Views Cache Dir'
    ];
    
    foreach ($cacheFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: orange;'>⚠️ {$description} ainda existe: {$file}</p>";
            
            // Tentar remover manualmente
            if (is_file($file)) {
                if (unlink($file)) {
                    echo "<p style='color: green;'>✅ {$description} removido manualmente</p>";
                } else {
                    echo "<p style='color: red;'>❌ Não foi possível remover {$description}</p>";
                }
            } elseif (is_dir($file)) {
                echo "<p style='color: blue;'>📁 {$description} é um diretório</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ {$description} não existe (limpo)</p>";
        }
    }
    
    // Recriar a aplicação do zero
    echo "<h3>🔄 Reinicializando aplicação...</h3>";
    
    // Limpar variáveis
    unset($app, $kernel, $request);
    
    // Recarregar tudo
    $app = require '../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<p style='color: green;'>✅ Aplicação reinicializada</p>";
    
    // Testar o helper config() novamente
    echo "<h3>🎯 Testando helper config() após limpeza:</h3>";
    
    try {
        $appName = config('app.name');
        echo "<p style='color: green;'>✅ config('app.name'): {$appName}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ AINDA com erro no config(): " . $e->getMessage() . "</p>";
        throw $e;
    }
    
    try {
        $appEnv = config('app.env');
        echo "<p style='color: green;'>✅ config('app.env'): {$appEnv}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro no config('app.env'): " . $e->getMessage() . "</p>";
    }
    
    // Testar a rota /admin/bar
    echo "<h3>🎯 Testando rota /admin/bar:</h3>";
    
    try {
        // Simular request para /admin/bar
        $request = Illuminate\Http\Request::create('/admin/bar', 'GET');
        $response = $app->handle($request);
        
        echo "<p style='color: green;'>✅ Rota /admin/bar processada com sucesso!</p>";
        echo "<p style='color: blue;'>📄 Status: " . $response->getStatusCode() . "</p>";
        
        if ($response->getStatusCode() == 200) {
            $content = $response->getContent();
            if (strlen($content) > 100) {
                echo "<p style='color: green;'>✅ Conteúdo HTML gerado (" . strlen($content) . " caracteres)</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Conteúdo muito pequeno</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Status não é 200: " . $response->getStatusCode() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro na rota /admin/bar: " . $e->getMessage() . "</p>";
        echo "<p style='color: red;'>📍 Arquivo: " . $e->getFile() . "</p>";
        echo "<p style='color: red;'>📍 Linha: " . $e->getLine() . "</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 LIMPEZA COMPLETA REALIZADA!</h2>";
    
    echo "<hr>";
    echo "<h3>🔗 Links para Teste:</h3>";
    echo "<p><a href='/admin/bar' target='_blank' style='color: blue; font-weight: bold;'>🔗 Testar Rota /admin/bar</a></p>";
    echo "<p><a href='/teste-rota-bar.php' target='_blank' style='color: green;'>🔍 Teste Detalhado da Rota</a></p>";
    echo "<p><a href='/teste-config-helper.php' target='_blank' style='color: orange;'>⚙️ Teste do Helper Config</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO CRÍTICO!</h2>";
    echo "<p style='color: red;'><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: red;'><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p style='color: red;'><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>📍 Stack Trace:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;'>" . $e->getTraceAsString() . "</pre>";
    
    echo "<hr>";
    echo "<h3>💡 Próximos Passos:</h3>";
    echo "<ul>";
    echo "<li>Verificar se o composer install foi executado corretamente</li>";
    echo "<li>Verificar se o arquivo .env existe e está configurado</li>";
    echo "<li>Verificar permissões das pastas storage/ e bootstrap/cache/</li>";
    echo "<li>Executar: composer dump-autoload</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Limpeza executada em: " . date('Y-m-d H:i:s') . "</small></p>";
?>