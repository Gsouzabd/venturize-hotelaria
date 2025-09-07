# Guia de Deploy no DreamHost

Este guia fornece instruções detalhadas para hospedar seu projeto Laravel no DreamHost.

## 📋 Pré-requisitos

- Conta no DreamHost com acesso SSH
- Projeto Laravel clonado no servidor
- Banco de dados MySQL configurado no painel do DreamHost

## 🚀 Passo a Passo

### 1. Configurar Banco de Dados MySQL

1. **Acesse o painel do DreamHost**
   - Vá para `Goodies > MySQL Databases`
   - Clique em `Add New Database`

2. **Criar o banco de dados**
   - Database Name: `venturize_hotelaria` (ou nome de sua escolha)
   - Use Hostname: deixe o padrão
   - First User: crie um usuário (ex: `venturize_user`)
   - Password: crie uma senha segura
   - Clique em `Add new database now!`

3. **Anotar as informações**
   - Hostname: geralmente `mysql.seudominio.com`
   - Database: nome do banco criado
   - Username: usuário criado
   - Password: senha definida

### 2. Configurar Estrutura de Pastas

**Estrutura recomendada no DreamHost:**
```
/home/username/
├── seudominio.com/          # Pasta do domínio (document root)
│   └── (arquivos da pasta public/)
└── laravel-app/             # Pasta do projeto Laravel
    ├── app/
    ├── config/
    ├── database/
    ├── public/                # Conteúdo vai para seudominio.com/
    └── ...
```

### 3. Executar o Script de Deploy

1. **Conectar via SSH**
   ```bash
   ssh username@seudominio.com
   ```

2. **Navegar para o diretório do projeto**
   ```bash
   cd ~/laravel-app  # ou onde você clonou o projeto
   ```

3. **Tornar o script executável**
   ```bash
   chmod +x deploy-dreamhost.sh
   ```

4. **Executar o script**
   ```bash
   ./deploy-dreamhost.sh
   ```

### 4. Configurar Variáveis de Ambiente

1. **Editar o arquivo .env**
   ```bash
   nano .env
   ```

2. **Configurar as principais variáveis:**
   ```env
   APP_NAME="Venturize Hotelaria"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://seudominio.com
   
   # Banco de dados (use as informações do passo 1)
   DB_CONNECTION=mysql
   DB_HOST=mysql.seudominio.com
   DB_PORT=3306
   DB_DATABASE=venturize_hotelaria
   DB_USERNAME=venturize_user
   DB_PASSWORD=sua_senha_aqui
   
   # Email (opcional - configurar depois)
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.dreamhost.com
   MAIL_PORT=587
   MAIL_USERNAME=noreply@seudominio.com
   MAIL_PASSWORD=senha_do_email
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@seudominio.com"
   ```

### 5. Configurar Document Root

1. **No painel do DreamHost:**
   - Vá para `Domains > Manage Domains`
   - Clique em `Edit` no seu domínio
   - Altere o `Web directory` para apontar para a pasta `public` do Laravel
   - Exemplo: `/home/username/laravel-app/public`
   - Salve as alterações

### 6. Configurar Symlinks (Alternativa)

Se preferir manter a estrutura padrão do DreamHost:

```bash
# Backup da pasta public original (se existir)
mv ~/seudominio.com ~/seudominio.com.backup

# Criar symlink da pasta public do Laravel
ln -s ~/laravel-app/public ~/seudominio.com
```

### 7. Executar Migrations

```bash
cd ~/laravel-app
php artisan migrate --force
```

### 8. Configurar Permissões

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 9. Otimizar para Produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Configurações Adicionais

### SSL/HTTPS

1. No painel do DreamHost:
   - Vá para `Domains > Secure Certificates`
   - Adicione um certificado SSL gratuito (Let's Encrypt)
   - Aguarde a ativação (pode levar algumas horas)

### Cron Jobs (se necessário)

1. No painel do DreamHost:
   - Vá para `Goodies > Cron Jobs`
   - Adicione: `cd /home/username/laravel-app && php artisan schedule:run`
   - Frequência: A cada minuto

### Email

1. Configure uma conta de email no DreamHost
2. Use as configurações SMTP no arquivo `.env`

## 🧪 Testar a Aplicação

1. **Acesse seu domínio no navegador**
2. **Verifique se a aplicação carrega corretamente**
3. **Teste funcionalidades principais**
4. **Verifique logs em caso de erro:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## 🚨 Solução de Problemas

### Erro 500
- Verifique permissões das pastas `storage` e `bootstrap/cache`
- Verifique o arquivo `.env`
- Consulte os logs: `storage/logs/laravel.log`

### Erro de Banco de Dados
- Verifique as credenciais no `.env`
- Teste a conexão: `php artisan tinker` → `DB::connection()->getPdo()`

### Problemas com Assets
- Execute: `php artisan storage:link`
- Verifique se os arquivos CSS/JS estão sendo servidos corretamente

### Performance
- Execute as otimizações: `php artisan optimize`
- Configure cache de configuração: `php artisan config:cache`

## 📝 Comandos Úteis

```bash
# Limpar todos os caches
php artisan optimize:clear

# Recriar caches otimizados
php artisan optimize

# Ver informações da aplicação
php artisan about

# Executar migrations
php artisan migrate --force

# Reverter migrations (cuidado!)
php artisan migrate:rollback

# Ver status das migrations
php artisan migrate:status
```

## 🔄 Atualizações Futuras

Para atualizar a aplicação:

1. **Fazer backup do banco de dados**
2. **Atualizar código:**
   ```bash
   git pull origin main
   ```
3. **Executar o script de deploy novamente:**
   ```bash
   ./deploy-dreamhost.sh
   ```

---

**✅ Pronto! Sua aplicação Laravel está rodando no DreamHost.**

Em caso de dúvidas, consulte a documentação do DreamHost ou os logs da aplicação.