---
name: clear-cache
description: Clear and re-cache Laravel routes, config, views, and application cache.
disable-model-invocation: true
---

Run these commands in order and report each result:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan route:cache
```
