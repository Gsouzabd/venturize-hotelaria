#!/bin/bash

# Script de Deploy para DreamHost
# Execute este script no servidor DreamHost via SSH

echo "🚀 Iniciando deploy no DreamHost..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# Verificar se estamos no diretório correto
if [ ! -f "composer.json" ]; then
    print_error "composer.json não encontrado. Execute este script no diretório raiz do projeto."
    exit 1
fi

print_status "Verificando estrutura do projeto..."

# 1. Configurar arquivo .env
print_status "Configurando arquivo .env..."
if [ -f ".env.dreamhost" ]; then
    cp .env.dreamhost .env
    print_status "Arquivo .env criado a partir do .env.dreamhost"
else
    print_warning "Arquivo .env.dreamhost não encontrado. Criando .env básico..."
    cp .env.example .env
fi

# 2. Gerar APP_KEY
print_status "Gerando APP_KEY..."
php artisan key:generate --force

# 3. Instalar dependências do Composer
print_status "Instalando dependências do Composer..."
composer install --optimize-autoloader --no-dev

if [ $? -ne 0 ]; then
    print_error "Falha ao instalar dependências do Composer"
    exit 1
fi

# 4. Configurar permissões
print_status "Configurando permissões..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 5. Limpar e otimizar cache
print_status "Limpando e otimizando cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Otimizações para produção
print_status "Aplicando otimizações para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Executar migrations (com confirmação)
print_warning "ATENÇÃO: As migrations serão executadas. Isso pode alterar a estrutura do banco de dados."
read -p "Deseja continuar? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Executando migrations..."
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        print_status "Migrations executadas com sucesso!"
    else
        print_error "Falha ao executar migrations"
        exit 1
    fi
else
    print_warning "Migrations puladas. Execute manualmente: php artisan migrate --force"
fi

# 7. Criar symlink para storage (se necessário)
if [ ! -L "public/storage" ]; then
    print_status "Criando symlink para storage..."
    php artisan storage:link
fi

# 8. Verificar configuração
print_status "Verificando configuração..."
php artisan about

print_status "✅ Deploy concluído com sucesso!"
print_warning "Lembre-se de:"
echo "  1. Configurar as variáveis de ambiente no arquivo .env"
echo "  2. Configurar o banco de dados MySQL no painel do DreamHost"
echo "  3. Apontar o domínio para a pasta 'public' do projeto"
echo "  4. Configurar SSL/HTTPS no painel do DreamHost"
echo "  5. Testar a aplicação no navegador"

print_status "🎉 Aplicação pronta para uso!"