# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Venturize Hotelaria is a hotel management system built with **Laravel 11** (PHP 8.2+). It manages room reservations, a bar/restaurant module, inventory, expenses, and thermal printer integration.

## Common Commands

```bash
# Start development server
php artisan serve

# Frontend assets (run in parallel with serve)
npm run dev       # hot-reload
npm run build     # production build (required after cloning)

# Database
php artisan migrate
php artisan migrate:fresh --seed   # rebuild from scratch

# Clear all caches (run after route changes)
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# After route changes, re-cache
php artisan cache:clear; php artisan route:cache

# Run tests
php artisan test

# Lint (Laravel Pint)
./vendor/bin/pint
```

## Architecture

### Authentication & Guards
- Two auth guards: `admin` (hotel staff) and `bar` (bar module users)
- Admin routes: `routes/web.php` under `/admin` prefix with `auth:admin` middleware
- Bar routes: `routes/bar/routes.php` (included in web.php) under `/admin/bar`
- API routes: `routes/api.php` — split between:
  - `/api/print/*` — protected by the `agente.impressao` middleware (`App\Http\Middleware\VerificaAgenteImpressao`): requires either header `X-Print-Agent-Token` matching `config('services.print_agent.token')` (env `PRINT_AGENT_TOKEN` — **not present in `.env.example`, add it there if regenerating**) or an authenticated `admin` session. Consumed by the external `pousada-venturize-print-agent` repo.
  - `/api/disponibilidade/verificar`, `/api/reservas` — consumed by `aldeia-dos-camaras-site` (WordPress `rest-api.php`) for Day Use availability/booking sync.
  - `API-IMPRESSAO.md`/`SISTEMA_CONTROLE_IMPRESSAO.md` still say these print routes are public/unauthenticated — that's **outdated**; the token middleware was added after those docs were written.

### Route Pattern
Most CRUD resources follow a consistent loop pattern in web.php and bar routes, registering index/create/edit/save/destroy for each controller. Specific routes that conflict with `/{id}` must be defined **before** the loop.

### Module Structure

**Admin module** (`app/Http/Controllers/Admin/`):
- Core hotel ops: `ReservaController`, `QuartoController`, `ClienteController`
- Inventory: `EstoqueController`, `MovimentacaoEstoqueController`, `LocalEstoqueController`
- Financial: `DespesaController`, `PagamentoService`
- Utilities: `ImpressoraController`, `DisponibilidadeController`

**Bar module** (`app/Http/Controllers/Admin/Bar/`):
- `PedidoController`, `MesaController`, `BarHomeController`
- Bar-specific models in `app/Models/Bar/`: `Pedido`, `Mesa`, `ItemPedido`, `ImpressaoPedido`

**Services** (`app/Services/`):
- `ReservaService` — booking business logic
- `PrinterService` — ESC/POS thermal printing via `mike42/escpos-php`, connects to printers by IP
- `PagamentoService` — payment processing
- `MovimentacaoEstoqueService` — inventory movements
- `ExcelExportService` — spreadsheet exports via PhpSpreadsheet
- `Bar/MesaService` — table management

### Printing System
Two-layer print architecture:
1. **PrinterService** (PHP/Laravel): directly connects to ESC/POS printers over TCP (port 9100)
2. **PrintingAgent** — **lives in the sibling repo `pousada-venturize-print-agent/`** (not inside this repo; the old `printingAgent/Agentimpressao/` reference was the installer build output, not source). It polls `GET /api/print/pedidos-pendentes`, prints locally, and reports back via:
   - `GET /api/print/pedido/{id}` — full order data
   - `POST /api/print/pedido/{id}/impresso` — mark printed
   - `POST /api/print/pedido/{id}/tentativa` — log a print attempt
   - `POST /api/print/pedido/{id}/erro` — log a failure with details
   - `GET /api/print/historico` — print history
   - `GET /api/print/estatisticas` — stats
   - `GET /api/print/impressoras` — printer list
   All of the above require the `agente.impressao` middleware (see Authentication & Guards).

Print status tracked in `impressoes_pedidos` table (model: `ImpressaoPedido`) with statuses: `pendente`, `processando`, `sucesso`, `erro`.

Printer configs are stored in the `impressoras` DB table (model: `Impressora`) with fallback to `.env` vars `PRINTER_1_IP`, `PRINTER_1_NAME`, etc. `app/Console/Commands/SyncPrinters.php` syncs printer definitions (check it before assuming printer config only lives in one place).

