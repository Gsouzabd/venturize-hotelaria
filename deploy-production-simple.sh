#!/bin/bash

# Script de Deploy Simples para Produção - Venturize Hotelaria
# Para servidores sem NPM/Node.js - apenas PHP

echo "🚀 Iniciando deploy simples para produção..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para exibir mensagens coloridas
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    print_error "Arquivo artisan não encontrado. Execute este script na raiz do projeto Laravel."
    exit 1
fi

echo "📁 Diretório atual: $(pwd)"

# 1. Instalar dependências do Composer
echo "\n📦 Instalando dependências do Composer..."

# Tentar diferentes caminhos do composer no DreamHost
COMPOSER_CMD=""
if command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
elif [ -f "/usr/local/bin/composer" ]; then
    COMPOSER_CMD="/usr/local/bin/composer"
elif [ -f "/opt/cpanel/composer/bin/composer" ]; then
    COMPOSER_CMD="/opt/cpanel/composer/bin/composer"
elif [ -f "$HOME/.config/composer/vendor/bin/composer" ]; then
    COMPOSER_CMD="$HOME/.config/composer/vendor/bin/composer"
else
    print_error "Composer não encontrado. Instalando composer localmente..."
    # Baixar e instalar composer localmente
    curl -sS https://getcomposer.org/installer | php
    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
        print_success "Composer instalado localmente"
    else
        print_error "Falha ao instalar composer"
        exit 1
    fi
fi

echo "📍 Usando composer: $COMPOSER_CMD"

if $COMPOSER_CMD install --optimize-autoloader --no-dev --no-interaction; then
    print_success "Dependências do Composer instaladas"
else
    print_error "Erro ao instalar dependências do Composer"
    exit 1
fi

# 2. Configurar arquivo .env
echo "\n⚙️ Configurando ambiente..."
if [ ! -f ".env" ]; then
    if [ -f ".env.dreamhost" ]; then
        cp .env.dreamhost .env
        print_success "Arquivo .env criado a partir de .env.dreamhost"
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        print_warning "Arquivo .env criado a partir de .env.example - CONFIGURE AS VARIÁVEIS!"
    else
        print_error "Nenhum arquivo .env encontrado"
        exit 1
    fi
else
    print_success "Arquivo .env já existe"
fi

# 3. Gerar chave da aplicação
echo "\n🔑 Gerando chave da aplicação..."
if php artisan key:generate --force; then
    print_success "Chave da aplicação gerada"
else
    print_error "Erro ao gerar chave da aplicação"
    exit 1
fi

# 4. Configurar permissões
echo "\n🔒 Configurando permissões..."
chmod -R 755 storage bootstrap/cache
print_success "Permissões configuradas"

# 5. Limpar cache
echo "\n🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Cache limpo"

# 6. Otimizar para produção
echo "\n⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Otimizações aplicadas"

# 7. Executar migrações
echo "\n🗄️ Executando migrações..."
if php artisan migrate --force; then
    print_success "Migrações executadas"
else
    print_warning "Erro ao executar migrações - verifique a configuração do banco"
fi

# 8. Criar link do storage
echo "\n🔗 Criando link do storage..."
if php artisan storage:link; then
    print_success "Link do storage criado"
else
    print_warning "Erro ao criar link do storage - pode já existir"
fi

# 9. Verificar estrutura de arquivos
echo "\n📋 Verificando estrutura de arquivos..."
if [ -f "public/index.php" ]; then
    print_success "public/index.php encontrado"
else
    print_error "public/index.php não encontrado!"
fi

if [ -f "public/.htaccess" ]; then
    print_success "public/.htaccess encontrado"
else
    print_warning "public/.htaccess não encontrado - pode ser necessário"
fi

echo "\n🎉 Deploy simples concluído!"
echo "\n📝 Próximos passos:"
echo "   1. Configure o Document Root para apontar para a pasta 'public/'"
echo "   2. Verifique as configurações do .env"
echo "   3. Faça upload dos assets compilados (pasta public/build/) se necessário"
echo "   4. Teste a aplicação"

print_success "Deploy finalizado!"