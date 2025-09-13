<?php

// Script para testar conexão com banco usando configurações do Laravel
// Execute via web: https://venturize.codebeans.dev/test-db-connection-web.php

require_once 'vendor/autoload.php';
use Illuminate\Support\Facades\DB;

try {
    // Carregar aplicação Laravel
    $app = require_once 'bootstrap/app.php';
    
    echo "<h2>🔍 Teste de Conexão com Banco de Dados</h2>";
    echo "<p><strong>Ambiente:</strong> " . env('APP_ENV') . "</p>";
    echo "<p><strong>Debug:</strong> " . (env('APP_DEBUG') ? 'true' : 'false') . "</p>";
    
    echo "<h3>📋 Configurações do Banco:</h3>";
    echo "<ul>";
    echo "<li><strong>Connection:</strong> " . env('DB_CONNECTION') . "</li>";
    echo "<li><strong>Host:</strong> " . env('DB_HOST') . "</li>";
    echo "<li><strong>Port:</strong> " . env('DB_PORT') . "</li>";
    echo "<li><strong>Database:</strong> " . env('DB_DATABASE') . "</li>";
    echo "<li><strong>Username:</strong> " . env('DB_USERNAME') . "</li>";
    echo "</ul>";
    
    echo "<h3>🔌 Teste de Conexão:</h3>";
    
    // Testar usando Laravel DB
    
    $connection = DB::connection();
    $pdo = $connection->getPdo();
    
    echo "<p style='color: green;'>✅ <strong>Conexão Laravel DB: OK</strong></p>";
    
    // Testar query simples
    $result = DB::select('SELECT 1 as test');
    echo "<p style='color: green;'>✅ <strong>Query teste: OK</strong> (resultado: " . $result[0]->test . ")</p>";
    
    // Testar tabelas específicas
    echo "<h3>📊 Verificação de Tabelas:</h3>";
    $tables = ['users', 'clientes', 'mesas', 'reservas', 'pedidos'];
    
    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "<p style='color: green;'>✅ <strong>Tabela {$table}:</strong> {$count} registros</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ <strong>Tabela {$table}:</strong> " . $e->getMessage() . "</p>";
        }
    }
    
    // Testar o que o BarHomeController faz
    echo "<h3>🎯 Teste Específico do BarHomeController:</h3>";
    
    try {
        // Testar Usuario::count()
        $totalUsuarios = DB::table('users')->count();
        echo "<p style='color: green;'>✅ <strong>Usuario::count():</strong> {$totalUsuarios}</p>";
        
        // Testar Cliente::count()
        $totalClientes = DB::table('clientes')->count();
        echo "<p style='color: green;'>✅ <strong>Cliente::count():</strong> {$totalClientes}</p>";
        
        // Testar outras queries do controller
        $reservas = DB::table('reservas')->where('situacao_reserva', 'HOSPEDADO')->count();
        echo "<p style='color: green;'>✅ <strong>Reservas HOSPEDADO:</strong> {$reservas}</p>";
        
        echo "<p style='color: blue; font-weight: bold;'>🎉 Todos os testes do BarHomeController passaram!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Erro no teste do BarHomeController:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>🔧 Diagnóstico:</h3>";
    echo "<p>Se você está vendo esta página, significa que:</p>";
    echo "<ul>";
    echo "<li>✅ O Laravel consegue conectar ao banco via web</li>";
    echo "<li>✅ As configurações do .env estão corretas</li>";
    echo "<li>✅ O problema pode ser específico do ambiente CLI vs Web</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro na Conexão</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>🔍 Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";