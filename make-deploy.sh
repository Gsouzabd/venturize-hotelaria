#!/bin/bash
echo "Compilando assets..."
npm run build

echo "Gerando zip..."
zip -r venturize-hotelaria.zip . \
  --exclude "*.git*" \
  --exclude "*.zip" \
  --exclude ".claude/*" \
  --exclude "node_modules/*" \
  --exclude "bitz-exports/*" \
  --exclude "tests/*" \
  --exclude "printingAgent/*" \
  --exclude "database/seeders/*" \
  --exclude "resources/js/*" \
  --exclude "resources/css/*" \
  --exclude "storage/logs/*" \
  --exclude "storage/app/*" \
  --exclude "storage/framework/*" \
  --exclude "bootstrap/cache/*" \
  --exclude "package.json" \
  --exclude "package-lock.json" \
  --exclude "vite.config.js"

echo "Concluído! Tamanho: $(du -sh venturize-hotelaria.zip)"
