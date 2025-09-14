# Troubleshooting - Erro 404 em Produção

## Problema
A aplicação apresenta erro 404 em produção para URLs que funcionam localmente.

## Causa Provável
Após fazer o clone do projeto em produção, alguns passos essenciais não foram executados.

## ✅ Checklist Completo - Passos Obrigatórios

### 1. Instalar Dependências do Composer
```bash
# Instalar dependências PHP (OBRIGATÓRIO)
composer install --optimize-autoloader --no-dev
```

### 2. Configurar Arquivo .env
```bash
# Copiar arquivo de configuração
cp .env.example .env
# OU se existir arquivo específico para produção:
cp .env.dreamhost .env

# Gerar chave da aplicação (OBRIGATÓRIO)
php artisan key:generate --force
```

### 3. Configurar Permissões
```bash
# Configurar permissões das pastas (OBRIGATÓRIO)
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 4. Executar Migrações
```bash
# Executar migrações do banco de dados
php artisan migrate --force
```

### 5. Otimizar para Produção
```bash
# Limpar caches existentes
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Criar caches otimizados para produção (IMPORTANTE)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Criar Link Simbólico para Storage
```bash
# Criar link para arquivos públicos
php artisan storage:link
```

### 7. Instalar e Compilar Assets (Se Necessário)
```bash
# Se a aplicação usa Vite/Node.js
npm install --production
npm run build
```

## 🚨 Principais Causas do Erro 404

### 1. **Autoload do Composer não configurado**
- **Sintoma**: Classes não encontradas, erro 500 ou 404
- **Solução**: `composer install --optimize-autoloader --no-dev`

### 2. **Cache de rotas desatualizado**
- **Sintoma**: Rotas não funcionam, erro 404 para rotas existentes
- **Solução**: 
  ```bash
  php artisan route:clear
  php artisan route:cache
  ```

### 3. **Arquivo .env não configurado**
- **Sintoma**: Erro 500, configurações não carregam
- **Solução**: Copiar `.env.example` para `.env` e configurar

### 4. **APP_KEY não gerada**
- **Sintoma**: Erro de criptografia, sessões não funcionam
- **Solução**: `php artisan key:generate --force`

### 5. **Permissões incorretas**
- **Sintoma**: Erro de escrita, cache não funciona
- **Solução**: `chmod -R 755 storage bootstrap/cache`

### 6. **Configuração do servidor web**
- **Sintoma**: Apenas a página inicial funciona
- **Solução**: Verificar se o documento root aponta para a pasta `public/`

## 🔧 Script Automático de Deploy

Use o script já criado no projeto:

```bash
# Para DreamHost
bash deploy-dreamhost.sh

# OU versão corrigida
bash deploy-dreamhost-fix.sh
```

## 🌐 Configuração do Servidor Web

### Apache (.htaccess já configurado)
O arquivo `public/.htaccess` já está configurado corretamente.

### Nginx (se aplicável)
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/para/projeto/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔍 Verificações Adicionais

### 1. Verificar se o projeto está funcionando
```bash
php artisan about
```

### 2. Testar rotas
```bash
php artisan route:list
```

### 3. Verificar logs de erro
```bash
tail -f storage/logs/laravel.log
```

### 4. Testar conexão com banco
```bash
php artisan tinker
# No tinker:
\DB::connection()->getPdo();
```

## 📋 Variáveis de Ambiente Importantes

Verifique se estas variáveis estão configuradas no `.env`:

```env
APP_NAME="Venturize Hotelaria"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## 🎯 Solução Rápida

Se você acabou de fazer o clone, execute estes comandos na ordem:

```bash
# 1. Instalar dependências
composer install --optimize-autoloader --no-dev

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate --force

# 3. Configurar permissões
chmod -R 755 storage bootstrap/cache

# 4. Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Executar migrações
php artisan migrate --force

# 6. Criar link do storage
php artisan storage:link
```

## ❗ Importante

- **NUNCA** execute `composer install` sem a flag `--no-dev` em produção
- **SEMPRE** use `--force` nos comandos artisan em produção
- **SEMPRE** configure as permissões corretas
- **SEMPRE** gere os caches de produção
- **VERIFIQUE** se o documento root do servidor aponta para a pasta `public/`

## 🆘 Se Ainda Não Funcionar

1. Verifique os logs do servidor web
2. Verifique os logs do Laravel: `storage/logs/laravel.log`
3. Teste com `php artisan serve` temporariamente
4. Verifique se o PHP está na versão correta (8.2+)
5. Verifique se as extensões PHP necessárias estão instaladas

---

**Resumo**: O erro 404 em produção geralmente acontece porque os passos de build/deploy não foram executados após o clone. Execute o script de deploy ou siga o checklist acima.
