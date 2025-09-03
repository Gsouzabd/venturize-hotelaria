#!/bin/bash

# Start script para Render.com
# Este script é executado quando a aplicação inicia

set -e  # Sair se algum comando falhar

echo "🚀 Iniciando aplicação no Render.com..."

# 1. Aguardar banco de dados estar disponível
echo "🔍 Verificando conexão com banco de dados..."
php artisan tinker --execute="\DB::connection()->getPdo(); echo 'Conexão OK';" || {
    echo "❌ Erro na conexão com banco de dados"
    exit 1
}

# 2. Executar migrações
echo "🗄️ Executando migrações..."
php artisan migrate --force

# 3. Verificar se há seeders para executar (opcional)
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "🌱 Executando seeders..."
    php artisan db:seed --force
fi

# 4. Limpar e otimizar caches
echo "🧹 Otimizando caches..."
php artisan optimize

# 5. Criar link simbólico para storage (se necessário)
if [ ! -L "public/storage" ]; then
    echo "🔗 Criando link simbólico para storage..."
    php artisan storage:link
fi

echo "✅ Aplicação configurada com sucesso!"
echo "🌐 Iniciando servidor web..."

# 6. Iniciar servidor Apache com PHP
exec vendor/bin/heroku-php-apache2 public/