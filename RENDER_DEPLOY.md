# Deploy no Render.com - Venturize Hotelaria

Este guia explica como fazer o deploy da aplicação Laravel no Render.com.

## Por que Render.com?

- ✅ **MySQL e PostgreSQL** nativos e gratuitos
- ✅ **SSL automático** e domínios personalizados
- ✅ **Deploy automático** via Git
- ✅ **Plano gratuito** generoso (750 horas/mês)
- ✅ **Melhor performance** que Heroku free tier
- ✅ **Logs em tempo real** e monitoramento

## Pré-requisitos

1. Conta no [Render.com](https://render.com)
2. Repositório Git (GitHub, GitLab, ou Bitbucket)
3. Código commitado e enviado para o repositório

## Opções de Deploy

### Opção 1: Deploy Automático via render.yaml (Recomendado)

1. **Conectar Repositório**
   - Acesse [Render Dashboard](https://dashboard.render.com)
   - Clique em "New" → "Blueprint"
   - Conecte seu repositório GitHub/GitLab
   - O Render detectará automaticamente o arquivo `render.yaml`

2. **Configurar Variáveis de Ambiente**
   - `APP_KEY`: Será gerado automaticamente
   - `DATABASE_URL`: Configurado automaticamente pelo MySQL/PostgreSQL
   - Outras variáveis já estão no `render.yaml`

3. **Deploy**
   - Clique em "Apply" para iniciar o deploy
   - O Render criará automaticamente:
     - Web Service (aplicação Laravel)
     - Worker Service (filas)
     - MySQL Database

### Opção 2: Deploy Manual

#### 1. Criar Database

1. No Dashboard do Render, clique em "New" → "MySQL" (ou "PostgreSQL")
2. Configure:
   - **Name**: `venturize-hotelaria-db`
   - **Database Name**: `venturize_hotelaria`
   - **User**: `venturize_user`
   - **Plan**: Free
3. Anote as credenciais geradas

#### 2. Criar Web Service

1. Clique em "New" → "Web Service"
2. Conecte seu repositório
3. Configure:
   - **Name**: `venturize-hotelaria`
   - **Runtime**: `PHP`
   - **Build Command**:
     ```bash
     composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   - **Start Command**:
     ```bash
     php artisan migrate --force && vendor/bin/heroku-php-apache2 public/
     ```

#### 3. Configurar Variáveis de Ambiente

Adicione estas variáveis no painel do Web Service:

```env
APP_NAME=Venturize Hotelaria
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:CHAVE_GERADA_AUTOMATICAMENTE
APP_URL=https://seu-app.onrender.com
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR

# Database (use as credenciais do MySQL criado)
DATABASE_URL=mysql://usuario:senha@host:port/database
# OU configure individualmente:
DB_CONNECTION=mysql
DB_HOST=seu-mysql-host
DB_PORT=3306
DB_DATABASE=venturize_hotelaria
DB_USERNAME=venturize_user
DB_PASSWORD=sua-senha

# Cache e Sessões
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Logs
LOG_CHANNEL=errorlog

# Impressoras (desabilitadas por padrão)
PRINTER_ENABLED=false

# Email
MAIL_MAILER=log
```

#### 4. Criar Worker Service (Opcional)

Para processar filas em background:

1. Clique em "New" → "Background Worker"
2. Configure:
   - **Name**: `venturize-hotelaria-worker`
   - **Build Command**: `composer install --no-dev --optimize-autoloader`
   - **Start Command**: `php artisan queue:work --verbose --tries=3 --timeout=90`
3. Use as mesmas variáveis de ambiente do Web Service

## Configurações Importantes

### 1. Gerar APP_KEY

Se não foi gerado automaticamente:

```bash
# Localmente
php artisan key:generate --show

# Copie a chave gerada e adicione como variável de ambiente
```

### 2. Configurar Domínio Personalizado

1. No painel do Web Service, vá em "Settings" → "Custom Domains"
2. Adicione seu domínio
3. Configure os DNS conforme instruções do Render

### 3. Configurar SSL

O SSL é automático no Render.com, mas você pode forçar HTTPS:

```php
// No AppServiceProvider.php
use Illuminate\Support\Facades\URL;

public function boot()
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

## Comandos Úteis

### Executar Comandos Artisan

1. Acesse o painel do Web Service
2. Vá em "Shell" (terminal)
3. Execute comandos:

```bash
# Executar migrações
php artisan migrate

# Executar seeders
php artisan db:seed

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver logs
tail -f storage/logs/laravel.log
```

### Monitoramento

- **Logs**: Disponíveis em tempo real no painel
- **Métricas**: CPU, memória, requests/segundo
- **Alertas**: Configure notificações por email

## Estrutura de Arquivos para Render

- `render.yaml`: Configuração automática (Blueprint)
- `.env.example`: Template das variáveis de ambiente
- `composer.json`: Dependências PHP
- `package.json`: Dependências Node.js

## Troubleshooting

### Erro de Chave da Aplicação

```bash
# Gere uma nova chave
php artisan key:generate --show
# Adicione como variável APP_KEY
```

### Erro de Permissões de Storage

```bash
# No build command, adicione:
chmod -R 775 storage bootstrap/cache
```

### Problemas com Assets

```bash
# Certifique-se que o build command inclui:
npm ci && npm run build
```

### Erro de Conexão com Banco

1. Verifique se o DATABASE_URL está correto
2. Teste a conexão no Shell:

```bash
php artisan tinker
\DB::connection()->getPdo();
```

## Vantagens do Render vs Heroku

| Recurso | Render.com | Heroku |
|---------|------------|--------|
| **Plano Gratuito** | 750h/mês | 550h/mês |
| **MySQL Gratuito** | ✅ Nativo | ❌ Add-on pago |
| **PostgreSQL** | ✅ Nativo | ✅ Nativo |
| **SSL Automático** | ✅ Sim | ✅ Sim |
| **Deploy Automático** | ✅ Git push | ✅ Git push |
| **Domínio Personalizado** | ✅ Gratuito | ✅ Gratuito |
| **Logs** | ✅ Tempo real | ✅ Limitado |
| **Performance** | ✅ Melhor | ❌ Sleep mode |

## Backup

### Backup do Banco de Dados

```bash
# MySQL
mysqldump -h HOST -u USER -p DATABASE > backup.sql

# PostgreSQL
pg_dump DATABASE_URL > backup.sql
```

### Restaurar Backup

```bash
# MySQL
mysql -h HOST -u USER -p DATABASE < backup.sql

# PostgreSQL
psql DATABASE_URL < backup.sql
```

## Monitoramento e Alertas

1. **Uptime Monitoring**: Configure no painel
2. **Performance Alerts**: CPU/Memória
3. **Error Tracking**: Integre com Sentry
4. **Log Monitoring**: Use ferramentas como Papertrail

## Considerações de Segurança

1. ✅ `APP_DEBUG=false` em produção
2. ✅ `APP_ENV=production`
3. ✅ HTTPS forçado
4. ✅ Variáveis de ambiente seguras
5. ✅ Dependências atualizadas
6. ✅ Logs configurados adequadamente

## Suporte

Para mais informações:
- [Documentação do Render](https://render.com/docs)
- [Guias PHP/Laravel](https://render.com/docs/deploy-php-laravel)
- [Comunidade Render](https://community.render.com)

---

**Sua aplicação está pronta para deploy no Render.com!** 🚀

Use o arquivo `render.yaml` para deploy automático ou siga o guia manual para mais controle.