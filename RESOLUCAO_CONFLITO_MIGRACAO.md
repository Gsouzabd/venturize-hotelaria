# Resolução de Conflito na Migração - Diretório Public Existente

## Problema Identificado
O comando `mv ~/pousada/* ~/venturize.codebeans.dev/` falhou porque o diretório `public` já existe no destino.

## Soluções Disponíveis

### Opção 1: Backup e Substituição Completa (Recomendado)
```bash
# 1. Fazer backup do public existente
mv ~/venturize.codebeans.dev/public ~/venturize.codebeans.dev/public_backup_$(date +%Y%m%d_%H%M%S)

# 2. Mover todos os arquivos do pousada
mv ~/pousada/* ~/venturize.codebeans.dev/

# 3. Verificar se há arquivos importantes no backup que precisam ser preservados
ls -la ~/venturize.codebeans.dev/public_backup_*/
```

### Opção 2: Merge Manual dos Diretórios
```bash
# 1. Mover arquivos específicos (exceto public)
cd ~/pousada/
for item in *; do
  if [ "$item" != "public" ]; then
    mv "$item" ~/venturize.codebeans.dev/
  fi
done

# 2. Fazer merge do diretório public
cp -r ~/pousada/public/* ~/venturize.codebeans.dev/public/

# 3. Remover diretório pousada vazio
rmdir ~/pousada/public
rmdir ~/pousada
```

### Opção 3: Verificação e Merge Seletivo
```bash
# 1. Comparar conteúdos dos diretórios public
diff -r ~/pousada/public ~/venturize.codebeans.dev/public

# 2. Se forem idênticos, remover o antigo
rm -rf ~/pousada/public

# 3. Mover o restante
mv ~/pousada/* ~/venturize.codebeans.dev/
```

## Script Automático de Resolução
```bash
#!/bin/bash

# Script para resolver conflito de migração
echo "Resolvendo conflito de migração..."

# Verificar se os diretórios existem
if [ ! -d "~/pousada" ]; then
    echo "Erro: Diretório ~/pousada não encontrado"
    exit 1
fi

if [ ! -d "~/venturize.codebeans.dev" ]; then
    echo "Erro: Diretório ~/venturize.codebeans.dev não encontrado"
    exit 1
fi

# Fazer backup do public existente
echo "Fazendo backup do diretório public existente..."
BACKUP_NAME="public_backup_$(date +%Y%m%d_%H%M%S)"
mv ~/venturize.codebeans.dev/public ~/venturize.codebeans.dev/$BACKUP_NAME

# Mover todos os arquivos
echo "Movendo arquivos do pousada para venturize.codebeans.dev..."
mv ~/pousada/* ~/venturize.codebeans.dev/

# Remover diretório pousada vazio
rmdir ~/pousada

echo "Migração concluída com sucesso!"
echo "Backup do public anterior salvo em: ~/venturize.codebeans.dev/$BACKUP_NAME"
```

## Verificações Pós-Migração

### 1. Verificar Estrutura Final
```bash
ls -la ~/venturize.codebeans.dev/
```

### 2. Verificar Arquivos Críticos
```bash
# Verificar se os arquivos principais existem
ls -la ~/venturize.codebeans.dev/.env*
ls -la ~/venturize.codebeans.dev/composer.json
ls -la ~/venturize.codebeans.dev/public/index.php
```

### 3. Executar Deploy
```bash
cd ~/venturize.codebeans.dev/
./deploy-dreamhost.sh
```

## Arquivos que Podem Causar Conflito
- `public/` - Diretório principal (já identificado)
- `.env` - Arquivo de configuração
- `storage/` - Diretório de armazenamento
- `bootstrap/cache/` - Cache do framework

## Recomendação Final

**Use a Opção 1 (Backup e Substituição)** pois:
- É mais segura (mantém backup)
- É mais rápida
- Evita conflitos de arquivos
- Permite rollback se necessário

## Próximos Passos Após Migração
1. Executar `./deploy-dreamhost.sh`
2. Verificar se o site está funcionando
3. Testar funcionalidades críticas
4. Remover backup se tudo estiver OK

## Comandos de Emergência
```bash
# Se algo der errado, restaurar backup
mv ~/venturize.codebeans.dev/public_backup_* ~/venturize.codebeans.dev/public

# Verificar logs de erro
tail -f ~/venturize.codebeans.dev/storage/logs/laravel.log
```