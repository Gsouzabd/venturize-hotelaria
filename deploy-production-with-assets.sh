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
if composer install --optimize-autoloader --no-dev --no-interaction; then
    print_success "Dependências do Composer instaladas"
else
    print_error "Erro ao instalar dependências do Composer"
    exit 1
fi

# 2. Instalar dependências do NPM
echo "\n📦 Instalando dependências do NPM..."
if npm install --production=false; then
    print_success "Dependências do NPM instaladas"
else
    print_error "Erro ao instalar dependências do NPM"
    exit 1
fi

# 3. Compilar assets do Vite
echo "\n🏗️ Compilando assets do Vite..."
if npm run build; then
    print_success "Assets compilados com sucesso"
    echo "📁 Arquivos gerados em public/build/"
    ls -la public/build/
else
    print_error "Erro ao compilar assets"
    exit 1
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
    print_error "Manifest do Vite não encontrado!"
    exit 1
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