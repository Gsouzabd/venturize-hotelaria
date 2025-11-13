<?php

// Script para limpar todos os caches do Laravel em produção
// Execute este arquivo via browser: https://seudominio.com/limpar-cache-laravel.php

echo "<h1>🧹 Limpeza de Cache Laravel</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Carregar Laravel
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    // Fazer o boot da aplicação
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<h2>✅ Laravel inicializado</h2>";
    
    // Limpar caches
    echo "<h3>🧹 Limpando Caches:</h3>";
    echo "<ul>";
    
    // 1. Limpar cache de configuração
    try {
        Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "<li style='color: green;'>✅ Cache de configuração limpo</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao limpar config: " . $e->getMessage() . "</li>";
    }
    
    // 2. Limpar cache de rotas
    try {
        Illuminate\Support\Facades\Artisan::call('route:clear');
        echo "<li style='color: green;'>✅ Cache de rotas limpo</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao limpar rotas: " . $e->getMessage() . "</li>";
    }
    
    // 3. Limpar cache de views
    try {
        Illuminate\Support\Facades\Artisan::call('view:clear');
        echo "<li style='color: green;'>✅ Cache de views limpo</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao limpar views: " . $e->getMessage() . "</li>";
    }
    
    // 4. Limpar cache geral
    try {
        Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "<li style='color: green;'>✅ Cache geral limpo</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao limpar cache: " . $e->getMessage() . "</li>";
    }
    
    echo "</ul>";
    
    // Regenerar caches para produção
    echo "<h3>🔄 Regenerando Caches para Produção:</h3>";
    echo "<ul>";
    
    // 1. Cache de configuração
    try {
        Illuminate\Support\Facades\Artisan::call('config:cache');
        echo "<li style='color: green;'>✅ Cache de configuração regenerado</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao regenerar config: " . $e->getMessage() . "</li>";
    }
    
    // 2. Cache de rotas
    try {
        Illuminate\Support\Facades\Artisan::call('route:cache');
        echo "<li style='color: green;'>✅ Cache de rotas regenerado</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao regenerar rotas: " . $e->getMessage() . "</li>";
    }
    
    // 3. Cache de views
    try {
        Illuminate\Support\Facades\Artisan::call('view:cache');
        echo "<li style='color: green;'>✅ Cache de views regenerado</li>";
    } catch (Exception $e) {
        echo "<li style='color: red;'>❌ Erro ao regenerar views: " . $e->getMessage() . "</li>";
    }
    
    echo "</ul>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h4 style='color: #155724; margin: 0;'>🎉 LIMPEZA CONCLUÍDA!</h4>";
    echo "<p style='color: #155724; margin: 5px 0 0 0;'>Todos os caches foram limpos e regenerados.</p>";
    echo "<p style='color: #155724; margin: 5px 0 0 0;'>Agora teste a rota <strong>/admin/bar</strong></p>";
    echo "</div>";
    
    echo "<p><a href='/admin/bar' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Testar /admin/bar</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h2 style='color: #721c24;'>❌ Erro na Limpeza</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<h3>🔍 Stack Trace:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Limpeza executada em: " . date('Y-m-d H:i:s') . " | Servidor: " . $_SERVER['SERVER_NAME'] . "</small></p>";
echo "<p><small><strong>IMPORTANTE:</strong> Delete este arquivo após usar por segurança!</small></p>";