### Key Dependencies
- `barryvdh/laravel-dompdf` — PDF generation (extratos, ficha nacional)
- `mike42/escpos-php` — thermal printer ESC/POS protocol
- `phpoffice/phpspreadsheet` — Excel exports
- `pusher/pusher-php-server` + `laravel-echo` — real-time events
- `@fullcalendar/*` — reservation calendar UI

### Frontend
- Blade templates in `resources/views/admin/` and `resources/views/pdf/`
- Minimal JS in `resources/js/` (app.js, echo.js for Pusher)
- Built with Vite (`vite.config.js`)

### Helpers
`app/helpers.php` is autoloaded globally — check it before adding utility functions.

## Deploy to Production

**O deploy é automático via GitHub Actions** (`.github/workflows/deploy.yml`): todo push na branch `main` roda rsync dos arquivos para o servidor e executa os comandos pós-deploy (`migrate --force`, limpeza e recache de config/rotas/views). **Para deployar, basta commit + push na `main`.** Não faça SFTP manual de arquivos.

> **NUNCA use `deployStaticWebsite` ou `deployJsApplication` do MCP Hostinger para este projeto.** Essas ferramentas são para sites estáticos e apps Node.js — usá-las aqui destrói a estrutura PHP no servidor. Isso já aconteceu neste projeto.

O rsync do pipeline exclui `.env`, `vendor/`, `storage/`, `node_modules/`, `tests/`, `database/seeders/`, `resources/js|css`, `bitz-exports/` etc. — o `.env` de produção nunca é sobrescrito. Migrations que dependem de dados externos devem embutir os dados no próprio arquivo (padrão das migrations `import_bitz_*`).

**Atenção — assets públicos:** o site serve o docroot `public_html/` (cópia de `laravel/public/`), mas o rsync do pipeline só atualiza `laravel/`. Após alterar arquivos em `public/` (ex.: `public/assets/admin/app.css`), copie manualmente via SSH: `cp .../laravel/public/assets/... .../public_html/assets/...`.

O passo "Setup SSH key" do workflow falha esporadicamente (ssh-keyscan transitório). Se o deploy falhar aí, basta re-run: `POST /repos/Gsouzabd/venturize-hotelaria/actions/runs/<id>/rerun-failed-jobs` na API do GitHub (token disponível via `git credential fill`).

### Executar comandos no servidor (verificação, artisan avulso)

SSH no Windows: use Python `paramiko` (`sshpass` não está disponível; `gh` CLI também não está instalado localmente):

```python
import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('147.79.94.231', port=65002, username='u529148852', password='<senha>', timeout=30)
base = '/home/u529148852/domains/venturize.com.br/public_html/laravel'
_, out, err = client.exec_command(f'cd {base} && php artisan <comando>', timeout=120)
print(out.read().decode())
client.close()
```

### Scheduler em produção

Um cron na Hostinger (conta `u529148852`) roda `php artisan schedule:run` a cada minuto no diretório do Laravel. As tarefas agendadas ficam em `routes/console.php` (ex.: `reservas:expirar` diariamente às 12:05, que marca reservas vencidas como NO SHOW).

## Integrações externas / repos irmãos

Este repo faz parte de um monorepo local com 3 outros diretórios (ver `../CLAUDE.md` para o mapa geral). Nenhum deles compartilha controle de versão com este — mudar um contrato de API aqui sem avisar quebra o outro lado silenciosamente:

- **`../aldeia-dos-camaras-site/`** (WordPress, tema "sailing") — chama `/api/disponibilidade/verificar` e `/api/reservas` a partir de `themes/sailing/inc/aldeia/rest-api.php` para sincronizar pedidos Day Use do WooCommerce.
- **`../pousada-venturize-print-agent/`** (Node.js, roda localmente na pousada) — consome todo o grupo `/api/print/*` via polling, autenticado pelo header `X-Print-Agent-Token`. Ver seção "Printing System" acima.

## Important Notes

- After any route changes, always clear and re-cache routes: `php artisan cache:clear; php artisan route:cache`
- The `resources/views/admin/bar/` directory contains bar module views
- Day-use reservations are a separate flow from regular room reservations (`DayUsePlanoPreco` model, `/reservas/day-use` route)
- Despesas (expenses) routes are defined manually to avoid conflicts with the generic `/{id}` route pattern
- Inventory locations (`locais_estoque`) are hierarchical (2 levels via `parent_id`): parents like Cozinha/Almoxarifado/Lavanderia/Inventário group leaf sub-locais (Dispensa, Freezer, etc.). Only leaves hold stock rows (`estoques`); the stock report filter aggregates a parent's children (`EstoqueController::index`). Balances were imported from Bitz by `2026_07_03_120000_import_bitz_saldo_estoque.php`
