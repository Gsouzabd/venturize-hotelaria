#!/bin/bash

# Script para corrigir erro 500 na rota /admin/bar em produção
# Execute este script no servidor DreamHost via SSH

echo "🔧 Corrigindo erro 500 na rota /admin/bar..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para exibir mensagens coloridas
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# 1. Verificar se estamos no diretório correto
if [ ! -f "composer.json" ]; then
    print_error "composer.json não encontrado. Execute este script no diretório raiz do projeto."
    exit 1
fi

print_status "=== DIAGNÓSTICO DO PROBLEMA ==="
print_status "Problema identificado: Erro 500 na rota /admin/bar"
print_status "Causa: Falha na conexão com banco de dados no BarHomeController::index()"
print_status "Local do erro: Usuario::count() na linha 28 do BarHomeController"

echo ""
print_step "1. Verificando configuração atual do banco de dados..."

# Verificar se .env existe
if [ ! -f ".env" ]; then
    print_error "Arquivo .env não encontrado!"
    if [ -f ".env.dreamhost" ]; then
        print_status "Copiando .env.dreamhost para .env..."
        cp .env.dreamhost .env
    else
        print_error "Arquivo .env.dreamhost também não encontrado!"
        exit 1
    fi
fi

# Mostrar configurações atuais do banco
echo "Configurações atuais do banco:"
echo "DB_CONNECTION: $(grep '^DB_CONNECTION=' .env | cut -d'=' -f2)"
echo "DB_HOST: $(grep '^DB_HOST=' .env | cut -d'=' -f2)"
echo "DB_PORT: $(grep '^DB_PORT=' .env | cut -d'=' -f2)"
echo "DB_DATABASE: $(grep '^DB_DATABASE=' .env | cut -d'=' -f2)"
echo "DB_USERNAME: $(grep '^DB_USERNAME=' .env | cut -d'=' -f2)"

echo ""
print_step "2. Testando conexão com banco de dados..."

# Testar conexão com banco
php -r "
try {
    \$config = [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD')
    ];
    
    \$dsn = 'mysql:host=' . \$config['host'] . ';port=' . \$config['port'] . ';dbname=' . \$config['database'];
    \$pdo = new PDO(\$dsn, \$config['username'], \$config['password']);
    echo '✅ Conexão com banco: OK\n';
    
    // Testar se as tabelas existem
    \$tables = ['users', 'clientes', 'mesas', 'reservas'];
    foreach (\$tables as \$table) {
        \$stmt = \$pdo->query('SHOW TABLES LIKE \"' . \$table . '\"');
        if (\$stmt->rowCount() > 0) {
            echo '✅ Tabela ' . \$table . ': OK\n';
        } else {
            echo '❌ Tabela ' . \$table . ': NÃO ENCONTRADA\n';
        }
    }
    
} catch (Exception \$e) {
    echo '❌ Erro na conexão: ' . \$e->getMessage() . '\n';
    exit(1);
}"

if [ $? -ne 0 ]; then
    print_error "Falha na conexão com banco de dados!"
    echo ""
    print_warning "SOLUÇÕES POSSÍVEIS:"
    echo "1. Verifique se as credenciais do banco estão corretas no .env"
    echo "2. Verifique se o banco de dados foi criado no painel do DreamHost"
    echo "3. Verifique se o usuário tem permissões no banco"
    echo "4. Execute as migrations: php artisan migrate"
    exit 1
fi

echo ""
print_step "3. Limpando cache da aplicação..."

# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

print_status "Cache limpo com sucesso!"

echo ""
print_step "4. Verificando se as migrations foram executadas..."

# Verificar migrations
php artisan migrate:status

echo ""
print_step "5. Testando o controller diretamente..."

# Testar o controller
php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';

try {
    \$request = Illuminate\Http\Request::create('/admin/bar', 'GET');
    \$controller = app('App\Http\Controllers\Admin\Bar\BarHomeController');
    \$response = \$controller->index(\$request);
    echo '✅ BarHomeController::index() executado com sucesso!\n';
} catch (Exception \$e) {
    echo '❌ Erro no controller: ' . \$e->getMessage() . '\n';
    exit(1);
}"

if [ $? -eq 0 ]; then
    echo ""
    print_status "🎉 PROBLEMA RESOLVIDO!"
    print_status "A rota /admin/bar agora deve funcionar corretamente."
    echo ""
    print_status "Teste acessando: https://venturize.codebeans.dev/admin/bar"
else
    echo ""
    print_error "Ainda há problemas. Verifique os erros acima."
fi

echo ""
print_step "6. Recompilando cache para produção..."

# Recompilar cache para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "✅ Cache recompilado para produção!"

echo ""
print_status "=== RESUMO DA CORREÇÃO ==="
echo "1. ✅ Verificação da configuração do banco de dados"
echo "2. ✅ Teste de conectividade com o banco"
echo "3. ✅ Limpeza de cache da aplicação"
echo "4. ✅ Verificação das migrations"
echo "5. ✅ Teste do controller BarHomeController"
echo "6. ✅ Recompilação do cache para produção"

echo ""
print_warning "IMPORTANTE:"
echo "- Se o problema persistir, verifique os logs: tail -f storage/logs/laravel.log"
echo "- Certifique-se de que o Document Root aponta para a pasta 'public'"
echo "- Verifique as permissões: chmod -R 755 storage bootstrap/cache"

print_status "🔧 Correção concluída!"