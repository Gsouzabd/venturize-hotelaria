# Configuração de Variáveis de Ambiente - DreamHost

## 🎉 Deploy Concluído com Sucesso!

Seu projeto Laravel foi instalado com sucesso no DreamHost. Agora você precisa configurar as variáveis de ambiente para conectar ao banco de dados MySQL.

## 📝 Configuração do Arquivo .env

### 1. Editar o arquivo .env no servidor
```bash
nano .env
```

### 2. Configurações Essenciais do MySQL

Substitua as seguintes variáveis no arquivo `.env`:

```env
# Configurações da Aplicação
APP_NAME="Venturize Hotelaria"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_GERADA_AUTOMATICAMENTE
APP_DEBUG=false
APP_URL=https://seudominio.com
APP_TIMEZONE="America/Sao_Paulo"
APP_LOCALE=pt_BR

# Configurações do Banco de Dados MySQL (DreamHost)
DB_CONNECTION=mysql
DB_HOST=mysql.seudominio.com
DB_PORT=3306
DB_DATABASE=nome_do_seu_banco
DB_USERNAME=seu_usuario_mysql
DB_PASSWORD=sua_senha_mysql

# Configurações de Cache e Sessão
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Configurações de Email (opcional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.dreamhost.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@seudominio.com
MAIL_PASSWORD=sua_senha_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu_email@seudominio.com
MAIL_FROM_NAME="Venturize Hotelaria"
```

## 🔍 Como Obter as Informações do MySQL no DreamHost

### 1. Acesse o Painel do DreamHost
- Faça login no painel de controle do DreamHost
- Vá para **Goodies > MySQL Databases**

### 2. Informações que você precisa:
- **DB_HOST**: Geralmente é `mysql.seudominio.com` ou um hostname específico
- **DB_DATABASE**: Nome do banco de dados que você criou
- **DB_USERNAME**: Nome de usuário do MySQL
- **DB_PASSWORD**: Senha do usuário MySQL

### 3. Se você ainda não criou o banco:
1. No painel do DreamHost, vá para **Goodies > MySQL Databases**
2. Clique em **Add new database**
3. Preencha:
   - **Database Name**: `venturize_hotelaria` (ou nome de sua escolha)
   - **Use Hostname**: deixe o padrão ou escolha um hostname
   - **First User**: crie um usuário (ex: `venturize_user`)
   - **Password**: crie uma senha segura
4. Clique em **Add new database now!**

## ⚡ Próximos Passos Após Configurar o .env

### 1. Executar as Migrations
```bash
php artisan migrate --force
```

### 2. Executar os Seeders (opcional)
```bash
php artisan db:seed --force
```

### 3. Limpar e Recriar Cache
```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Testar Conexão com o Banco
```bash
php artisan tinker
# No tinker, execute:
DB::connection()->getPdo();
# Se não der erro, a conexão está funcionando!
exit
```

## 🌐 Configuração do Document Root

### No Painel do DreamHost:
1. Vá para **Domains > Manage Domains**
2. Clique em **Edit** no seu domínio
3. Em **Web directory**, altere para: `/home/seuusuario/seudominio.com/public`
4. Salve as alterações

## 🔒 Configuração SSL/HTTPS

### No Painel do DreamHost:
1. Vá para **Domains > Secure Certificates**
2. Clique em **Add** para seu domínio
3. Escolha **Let's Encrypt SSL** (gratuito)
4. Aguarde a ativação (pode levar alguns minutos)

## 🧪 Testando a Aplicação

### 1. Acesse seu domínio no navegador
```
https://seudominio.com
```

### 2. Verificar logs em caso de erro
```bash
tail -f storage/logs/laravel.log
```

### 3. Comandos úteis para debug
```bash
# Ver informações da aplicação
php artisan about

# Verificar rotas
php artisan route:list

# Limpar todos os caches
php artisan optimize:clear
```

## 🚨 Problemas Comuns

### Erro de Conexão com Banco
- Verifique se as credenciais estão corretas no `.env`
- Confirme se o banco de dados foi criado no painel do DreamHost
- Teste a conexão com: `php artisan tinker` → `DB::connection()->getPdo();`

### Erro 500 - Internal Server Error
- Verifique os logs: `tail -f storage/logs/laravel.log`
- Confirme se as permissões estão corretas: `chmod -R 755 storage bootstrap/cache`
- Verifique se o Document Root aponta para a pasta `public`

### Assets não carregam (CSS/JS)
- Confirme se o `APP_URL` no `.env` está correto
- Execute: `php artisan config:clear && php artisan config:cache`

## 🎯 Checklist Final

- [ ] Arquivo `.env` configurado com credenciais do MySQL
- [ ] Migrations executadas com sucesso
- [ ] Document Root configurado para pasta `public`
- [ ] SSL/HTTPS ativado
- [ ] Aplicação acessível no navegador
- [ ] Logs sem erros críticos

**🎉 Parabéns! Sua aplicação Laravel está rodando no DreamHost!**