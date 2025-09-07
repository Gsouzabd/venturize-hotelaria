# Solução de Problemas - DreamHost

## 🚨 Erro: Composer não encontrado

**Problema:** `composer: command not found`

**Solução:**

1. **Use o script corrigido:**
   ```bash
   chmod +x deploy-dreamhost-fix.sh
   ./deploy-dreamhost-fix.sh
   ```

2. **Ou instale manualmente:**
   ```bash
   # Baixar Composer
   curl -sS https://getcomposer.org/installer | php
   
   # Usar diretamente como composer.phar
   php composer.phar --version
   ```

## 🚨 Erro: vendor/autoload.php não encontrado

**Problema:** `Failed opening required 'vendor/autoload.php'`

**Causa:** Dependências do Composer não foram instaladas.

**Solução:**
```bash
# Instalar dependências usando composer.phar
php composer.phar install --optimize-autoloader --no-dev

# Ou se o composer estiver instalado globalmente
composer install --optimize-autoloader --no-dev
```

## 🚨 Erro de Banco de Dados

**Problema:** Conexão com MySQL falha

**Solução:**

1. **Verificar configurações no .env:**
   ```bash
   nano .env
   ```

2. **Configurar corretamente:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql.seudominio.com  # Hostname do DreamHost
   DB_PORT=3306
   DB_DATABASE=nome_do_banco     # Nome criado no painel
   DB_USERNAME=usuario_banco     # Usuário criado no painel
   DB_PASSWORD=senha_banco       # Senha definida no painel
   ```

3. **Testar conexão:**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   exit
   ```

## 🚨 Erro de Permissões

**Problema:** Erro 500 ou problemas de escrita

**Solução:**
```bash
# Configurar permissões corretas
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 644 .env

# Se necessário, permissões mais amplas (cuidado!)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 🚨 Erro: APP_KEY não definida

**Problema:** `No application encryption key has been specified`

**Solução:**
```bash
php artisan key:generate --force
```

## 🚨 Erro: Rota não encontrada

**Problema:** Document Root não configurado corretamente

**Solução:**

1. **No painel do DreamHost:**
   - Domains > Manage Domains
   - Edit no seu domínio
   - Web directory: `/home/username/pousada/public`
   - Salvar

2. **Ou criar symlink:**
   ```bash
   # Backup da pasta atual
   mv ~/seudominio.com ~/seudominio.com.backup
   
   # Criar symlink
   ln -s ~/pousada/public ~/seudominio.com
   ```

## 🚨 Erro: Assets não carregam

**Problema:** CSS/JS não aparecem

**Solução:**

1. **Criar symlink do storage:**
   ```bash
   php artisan storage:link
   ```

2. **Verificar APP_URL no .env:**
   ```env
   APP_URL=https://seudominio.com
   ```

3. **Limpar cache:**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   ```

## 🚨 Erro: Migrations falham

**Problema:** Erro ao executar migrations

**Solução:**

1. **Verificar conexão com banco:**
   ```bash
   php artisan migrate:status
   ```

2. **Executar migrations passo a passo:**
   ```bash
   php artisan migrate --step
   ```

3. **Se necessário, resetar:**
   ```bash
   php artisan migrate:fresh --force
   ```

## 🚨 Erro: Memory Limit

**Problema:** `Fatal error: Allowed memory size exhausted`

**Solução:**

1. **Criar arquivo .htaccess na raiz:**
   ```apache
   php_value memory_limit 256M
   php_value max_execution_time 300
   ```

2. **Ou otimizar Composer:**
   ```bash
   php composer.phar install --optimize-autoloader --no-dev --classmap-authoritative
   ```

## 🚨 Erro: SSL/HTTPS

**Problema:** Site não carrega com HTTPS

**Solução:**

1. **No painel do DreamHost:**
   - Domains > Secure Certificates
   - Add certificate (Let's Encrypt - gratuito)
   - Aguardar ativação

2. **Forçar HTTPS no .env:**
   ```env
   APP_URL=https://seudominio.com
   ```

## 📋 Comandos de Diagnóstico

```bash
# Verificar status geral
php artisan about

# Ver logs de erro
tail -f storage/logs/laravel.log

# Testar configuração
php artisan config:show

# Verificar rotas
php artisan route:list

# Testar banco de dados
php artisan migrate:status

# Limpar todos os caches
php artisan optimize:clear
```

## 🆘 Se nada funcionar

1. **Verificar logs do servidor:**
   - No painel DreamHost: Logs > Error Logs

2. **Verificar versão do PHP:**
   ```bash
   php -v
   ```
   - Laravel requer PHP 8.1+

3. **Recriar projeto do zero:**
   ```bash
   # Backup do .env
   cp .env .env.backup
   
   # Limpar tudo
   rm -rf vendor/
   rm -rf bootstrap/cache/*
   rm -rf storage/framework/cache/*
   rm -rf storage/framework/sessions/*
   rm -rf storage/framework/views/*
   
   # Reinstalar
   php composer.phar install --optimize-autoloader --no-dev
   cp .env.backup .env
   php artisan key:generate --force
   php artisan migrate --force
   ```

## 📞 Suporte

- **DreamHost Support:** https://help.dreamhost.com/
- **Laravel Docs:** https://laravel.com/docs
- **Logs da aplicação:** `storage/logs/laravel.log`