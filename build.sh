#!/bin/bash

# Build script para Render.com
# Este script é executado durante o deploy

set -e  # Sair se algum comando falhar

echo "🚀 Iniciando build para Render.com..."

# 1. Instalar dependências PHP
echo "📦 Instalando dependências PHP..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 2. Instalar dependências Node.js
echo "📦 Instalando dependências Node.js..."
npm ci --only=production

# 3. Compilar assets
echo "🔨 Compilando assets..."
npm run build

# 4. Configurar permissões
echo "🔐 Configurando permissões..."
chmod -R 775 storage bootstrap/cache

# 5. Otimizar Laravel
echo "⚡ Otimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Limpar caches desnecessários
echo "🧹 Limpando caches..."
php artisan clear-compiled

echo "✅ Build concluído com sucesso!"
echo "🌐 Aplicação pronta para produção no Render.com"