# 🔐 Correção de Autenticação - DreamHost

## 🚨 Problema Identificado

**Erro:** Login falha com "usuário/senha inválido" mesmo com credenciais corretas

**Causas Identificadas:**
1. ❌ Configurações do banco de dados com valores placeholder
2. ❌ Modelo Usuario usa campo 'senha' mas Laravel espera 'password'
3. ❌ Método `getAuthPassword()` não implementado

## ✅ Correções Aplicadas

### 1. **Correção do Modelo Usuario**

**Problema:** Laravel não sabia qual campo usar para autenticação

**✅ Solução:** Adicionado método `getAuthPassword()` no modelo Usuario

```php
// Configurar o campo de senha para autenticação
public function getAuthPassword()
{
    return $this->senha;
}
```

**Arquivo:** `app/Models/Usuario.php`

### 2. **Configuração do Banco de Dados**

**Problema:** Arquivo `.env.dreamhost` com valores placeholder

**Valores atuais (INCORRETOS):**
```env
DB_HOST=mysql.your-domain.com
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

**✅ Solução:** Atualizar com credenciais reais do DreamHost

## 🔧 Passos para Resolver

### Passo 1: Atualizar Código no Servidor

```bash
# No servidor DreamHost
cd ~/venturize.codebeans.dev

# Fazer pull das correções
git pull origin main

# Ou copiar manualmente o arquivo Usuario.php corrigido
```

### Passo 2: Configurar Credenciais do Banco

**No painel DreamHost:**
1. Acesse **Goodies** → **MySQL Databases**
2. Localize seu banco de dados
3. Anote as credenciais:
   - **Hostname:** (ex: `mysql.venturize.codebeans.dev`)
   - **Database:** (nome do banco)
   - **Username:** (usuário do banco)
   - **Password:** (senha do banco)

**No servidor, editar .env.dreamhost:**
```bash
nano .env.dreamhost
```

**Exemplo de configuração correta:**
```env
DB_CONNECTION=mysql
DB_HOST=mysql.venturize.codebeans.dev
DB_PORT=3306
DB_DATABASE=venturize_hotelaria
DB_USERNAME=venturize_user
DB_PASSWORD=SUA_SENHA_REAL_AQUI
```

### Passo 3: Aplicar Configurações

```bash
# Copiar configurações
cp .env.dreamhost .env

# Limpar cache de configuração
php artisan config:clear
php artisan config:cache
```

### Passo 4: Executar Seeders (se necessário)

```bash
# Executar seeder do usuário admin
php artisan db:seed --class=UsuarioAdminSeeder
```

## 🧪 Testes de Verificação

### Teste 1: Conexão com Banco
```bash
php artisan tinker

# No tinker:
DB::connection()->getPdo();
# Deve retornar: PDO object
```

### Teste 2: Verificar Usuário Admin
```bash
php artisan tinker

# No tinker:
$user = App\Models\Usuario::where('email', 'danilo@pousada.com.br')->first();
dd($user);
```

### Teste 3: Testar Hash da Senha
```bash
php artisan tinker

# No tinker:
$user = App\Models\Usuario::where('email', 'danilo@pousada.com.br')->first();
Hash::check('admin', $user->senha);
# Deve retornar: true
```

### Teste 4: Testar Autenticação
```bash
php artisan tinker

# No tinker:
Auth::attempt(['email' => 'danilo@pousada.com.br', 'password' => 'admin']);
# Deve retornar: true
```

## 📋 Credenciais de Teste

**Usuário Admin (criado pelo seeder):**
- **Email:** `danilo@pousada.com.br`
- **Senha:** `admin`
- **Tipo:** `administrador`

## 🚨 Troubleshooting Adicional

### Se ainda não conseguir fazer login:

#### 1. Verificar se o usuário existe
```bash
php artisan tinker
App\Models\Usuario::where('email', 'danilo@pousada.com.br')->exists();
```

#### 2. Recriar usuário admin
```bash
php artisan tinker

# Deletar usuário existente (se houver)
App\Models\Usuario::where('email', 'danilo@pousada.com.br')->delete();

# Executar seeder novamente
exit
php artisan db:seed --class=UsuarioAdminSeeder
```

#### 3. Verificar logs de erro
```bash
tail -f storage/logs/laravel.log
```

#### 4. Testar com outro usuário
```bash
php artisan tinker

# Criar usuário de teste
App\Models\Usuario::create([
    'nome' => 'Teste',
    'email' => 'teste@teste.com',
    'senha' => 'teste123',
    'tipo' => 'administrador',
    'fl_ativo' => true,
]);
```

## 🔄 Comandos de Reset Completo

**Se nada funcionar, reset completo:**

```bash
# Limpar todos os caches
php artisan optimize:clear

# Recriar .env
cp .env.dreamhost .env

# Recriar caches
php artisan config:cache

# Executar migrations (se necessário)
php artisan migrate --force

# Executar seeders
php artisan db:seed --class=UsuarioAdminSeeder

# Testar autenticação
php artisan tinker
Auth::attempt(['email' => 'danilo@pousada.com.br', 'password' => 'admin']);
```

## 📞 Próximos Passos

1. ✅ **Fazer pull** das correções no servidor
2. ✅ **Configurar credenciais** do banco no `.env.dreamhost`
3. ✅ **Aplicar configurações** (`cp .env.dreamhost .env`)
4. ✅ **Limpar cache** (`php artisan config:clear && php artisan config:cache`)
5. ✅ **Testar conexão** com banco via tinker
6. ✅ **Testar autenticação** via tinker
7. ✅ **Testar login** na aplicação

---

**🔑 Lembre-se:** 
- As credenciais do banco são diferentes das credenciais de FTP/SSH
- O usuário admin padrão é: `danilo@pousada.com.br` / `admin`
- Sempre limpe o cache após alterar configurações