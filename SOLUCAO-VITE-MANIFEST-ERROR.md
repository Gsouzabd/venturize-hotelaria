# Solução para ViteManifestNotFoundException

## Problema

Erro 500 ao acessar a rota `/admin/bar` em produção:

```
Vite manifest not found at: /home/dh_pousada/venturize.codebeans.dev/public/build/manifest.json
```

## Causa

O erro ocorre porque os assets do Vite não foram compilados para produção. O arquivo `manifest.json` é gerado apenas quando executamos `npm run build`.

## Solução

### 1. Compilar Assets Localmente

```bash
# No ambiente de desenvolvimento
npm install
npm run build
```

Isso criará:
- `public/build/manifest.json`
- `public/build/assets/` (com arquivos JS/CSS compilados)

### 2. Deploy para Produção

#### Opção A: Script Automatizado

Use o script `deploy-production-with-assets.sh`:

```bash
./deploy-production-with-assets.sh
```

#### Opção B: Comandos Manuais

```bash
# 1. Instalar dependências
composer install --optimize-autoloader --no-dev
npm install

# 2. Compilar assets
npm run build

# 3. Configurar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Verificar Estrutura de Arquivos

Após o build, verifique se existem:

```
public/
├── build/
│   ├── manifest.json
│   └── assets/
│       ├── app-[hash].js
│       ├── app-[hash].js
│       └── echo-[hash].js
├── index.php
└── .htaccess
```

## Configuração do Vite

O arquivo `vite.config.js` está configurado corretamente:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/echo.js'
            ],
            refresh: true,
        }),
    ],
});
```

## Diferença entre Desenvolvimento e Produção

### Desenvolvimento
- Vite serve assets dinamicamente
- Não precisa do `manifest.json`
- Hot reload ativo

### Produção
- Assets são compilados estaticamente
- `manifest.json` mapeia arquivos originais para versões com hash
- Performance otimizada

## Troubleshooting

### Se o erro persistir:

1. **Verificar permissões:**
   ```bash
   chmod -R 755 public/build
   ```

2. **Limpar cache do Laravel:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Verificar se o manifest existe:**
   ```bash
   ls -la public/build/manifest.json
   cat public/build/manifest.json
   ```

4. **Recompilar assets:**
   ```bash
   rm -rf public/build
   npm run build
   ```

## Prevenção

### Para evitar este erro no futuro:

1. **Sempre execute `npm run build` antes do deploy**
2. **Inclua a pasta `public/build/` no controle de versão** (opcional)
3. **Use o script de deploy automatizado**
4. **Configure CI/CD para compilar assets automaticamente**

### Exemplo de .gitignore

```gitignore
# Se quiser versionar os assets compilados, remova estas linhas:
# /public/build
# /public/hot
```

## Comandos Úteis

```bash
# Desenvolvimento
npm run dev          # Servidor de desenvolvimento
npm run build        # Compilar para produção
npm run preview      # Preview da build de produção

# Produção
php artisan optimize:clear  # Limpar todos os caches
php artisan optimize        # Otimizar para produção
```

---

**Nota:** Este erro é comum em aplicações Laravel que usam Vite e não compilaram os assets para produção. A solução é sempre executar `npm run build` antes do deploy.