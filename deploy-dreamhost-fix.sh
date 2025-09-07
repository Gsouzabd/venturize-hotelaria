#!/bin/bash

# Script de Deploy para DreamHost - Versão Corrigida
# Execute este script no servidor DreamHost via SSH

echo "🚀 Iniciando deploy no DreamHost (versão corrigida)..."

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

# 1. Instalar Composer se não existir
print_status "Verificando instalação do Composer..."
if ! command -v composer &> /dev/null; then
    print_warning "Composer não encontrado. Instalando..."
    
    # Baixar e instalar Composer
    curl -sS https://getcomposer.org/installer | php
    
    if [ $? -eq 0 ]; then
        print_status "Composer instalado com sucesso!"
        
        # Verificar se funciona
        if php composer.phar --version &> /dev/null; then
            print_status "Composer funcionando corretamente"
            COMPOSER_CMD="php composer.phar"
        else
            print_error "Falha ao verificar instalação do Composer"
            exit 1
        fi
    else
        print_error "Falha ao baixar o Composer"
        exit 1
    fi
else
    print_status "Composer já está instalado"
    COMPOSER_CMD="composer"
fi

# 2. Configurar arquivo .env
print_status "Configurando arquivo .env..."
if [ -f ".env.dreamhost" ]; then
    cp .env.dreamhost .env
    print_status "Arquivo .env criado a partir do .env.dreamhost"
else
    print_warning "Arquivo .env.dreamhost não encontrado. Criando .env básico..."
    cp .env.example .env
fi

# 3. Instalar dependências do Composer
print_status "Instalando dependências do Composer..."
$COMPOSER_CMD install --optimize-autoloader --no-dev

if [ $? -ne 0 ]; then
    print_error "Falha ao instalar dependências do Composer"
    exit 1
fi

# 4. Gerar APP_KEY
print_status "Gerando APP_KEY..."
php artisan key:generate --force

if [ $? -ne 0 ]; then
    print_error "Falha ao gerar APP_KEY. Verifique se as dependências foram instaladas corretamente."
    exit 1
fi

# 5. Configurar permissões
print_status "Configurando permissões..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 6. Limpar e otimizar cache
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

# 7. Executar migrations (com confirmação)
print_warning "ATENÇÃO: As migrations serão executadas. Isso pode alterar a estrutura do banco de dados."
read -p "Deseja continuar? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Executando migrations..."
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        print_status "Migrations executadas com sucesso!"
    else
        print_error "Falha ao executar migrations. Verifique as configurações do banco de dados no .env"
        print_warning "Você pode executar as migrations manualmente depois: php artisan migrate --force"
    fi
else
    print_warning "Migrations puladas. Execute manualmente: php artisan migrate --force"
fi

# 8. Criar symlink para storage (se necessário)
if [ ! -L "public/storage" ]; then
    print_status "Criando symlink para storage..."
    php artisan storage:link
fi

# 9. Verificar configuração
print_status "Verificando configuração..."
php artisan about

print_status "✅ Deploy concluído com sucesso!"
print_warning "Próximos passos:"
echo "  1. Edite o arquivo .env com suas configurações reais:"
echo "     - APP_URL=https://seudominio.com"
echo "     - Configurações do banco de dados MySQL"
echo "     - Configurações de email (se necessário)"
echo "  2. Configure o Document Root no painel do DreamHost para apontar para a pasta 'public'"
echo "  3. Configure SSL/HTTPS no painel do DreamHost"
echo "  4. Teste a aplicação no navegador"

print_status "📝 Comandos úteis:"
echo "  - Editar .env: nano .env"
echo "  - Ver logs: tail -f storage/logs/laravel.log"
echo "  - Executar migrations: php artisan migrate --force"
echo "  - Limpar cache: php artisan optimize:clear"

print_status "🎉 Aplicação pronta para configuração final!"