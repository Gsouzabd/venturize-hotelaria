# Correção de Problemas no Deploy DreamHost

## Problemas Identificados

### 1. Arquivo .env.example não encontrado
```
cp: cannot stat '.env.example': No such file or directory
```

### 2. Comando composer não encontrado
```
./deploy-dreamhost.sh: line 51: composer: command not found
```

## Soluções

### Solução 1: Criar arquivo .env.example manualmente
```bash
# No servidor de produção, criar o arquivo .env.example
cat > .env.example << 'EOF'
APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
EOF
```

### Solução 2: Configurar Composer no DreamHost

#### Opção A: Usar Composer via PHP (Recomendado)
```bash
# Baixar composer.phar
curl -sS https://getcomposer.org/installer | php

# Criar alias ou usar diretamente
alias composer='php composer.phar'

# Ou mover para diretório local
mv composer.phar ~/bin/composer
chmod +x ~/bin/composer
```

#### Opção B: Modificar script para usar PHP diretamente
Editar o arquivo `deploy-dreamhost.sh` linha 51:
```bash
# Substituir:
composer install --no-dev --optimize-autoloader

# Por:
php composer.phar install --no-dev --optimize-autoloader
```

### Solução 3: Script de Deploy Corrigido
```bash
#!/bin/bash

# Script de deploy corrigido para DreamHost
echo "🚀 Iniciando deploy no DreamHost..."

# Verificar se composer.phar existe, se não, baixar
if [ ! -f "composer.phar" ]; then
    echo "[INFO] Baixando Composer..."
    curl -sS https://getcomposer.org/installer | php
fi

# Verificar se .env.example existe, se não, criar
if [ ! -f ".env.example" ]; then
    echo "[WARNING] Criando .env.example..."
    cat > .env.example << 'EOF'
APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://venturize.codebeans.dev

DB_CONNECTION=mysql
DB_HOST=mysql.venturize.codebeans.dev
DB_PORT=3306
DB_DATABASE=venturize_db
DB_USERNAME=venturize_user
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
EOF
fi

# Configurar .env
if [ -f ".env.dreamhost" ]; then
    cp .env.dreamhost .env
else
    cp .env.example .env
fi

# Gerar APP_KEY
php artisan key:generate --force

# Instalar dependências
echo "[INFO] Instalando dependências..."
php composer.phar install --no-dev --optimize-autoloader

# Configurar permissões
chmod -R 755 storage bootstrap/cache

# Limpar e otimizar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar migrações
echo "[INFO] Executando migrações..."
php artisan migrate --force

# Criar link simbólico para storage
php artisan storage:link

echo "✅ Deploy concluído com sucesso!"
```

## Comandos de Correção Rápida

### Para executar no servidor DreamHost:
```bash
# 1. Criar .env.example
cp .env.dreamhost .env.example 2>/dev/null || echo "APP_NAME=Laravel" > .env.example

# 2. Baixar Composer
curl -sS https://getcomposer.org/installer | php

# 3. Instalar dependências
php composer.phar install --no-dev --optimize-autoloader

# 4. Configurar ambiente
cp .env.dreamhost .env 2>/dev/null || cp .env.example .env
php artisan key:generate --force

# 5. Configurar permissões
chmod -R 755 storage bootstrap/cache

# 6. Otimizar aplicação
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Executar migrações
php artisan migrate --force

# 8. Criar link de storage
php artisan storage:link
```

## Verificações Pós-Deploy

### 1. Verificar estrutura
```bash
ls -la
ls -la storage/
ls -la bootstrap/cache/
```

### 2. Testar aplicação
```bash
php artisan --version
php artisan config:show app.url
```

### 3. Verificar logs
```bash
tail -f storage/logs/laravel.log
```

## Configuração do Servidor Web

Certifique-se de que o **Document Root** aponta para:
```
/home/dh_pousada/venturize.codebeans.dev/public
```

## Troubleshooting Adicional

### Se ainda houver erro 404:
1. Verificar se `.htaccess` existe em `public/`
2. Verificar se mod_rewrite está habilitado
3. Verificar permissões dos diretórios
4. Verificar configuração do banco de dados

### Se houver erro de banco:
1. Verificar credenciais no `.env`
2. Testar conexão: `php artisan tinker` → `DB::connection()->getPdo()`
3. Verificar se o banco existe no painel DreamHost

## Próximos Passos
1. Executar os comandos de correção rápida
2. Testar o site em https://venturize.codebeans.dev
3. Verificar funcionalidades críticas
4. Monitorar logs por alguns minutos