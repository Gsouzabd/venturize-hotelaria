# Diagnóstico de Conexão com Banco de Dados - DreamHost

## 🔍 Problema Identificado

A aplicação Laravel não consegue se conectar ao banco MySQL, mesmo com as credenciais funcionando no PHPMyAdmin.

## 🛠️ Passos para Diagnóstico

### 1. Verificar Configurações do .env

**No servidor DreamHost via SSH:**

```bash
cd ~/laravel-app  # ou seu diretório do projeto
cat .env | grep DB_
```

**Configurações esperadas:**
```env
DB_CONNECTION=mysql
DB_HOST=highman.iad1-mysql-e2-17a.dreamhost.com
DB_PORT=3306
DB_DATABASE=venturize_hotelaria
DB_USERNAME=pousada_userdb
DB_PASSWORD=venturize2025
```

### 2. Limpar Cache de Configuração

**Execute estes comandos no servidor:**

```bash
# Limpar todos os caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recriar cache de configuração
php artisan config:cache
```

### 3. Testar Conexão Diretamente

**No Tinker:**

```bash
php artisan tinker
```

**Dentro do Tinker:**

```php
# Testar conexão básica
try {
    DB::connection()->getPdo();
    echo "Conexão OK!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

# Testar query simples
DB::select('SELECT 1 as test');

# Verificar configuração atual
config('database.connections.mysql');

exit
```

### 4. Verificar Logs de Erro

```bash
# Ver logs do Laravel
tail -f storage/logs/laravel.log

# Ver logs do servidor (se disponível)
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

### 5. Verificar Extensões PHP

```bash
# Verificar se PDO MySQL está instalado
php -m | grep -i mysql
php -m | grep -i pdo

# Informações detalhadas do PHP
php -i | grep -i mysql
```

### 6. Testar Conexão Manual

**Criar arquivo de teste temporário:**

```bash
nano test-db.php
```

**Conteúdo do arquivo:**

```php
<?php
try {
    $pdo = new PDO(
        'mysql:host=highman.iad1-mysql-e2-17a.dreamhost.com;port=3306;dbname=venturize_hotelaria',
        'pousada_userdb',
        'venturize2025',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Conexão PDO OK!\n";
    
    $stmt = $pdo->query('SELECT DATABASE() as db_name');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Banco atual: " . $result['db_name'] . "\n";
    
} catch (PDOException $e) {
    echo "Erro PDO: " . $e->getMessage() . "\n";
}
?>
```

**Executar teste:**

```bash
php test-db.php
```

**Remover arquivo após teste:**

```bash
rm test-db.php
```

## 🔧 Soluções Comuns

### Problema 1: Cache de Configuração

**Solução:**
```bash
php artisan config:clear
php artisan config:cache
```

### Problema 2: Arquivo .env não está sendo lido

**Verificar:**
```bash
# Verificar se o arquivo existe e tem permissões corretas
ls -la .env
chmod 644 .env
```

### Problema 3: Diferenças entre CLI e Web

**Verificar versão PHP:**
```bash
# PHP CLI
php -v

# PHP Web (criar arquivo info.php temporário)
echo "<?php phpinfo(); ?>" > public/info.php
# Acesse: https://seudominio.com/info.php
# REMOVA após verificar: rm public/info.php
```

### Problema 4: Configuração de Timezone

**No .env, adicionar:**
```env
DB_TIMEZONE=+00:00
```

**Ou no config/database.php:**
```php
'mysql' => [
    // ... outras configurações
    'timezone' => '+00:00',
],
```

### Problema 5: SSL/TLS

**Tentar desabilitar SSL temporariamente no config/database.php:**
```php
'mysql' => [
    // ... outras configurações
    'options' => [
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ],
],
```

## 📋 Checklist de Verificação

- [ ] Arquivo .env existe e tem as credenciais corretas
- [ ] Cache de configuração foi limpo
- [ ] Extensões PDO e MySQL estão instaladas
- [ ] Conexão manual via PDO funciona
- [ ] Logs não mostram erros específicos
- [ ] Versão PHP CLI e Web são compatíveis
- [ ] Permissões do arquivo .env estão corretas
- [ ] Timezone está configurado corretamente

## 🚨 Comandos de Emergência

**Se nada funcionar, recriar configuração:**

```bash
# Backup do .env atual
cp .env .env.backup

# Recriar .env baseado no exemplo
cp .env.example .env

# Editar com as credenciais corretas
nano .env

# Gerar nova APP_KEY
php artisan key:generate

# Limpar e recriar caches
php artisan optimize:clear
php artisan optimize
```

## 📞 Próximos Passos

1. Execute os comandos de diagnóstico na ordem
2. Anote os resultados de cada teste
3. Identifique onde está falhando
4. Aplique a solução correspondente
5. Teste a aplicação novamente

---

**💡 Dica:** Se o PHPMyAdmin funciona mas o Laravel não, o problema geralmente está no cache de configuração ou nas extensões PHP CLI vs Web.