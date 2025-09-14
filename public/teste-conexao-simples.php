<?php

// Script simples para testar conexão - copie este conteúdo para o servidor
// Salve como: teste-conexao-simples.php na raiz do projeto no servidor

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

echo "<h1>🔍 Teste de Conexão - Venturize Hotelaria</h1>";
echo "<p>Executado em: " . date('Y-m-d H:i:s') . "</p>";

try {
    // Carregar Laravel
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    // IMPORTANTE: Fazer o boot da aplicação Laravel
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    $kernel->bootstrap();
    
    echo "<h2>✅ Laravel carregado com sucesso</h2>";
    
    // Testar configurações
    echo "<h3>📋 Configurações:</h3>";
    echo "<ul>";
    echo "<li><strong>APP_ENV:</strong> " . config('app.env') . "</li>";
    echo "<li><strong>APP_DEBUG:</strong> " . (config('app.debug') ? 'true' : 'false') . "</li>";
    echo "<li><strong>DB_CONNECTION:</strong> " . config('database.default') . "</li>";
    echo "<li><strong>DB_HOST:</strong> " . config('database.connections.mysql.host') . "</li>";
    echo "<li><strong>DB_DATABASE:</strong> " . config('database.connections.mysql.database') . "</li>";
    echo "</ul>";
    
    // Testar conexão com banco
    echo "<h3>🔌 Teste de Conexão:</h3>";
    
    $pdo = DB::connection()->getPdo();
    echo "<p style='color: green;'>✅ <strong>Conexão com banco: OK</strong></p>";
    
    // Testar query simples
    $result = DB::select('SELECT 1 as test');
    echo "<p style='color: green;'>✅ <strong>Query teste: OK</strong></p>";
    
    // Testar tabelas do sistema
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
    
    // Testar especificamente o que o BarHomeController faz
    echo "<h3>🎯 Teste do BarHomeController:</h3>";
    
    try {
        // Simular as queries do BarHomeController
        $totalUsuarios = DB::table('users')->count();
        $totalClientes = DB::table('clientes')->count();
        $reservasHospedado = DB::table('reservas')->where('situacao_reserva', 'HOSPEDADO')->count();
        
        echo "<p style='color: green;'>✅ <strong>Usuario::count():</strong> {$totalUsuarios}</p>";
        echo "<p style='color: green;'>✅ <strong>Cliente::count():</strong> {$totalClientes}</p>";
        echo "<p style='color: green;'>✅ <strong>Reservas HOSPEDADO:</strong> {$reservasHospedado}</p>";
        
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h4 style='color: #155724; margin: 0;'>🎉 SUCESSO!</h4>";
        echo "<p style='color: #155724; margin: 5px 0 0 0;'>Todas as queries do BarHomeController funcionaram perfeitamente!</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Erro no BarHomeController:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    }
    
    // Teste final da rota
    echo "<h3>🌐 Próximo Passo:</h3>";
    echo "<p>Se todos os testes acima passaram, a rota <strong>/admin/bar</strong> deve funcionar.</p>";
    echo "<p><a href='/admin/bar' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Testar /admin/bar</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h2 style='color: #721c24;'>❌ Erro na Conexão</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<h3>🔍 Stack Trace:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . " | Servidor: " . $_SERVER['SERVER_NAME'] . "</small></p>";