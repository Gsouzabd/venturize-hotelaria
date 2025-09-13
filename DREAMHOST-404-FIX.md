# Guia de Correção do Erro 404 - DreamHost

## Problema Identificado
O erro 404 "The requested URL was not found on this server" indica que o **Document Root** não está configurado corretamente no painel do DreamHost.

## Solução Imediata

### 1. Configurar Document Root no Painel DreamHost

**PASSO A PASSO CRÍTICO:**

1. **Acesse o Painel DreamHost**
   - Login: https://panel.dreamhost.com

2. **Navegue para Domains**
   - Menu: `Domains` → `Manage Domains`

3. **Edite o Domínio**
   - Encontre: `venturize.codebeans.dev`
   - Clique em: **Edit**

4. **Altere o Web Directory**
   - **ATUAL (INCORRETO):** `/home/dh_pousada/venturize.codebeans.dev`
   - **NOVO (CORRETO):** `/home/dh_pousada/venturize.codebeans.dev/public`
   - ⚠️ **IMPORTANTE:** Adicione `/public` no final

5. **Salve as Alterações**
   - Clique em: **Change settings**
   - Aguarde: 5-15 minutos para propagação

### 2. Verificar Estrutura no Servidor

Certifique-se de que existe:
```
/home/dh_pousada/venturize.codebeans.dev/
├── app/
├── config/
├── database/
├── public/          ← Document Root deve apontar AQUI
│   ├── index.php    ← Arquivo principal do Laravel
│   ├── .htaccess
│   └── assets/
├── vendor/
├── .env
└── storage/
```

### 3. Verificar Arquivo index.php

O arquivo `/home/dh_pousada/venturize.codebeans.dev/public/index.php` deve existir e conter:
```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
```

### 4. Verificar Arquivo .htaccess

O arquivo `/home/dh_pousada/venturize.codebeans.dev/public/.htaccess` deve conter:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Comandos de Verificação

### No Servidor DreamHost (via SSH):
```bash
# Verificar se o arquivo index.php existe
ls -la /home/dh_pousada/venturize.codebeans.dev/public/index.php

# Verificar permissões
ls -la /home/dh_pousada/venturize.codebeans.dev/public/

# Limpar cache do Laravel
cd /home/dh_pousada/venturize.codebeans.dev
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Checklist de Verificação

- [ ] Document Root alterado para `/home/dh_pousada/venturize.codebeans.dev/public`
- [ ] Arquivo `public/index.php` existe
- [ ] Arquivo `public/.htaccess` existe
- [ ] Permissões corretas (755 para pastas, 644 para arquivos)
- [ ] Cache do Laravel limpo
- [ ] Aguardado 5-15 minutos após alteração do Document Root

## Teste Final

Após as alterações:
1. Aguarde 5-15 minutos
2. Acesse: `https://venturize.codebeans.dev`
3. Deve aparecer a página inicial do Laravel
4. Teste: `https://venturize.codebeans.dev/admin`

## Problemas Comuns

### Se ainda der 404:
1. **Verifique se salvou as alterações** no painel DreamHost
2. **Aguarde mais tempo** - pode levar até 30 minutos
3. **Limpe o cache do navegador**
4. **Verifique se os arquivos foram enviados** corretamente

### Se der erro 500:
1. Verifique o arquivo `.env`
2. Verifique permissões das pastas `storage/` e `bootstrap/cache/`
3. Verifique logs em `storage/logs/laravel.log`

## Contato de Emergência
Se o problema persistir, verifique os logs do servidor no painel DreamHost em:
`Goodies` → `Error Logs`