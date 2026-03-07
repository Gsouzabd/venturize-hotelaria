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
- API routes: `routes/api.php` — mostly unauthenticated (used by PrintingAgent)

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
2. **PrintingAgent** (`printingAgent/Agentimpressao/`): a separate agent that polls `GET /api/print/pedidos-pendentes` and handles printing locally

Print status tracked in `impressoes_pedidos` table (model: `ImpressaoPedido`) with statuses: `pendente`, `processando`, `sucesso`, `erro`.

Printer configs are stored in the `impressoras` DB table (model: `Impressora`) with fallback to `.env` vars `PRINTER_1_IP`, `PRINTER_1_NAME`, etc.

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

## Important Notes

- After any route changes, always clear and re-cache routes: `php artisan cache:clear; php artisan route:cache`
- The `resources/views/admin/bar/` directory contains bar module views
- Day-use reservations are a separate flow from regular room reservations (`DayUsePlanoPreco` model, `/reservas/day-use` route)
- Despesas (expenses) routes are defined manually to avoid conflicts with the generic `/{id}` route pattern
