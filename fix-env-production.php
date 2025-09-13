<?php

// Script para corrigir configurações do .env para produção
echo "🔧 Corrigindo configurações do .env para produção...\n\n";

// Verificar se os arquivos existem
if (!file_exists('.env')) {
    echo "❌ Arquivo .env não encontrado!\n";
    exit(1);
}

if (!file_exists('.env.dreamhost')) {
    echo "❌ Arquivo .env.dreamhost não encontrado!\n";
    exit(1);
}

// Fazer backup do .env atual
echo "📋 Fazendo backup do .env atual...\n";
copy('.env', '.env.backup.' . date('Y-m-d-H-i-s'));
echo "✅ Backup criado: .env.backup." . date('Y-m-d-H-i-s') . "\n\n";

// Ler configurações do .env.dreamhost
echo "📖 Lendo configurações do .env.dreamhost...\n";
$dreamhostConfig = [];
$lines = file('.env.dreamhost', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($key, $value) = explode('=', $line, 2);
        $dreamhostConfig[trim($key)] = trim($value);
    }
}

// Configurações críticas que devem ser atualizadas
$criticalKeys = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => 'https://venturize.codebeans.dev',
    'DB_CONNECTION' => $dreamhostConfig['DB_CONNECTION'] ?? 'mysql',
    'DB_HOST' => $dreamhostConfig['DB_HOST'] ?? '',
    'DB_PORT' => $dreamhostConfig['DB_PORT'] ?? '3306',
    'DB_DATABASE' => $dreamhostConfig['DB_DATABASE'] ?? '',
    'DB_USERNAME' => $dreamhostConfig['DB_USERNAME'] ?? '',
    'DB_PASSWORD' => $dreamhostConfig['DB_PASSWORD'] ?? '',
    'LOG_LEVEL' => 'error'
];

echo "🔄 Atualizando configurações críticas...\n";

// Ler .env atual
$envContent = file_get_contents('.env');
$envLines = explode("\n", $envContent);
$updatedLines = [];
$updatedKeys = [];

// Atualizar linhas existentes
foreach ($envLines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        
        if (isset($criticalKeys[$key])) {
            $newValue = $criticalKeys[$key];
            $updatedLines[] = "{$key}={$newValue}";
            $updatedKeys[] = $key;
            echo "✅ {$key}: {$newValue}\n";
        } else {
            $updatedLines[] = $line;
        }
    } else {
        $updatedLines[] = $line;
    }
}

// Adicionar chaves que não existiam
foreach ($criticalKeys as $key => $value) {
    if (!in_array($key, $updatedKeys)) {
        $updatedLines[] = "{$key}={$value}";
        echo "➕ {$key}: {$value} (adicionado)\n";
    }
}

// Salvar .env atualizado
echo "\n💾 Salvando .env atualizado...\n";
file_put_contents('.env', implode("\n", $updatedLines));
echo "✅ Arquivo .env atualizado com sucesso!\n\n";

// Limpar cache do Laravel
echo "🧹 Limpando cache do Laravel...\n";
echo "Executando: php artisan config:clear\n";
passthru('php artisan config:clear 2>&1', $return1);

echo "Executando: php artisan cache:clear\n";
passthru('php artisan cache:clear 2>&1', $return2);

echo "Executando: php artisan route:clear\n";
passthru('php artisan route:clear 2>&1', $return3);

if ($return1 === 0 && $return2 === 0 && $return3 === 0) {
    echo "✅ Cache limpo com sucesso!\n\n";
} else {
    echo "⚠️ Alguns comandos de limpeza falharam, mas isso pode ser normal.\n\n";
}

// Testar conexão
echo "🔌 Testando nova conexão...\n";
try {
    $host = $criticalKeys['DB_HOST'];
    $port = $criticalKeys['DB_PORT'];
    $database = $criticalKeys['DB_DATABASE'];
    $username = $criticalKeys['DB_USERNAME'];
    $password = $criticalKeys['DB_PASSWORD'];
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ Conexão com banco: OK\n";
    
    // Testar query simples
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    echo "✅ Query teste: OK ({$result['count']} usuários)\n";
    
    $pdo = null;
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    echo "\n🔍 Verificações adicionais necessárias:\n";
    echo "1. Confirmar se as credenciais estão corretas\n";
    echo "2. Verificar se o IP está liberado no DreamHost\n";
    echo "3. Testar conexão via web (test-db-connection-web.php)\n";
}

echo "\n🎉 Processo concluído!\n";
echo "\n📋 Próximos passos:\n";
echo "1. Testar a rota /admin/bar novamente\n";
echo "2. Se ainda houver erro, executar: php test-db-connection-web.php via web\n";
echo "3. Verificar logs: tail -f storage/logs/laravel.log\n";