# 🗄️ Configuração do Banco de Dados DreamHost

## 🚨 Problema Identificado

**Erro:** Login falha com "usuário/senha inválido"  
**Causa:** Configurações do banco de dados no `.env` estão com valores placeholder

## 📋 Configurações Necessárias

### 1. **Localizar Credenciais no DreamHost**

No painel do DreamHost:
1. Acesse **Goodies** → **MySQL Databases**
2. Localize seu banco de dados
3. Anote as informações:
   - **Hostname:** (ex: `mysql.venturize.codebeans.dev`)
   - **Database:** (nome do banco)
   - **Username:** (usuário do banco)
   - **Password:** (senha do banco)

### 2. **Atualizar .env.dreamhost**

**Arquivo atual (com placeholders):**
```env
DB_CONNECTION=mysql
DB_HOST=mysql.your-domain.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**Exemplo de configuração correta:**
```env
DB_CONNECTION=mysql
DB_HOST=mysql.venturize.codebeans.dev
DB_PORT=3306
DB_DATABASE=venturize_hotelaria
DB_USERNAME=venturize_user
DB_PASSWORD=sua_senha_real_aqui
```

### 3. **Comandos para Atualizar no Servidor**

```bash
# No servidor DreamHost
cd ~/venturize.codebeans.dev

# Editar o arquivo .env.dreamhost
nano .env.dreamhost

# Ou editar diretamente o .env
nano .env

# Após editar, recriar o .env a partir do .env.dreamhost
cp .env.dreamhost .env

# Limpar cache de configuração
php artisan config:clear
php artisan config:cache
```

## 🔍 Verificações de Conexão

### Teste 1: Verificar Configuração
```bash
# Mostrar configurações do banco (sem senha)
php artisan config:show database
```

### Teste 2: Testar Conexão Direta
```bash
# Testar conexão via tinker
php artisan tinker

# No tinker, execute:
DB::connection()->getPdo();

# Se funcionar, mostrará: PDO object
# Se falhar, mostrará erro de conexão
```

### Teste 3: Verificar Tabelas
```bash
# Listar tabelas do banco
php artisan tinker

# No tinker:
DB::select('SHOW TABLES');
```

## 🔧 Possíveis Problemas e Soluções

### Problema 1: Host Incorreto
**Sintomas:** `Connection refused` ou `Unknown host`

**Soluções:**
- Verificar hostname correto no painel DreamHost
- Pode ser: `mysql.seudominio.com` ou IP direto
- Testar: `ping mysql.venturize.codebeans.dev`

### Problema 2: Credenciais Incorretas
**Sintomas:** `Access denied for user`

**Soluções:**
- Verificar username/password no painel DreamHost
- Recriar usuário se necessário
- Verificar permissões do usuário

### Problema 3: Banco Não Existe
**Sintomas:** `Unknown database`

**Soluções:**
- Verificar nome exato do banco
- Criar banco se necessário
- Verificar se usuário tem acesso ao banco

### Problema 4: Firewall/Porta
**Sintomas:** `Connection timed out`

**Soluções:**
- Verificar se porta 3306 está aberta
- Alguns hosts usam portas diferentes
- Verificar se conexões externas são permitidas

## 📝 Checklist de Configuração

- [ ] ✅ Credenciais corretas no `.env.dreamhost`
- [ ] ✅ Arquivo `.env` atualizado (`cp .env.dreamhost .env`)
- [ ] ✅ Cache limpo (`php artisan config:clear`)
- [ ] ✅ Conexão testada (`php artisan tinker`)
- [ ] ✅ Tabelas verificadas (`SHOW TABLES`)
- [ ] ✅ Login testado na aplicação

## 🚨 Comandos de Emergência

```bash
# Se tudo falhar, reconfigurar do zero
cd ~/venturize.codebeans.dev

# Backup do .env atual
cp .env .env.backup

# Recriar .env
cp .env.dreamhost .env

# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recriar caches
php artisan config:cache

# Testar conexão
php artisan tinker
# DB::connection()->getPdo();
```

## 📞 Próximos Passos

1. **Localizar credenciais** no painel DreamHost
2. **Atualizar .env.dreamhost** com valores reais
3. **Copiar para .env** no servidor
4. **Limpar cache** de configuração
5. **Testar conexão** via tinker
6. **Testar login** na aplicação

---

**🔑 Lembre-se:** As credenciais do banco são diferentes das credenciais de FTP/SSH!