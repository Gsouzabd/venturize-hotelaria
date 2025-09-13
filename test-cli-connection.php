<?php

// Script para testar conexão direta via CLI
echo "🔍 Testando conexão com banco via CLI...\n";

// Carregar variáveis do .env
if (file_exists('.env.dreamhost')) {
    $lines = file('.env.dreamhost', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configurações do banco
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? '';
$username = $_ENV['DB_USERNAME'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "📋 Configurações:\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n";
echo "Database: {$database}\n";
echo "Username: {$username}\n";
echo "Password: " . (empty($password) ? 'VAZIO' : str_repeat('*', strlen($password))) . "\n\n";

// Teste 1: Conexão TCP direta
echo "🔌 Teste 1: Conexão TCP direta...\n";
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ Conexão TCP: OK\n";
    
    // Testar query simples
    $stmt = $pdo->query('SELECT 1 as test');
    $result = $stmt->fetch();
    echo "✅ Query teste: OK (resultado: {$result['test']})\n";
    
    // Testar tabelas
    echo "\n📊 Verificando tabelas:\n";
    $tables = ['users', 'clientes', 'mesas', 'reservas', 'pedidos'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch();
            echo "✅ Tabela {$table}: {$result['count']} registros\n";
        } catch (Exception $e) {
            echo "❌ Tabela {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    $pdo = null;
    
} catch (Exception $e) {
    echo "❌ Erro na conexão TCP: " . $e->getMessage() . "\n";
}

// Teste 2: Conexão via socket Unix (se disponível)
echo "\n🔌 Teste 2: Verificando socket Unix...\n";
$possibleSockets = [
    '/tmp/mysql.sock',
    '/var/run/mysqld/mysqld.sock',
    '/var/lib/mysql/mysql.sock',
    '/usr/local/mysql/tmp/mysql.sock'
];

foreach ($possibleSockets as $socket) {
    if (file_exists($socket)) {
        echo "✅ Socket encontrado: {$socket}\n";
        try {
            $dsn = "mysql:unix_socket={$socket};dbname={$database}";
            $pdo = new PDO($dsn, $username, $password);
            echo "✅ Conexão via socket: OK\n";
            $pdo = null;
            break;
        } catch (Exception $e) {
            echo "❌ Erro no socket {$socket}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Socket não encontrado: {$socket}\n";
    }
}

// Teste 3: Informações do sistema
echo "\n🖥️ Informações do sistema:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "SAPI: " . php_sapi_name() . "\n";

// Verificar extensões
echo "\n🔧 Extensões PHP:\n";
echo "PDO: " . (extension_loaded('pdo') ? '✅' : '❌') . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅' : '❌') . "\n";
echo "MySQLi: " . (extension_loaded('mysqli') ? '✅' : '❌') . "\n";

echo "\n🎯 Diagnóstico:\n";
echo "Se a conexão TCP funcionou, o problema pode ser:\n";
echo "1. Laravel tentando usar socket Unix em vez de TCP\n";
echo "2. Configuração específica do ambiente CLI\n";
echo "3. Cache de configuração do Laravel\n";
echo "\nSoluções sugeridas:\n";
echo "1. php artisan config:clear\n";
echo "2. php artisan cache:clear\n";
echo "3. Verificar se o .env está sendo carregado corretamente\n";