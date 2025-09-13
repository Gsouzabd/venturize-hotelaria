# Migração do Projeto para Diretório de Produção

## Situação Atual
- Projeto está no diretório: `~/pousada/`
- Domínio configurado aponta para: `~/venturize.codebeans.dev/`
- **Problema**: O projeto não está no diretório correto para o domínio

## ✅ Solução: Mover o Projeto

### Opção 1: Mover o Projeto Completo (Recomendado)

```bash
# 1. Fazer backup (segurança)
cp -r ~/pousada ~/pousada_backup

# 2. Mover o projeto para o diretório correto
mv ~/pousada/* ~/venturize.codebeans.dev/

# 3. Mover arquivos ocultos também
mv ~/pousada/.* ~/venturize.codebeans.dev/ 2>/dev/null || true

# 4. Remover diretório vazio
rmdir ~/pousada
```

### Opção 2: Copiar e Manter Original

```bash
# 1. Copiar todo o conteúdo
cp -r ~/pousada/* ~/venturize.codebeans.dev/
cp -r ~/pousada/.* ~/venturize.codebeans.dev/ 2>/dev/null || true
```

## 🔧 Configuração Após a Migração

Após mover/copiar os arquivos, execute no diretório `~/venturize.codebeans.dev/`:

```bash
# 1. Navegar para o diretório correto
cd ~/venturize.codebeans.dev/

# 2. Instalar dependências
composer install --optimize-autoloader --no-dev

# 3. Configurar ambiente
cp .env.example .env
# OU se existir:
cp .env.dreamhost .env

# 4. Gerar chave da aplicação
php artisan key:generate --force

# 5. Configurar permissões
chmod -R 755 storage bootstrap/cache

# 6. Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Executar migrações
php artisan migrate --force

# 8. Criar link do storage
php artisan storage:link
```

## 🌐 Configuração do Servidor Web

### Importante: Document Root
O servidor web deve apontar para a pasta `public/` do projeto:

```
Document Root: ~/venturize.codebeans.dev/public/
```

**NÃO** para a raiz do projeto (`~/venturize.codebeans.dev/`)

### Estrutura Correta Após Migração

```
~/venturize.codebeans.dev/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Document Root deve apontar aqui
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── composer.json
└── artisan
```

## 🔍 Verificações

### 1. Verificar se os arquivos foram movidos corretamente
```bash
ls -la ~/venturize.codebeans.dev/
```

### 2. Verificar se o Laravel está funcionando
```bash
cd ~/venturize.codebeans.dev/
php artisan about
```

### 3. Testar no navegador
Acesse: `http://venturize.codebeans.dev`

## 🚨 Problemas Comuns

### 1. Erro 500 - Internal Server Error
**Causa**: Permissões incorretas ou .env não configurado
**Solução**:
```bash
chmod -R 755 storage bootstrap/cache
cp .env.example .env
php artisan key:generate --force
```

### 2. Erro 404 - Not Found
**Causa**: Document root não aponta para `public/`
**Solução**: Configurar o servidor para apontar para `~/venturize.codebeans.dev/public/`

### 3. Página em branco
**Causa**: Erro no PHP ou dependências não instaladas
**Solução**:
```bash
composer install --no-dev
php artisan config:cache
```

## 📋 Script Automático

Crie um script para automatizar o processo:

```bash
#!/bin/bash
# migrate-to-production.sh

echo "🚀 Migrando projeto para produção..."

# Fazer backup
echo "📦 Criando backup..."
cp -r ~/pousada ~/pousada_backup_$(date +%Y%m%d_%H%M%S)

# Mover arquivos
echo "📁 Movendo arquivos..."
mv ~/pousada/* ~/venturize.codebeans.dev/
mv ~/pousada/.* ~/venturize.codebeans.dev/ 2>/dev/null || true
rmdir ~/pousada

# Configurar projeto
echo "⚙️ Configurando projeto..."
cd ~/venturize.codebeans.dev/

# Executar deploy
bash deploy-dreamhost.sh

echo "✅ Migração concluída!"
echo "🌐 Acesse: http://venturize.codebeans.dev"
```

## 🎯 Resumo dos Passos

1. **Mover/Copiar** o projeto de `~/pousada/` para `~/venturize.codebeans.dev/`
2. **Configurar** o Document Root para apontar para `~/venturize.codebeans.dev/public/`
3. **Executar** o script de deploy ou comandos manuais
4. **Testar** o acesso via navegador

## ⚠️ Importante

- **SEMPRE** faça backup antes de mover arquivos
- **NUNCA** exponha a raiz do projeto, apenas a pasta `public/`
- **SEMPRE** configure as permissões corretas
- **VERIFIQUE** se o .env está configurado corretamente

---

**Resultado**: Após seguir estes passos, seu projeto estará acessível em `http://venturize.codebeans.dev` e funcionando corretamente em produção.