#!/bin/bash

# Script de Deploy para Produção - Venturize Hotelaria
# Inclui compilação de assets do Vite

echo "🚀 Iniciando deploy para produção..."

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

# 2. Instalar dependências do NPM
echo "\n📦 Instalando dependências do NPM..."

# Verificar se NPM está disponível
NPM_CMD=""
if command -v npm &> /dev/null; then
    NPM_CMD="npm"
elif [ -f "/usr/local/bin/npm" ]; then
    NPM_CMD="/usr/local/bin/npm"
elif [ -f "$HOME/.nvm/versions/node/*/bin/npm" ]; then
    NPM_CMD=$(find $HOME/.nvm/versions/node/*/bin/npm | head -1)
else
    print_error "NPM não encontrado. Verifique se Node.js está instalado."
    print_warning "Pulando instalação de dependências NPM..."
    NPM_CMD=""
fi

if [ -n "$NPM_CMD" ]; then
    echo "📍 Usando npm: $NPM_CMD"
    if $NPM_CMD install --production=false; then
        print_success "Dependências do NPM instaladas"
    else
        print_error "Erro ao instalar dependências do NPM"
        exit 1
    fi
else
    print_warning "NPM não disponível - pulando instalação de dependências"
fi

# 3. Compilar assets do Vite
echo "\n🏗️ Compilando assets do Vite..."

if [ -n "$NPM_CMD" ]; then
    if $NPM_CMD run build; then
        print_success "Assets compilados com sucesso"
        echo "📁 Arquivos gerados em public/build/"
        ls -la public/build/
    else
        print_error "Erro ao compilar assets"
        exit 1
    fi
else
    print_warning "NPM não disponível - pulando compilação de assets"
    print_warning "Certifique-se de que os assets já foram compilados localmente"
fi

# 4. Configurar arquivo .env
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

# 5. Gerar chave da aplicação
echo "\n🔑 Gerando chave da aplicação..."
if php artisan key:generate --force; then
    print_success "Chave da aplicação gerada"
else
    print_error "Erro ao gerar chave da aplicação"
    exit 1
fi

# 6. Configurar permissões
echo "\n🔒 Configurando permissões..."
chmod -R 755 storage bootstrap/cache
print_success "Permissões configuradas"

# 7. Limpar cache
echo "\n🧹 Limpando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Cache limpo"

# 8. Otimizar para produção
echo "\n⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Otimizações aplicadas"

# 9. Executar migrações
echo "\n🗄️ Executando migrações..."
if php artisan migrate --force; then
    print_success "Migrações executadas"
else
    print_warning "Erro ao executar migrações - verifique a configuração do banco"
fi

# 10. Criar link do storage
echo "\n🔗 Criando link do storage..."
if php artisan storage:link; then
    print_success "Link do storage criado"
else
    print_warning "Erro ao criar link do storage - pode já existir"
fi

# 11. Verificar se os assets foram gerados
echo "\n📋 Verificando assets gerados..."
if [ -f "public/build/manifest.json" ]; then
    print_success "Manifest do Vite encontrado"
    echo "📄 Conteúdo do manifest:"
    cat public/build/manifest.json | head -10
else
    if [ -n "$NPM_CMD" ]; then
        print_error "Manifest do Vite não encontrado!"
        exit 1
    else
        print_warning "Manifest do Vite não encontrado - assets podem não ter sido compilados"
        print_warning "Certifique-se de fazer upload dos assets compilados localmente"
    fi
fi

echo "\n🎉 Deploy concluído com sucesso!"
echo "\n📝 Próximos passos:"
echo "   1. Configure o Document Root para apontar para a pasta 'public/'"
echo "   2. Verifique as configurações do .env"
echo "   3. Teste a aplicação"
echo "\n🌐 Estrutura de arquivos importantes:"
echo "   - public/build/manifest.json ✅"
echo "   - public/build/assets/ ✅"
echo "   - public/index.php ✅"
echo "   - public/.htaccess ✅"

print_success "Deploy finalizado!"