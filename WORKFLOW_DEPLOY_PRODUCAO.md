# Workflow de Deploy em Produção - DreamHost

## Estrutura Atual do Servidor

```
/home/dh_pousada/
├── pousada/                    # Pasta do repositório Git (clone)
│   ├── .git/                   # Controle de versão
│   ├── app/
│   ├── public/
│   └── ... (todos os arquivos do projeto)
└── venturize.codebeans.dev/    # Pasta de produção (Document Root)
    ├── app/
    ├── public/                 # <- Document Root aponta aqui
    └── ... (arquivos copiados)
```

## Workflow de Atualização

### Você está CORRETO! O processo é:

1. **Local**: Fazer alterações → commit → push
2. **Servidor**: Acessar pasta `pousada` → pull → copiar → deploy

## Processo Detalhado

### 1. Desenvolvimento Local
```bash
# No seu ambiente local
git add .
git commit -m "Sua mensagem de commit"
git push origin main
```

### 2. Atualização no Servidor
```bash
# SSH no servidor DreamHost
ssh dh_pousada@venturize.codebeans.dev

# Ir para pasta do repositório
cd ~/pousada/

# Fazer pull das atualizações
git pull origin main

# Copiar arquivos atualizados para produção
rsync -av --exclude='.git' --exclude='node_modules' ~/pousada/ ~/venturize.codebeans.dev/

# Executar deploy
cd ~/venturize.codebeans.dev/
./deploy-dreamhost.sh
```

## Script Automatizado de Atualização

### Criar arquivo `update-production.sh` na pasta `pousada`:
```bash
#!/bin/bash

# Script para atualizar produção no DreamHost
echo "🔄 Iniciando atualização da produção..."

# Verificar se estamos na pasta correta
if [ ! -d ".git" ]; then
    echo "❌ Erro: Execute este script na pasta do repositório (pousada)"
    exit 1
fi

# Fazer backup da produção atual
echo "📦 Fazendo backup da produção atual..."
cp -r ~/venturize.codebeans.dev ~/venturize.codebeans.dev.backup.$(date +%Y%m%d_%H%M%S)

# Fazer pull das atualizações
echo "⬇️ Baixando atualizações do repositório..."
git pull origin main

if [ $? -ne 0 ]; then
    echo "❌ Erro ao fazer pull do repositório"
    exit 1
fi

# Sincronizar arquivos para produção
echo "📁 Sincronizando arquivos para produção..."
rsync -av \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*' \
    ~/pousada/ ~/venturize.codebeans.dev/

# Ir para pasta de produção
cd ~/venturize.codebeans.dev/

# Verificar se composer.phar existe
if [ ! -f "composer.phar" ]; then
    echo "📥 Baixando Composer..."
    curl -sS https://getcomposer.org/installer | php
fi

# Instalar/atualizar dependências
echo "📦 Instalando dependências..."
php composer.phar install --no-dev --optimize-autoloader

# Configurar permissões
echo "🔐 Configurando permissões..."
chmod -R 755 storage bootstrap/cache

# Limpar caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Executar migrações
echo "🗄️ Executando migrações..."
php artisan migrate --force

# Otimizar para produção
echo "⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link de storage se não existir
if [ ! -L "public/storage" ]; then
    php artisan storage:link
fi

echo "✅ Atualização concluída com sucesso!"
echo "🌐 Site disponível em: https://venturize.codebeans.dev"
```

## Comandos Rápidos

### Atualização Completa (Recomendado)
```bash
# No servidor, pasta pousada
cd ~/pousada/
git pull && rsync -av --exclude='.git' --exclude='node_modules' ~/pousada/ ~/venturize.codebeans.dev/ && cd ~/venturize.codebeans.dev/ && ./deploy-dreamhost.sh
```

### Atualização Apenas de Arquivos (Sem dependências)
```bash
# Para mudanças simples (views, controllers, etc.)
cd ~/pousada/
git pull && rsync -av --exclude='.git' ~/pousada/ ~/venturize.codebeans.dev/ && cd ~/venturize.codebeans.dev/ && php artisan config:clear && php artisan cache:clear
```

## Vantagens desta Estrutura

### ✅ Prós:
- **Segurança**: Pasta de produção separada do Git
- **Backup**: Fácil fazer backup da produção
- **Controle**: Git apenas na pasta `pousada`
- **Flexibilidade**: Pode testar na pasta `pousada` antes de copiar

### ⚠️ Contras:
- **Duplicação**: Ocupa mais espaço em disco
- **Sincronização**: Precisa lembrar de copiar após pull
- **Complexidade**: Processo em duas etapas

## Alternativa: Estrutura Simplificada

### Se preferir uma abordagem mais simples:
```bash
# Mover tudo para venturize.codebeans.dev e usar como repositório
mv ~/pousada/.git ~/venturize.codebeans.dev/
rm -rf ~/pousada/
cd ~/venturize.codebeans.dev/

# Workflow simplificado:
git pull && ./deploy-dreamhost.sh
```

## Checklist de Atualização

### Antes de cada atualização:
- [ ] Fazer backup da produção atual
- [ ] Verificar se há conflitos no Git
- [ ] Testar localmente antes do push

### Após cada atualização:
- [ ] Verificar se o site está funcionando
- [ ] Testar funcionalidades críticas
- [ ] Verificar logs de erro
- [ ] Confirmar que migrações rodaram

## Monitoramento

### Verificar logs após deploy:
```bash
# Logs da aplicação
tail -f ~/venturize.codebeans.dev/storage/logs/laravel.log

# Logs do servidor web
tail -f ~/logs/venturize.codebeans.dev/http/error.log
```

### Testar funcionalidades:
```bash
# Testar conexão com banco
cd ~/venturize.codebeans.dev/
php artisan tinker
# No tinker: DB::connection()->getPdo()
```

## Troubleshooting

### Se algo der errado:
```bash
# Restaurar backup
rm -rf ~/venturize.codebeans.dev/
mv ~/venturize.codebeans.dev.backup.YYYYMMDD_HHMMSS ~/venturize.codebeans.dev/
```

### Verificar diferenças:
```bash
# Comparar pastas
diff -r ~/pousada/ ~/venturize.codebeans.dev/ --exclude='.git'
```

## Resumo do Workflow

1. **Desenvolvimento** → `git push`
2. **Servidor** → `cd ~/pousada/ && git pull`
3. **Sincronização** → `rsync ~/pousada/ ~/venturize.codebeans.dev/`
4. **Deploy** → `cd ~/venturize.codebeans.dev/ && ./deploy-dreamhost.sh`
5. **Verificação** → Testar site e funcionalidades

**Sim, você entendeu perfeitamente o processo!** 🎯