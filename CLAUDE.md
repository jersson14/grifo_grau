# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de Gestión de Grifo — a gas station management web application built with plain PHP (no framework). Manages shifts, fuel sales, customer credits, pump readings, and reports for a service station in Peru.

## Current Work: Grifo 2

`main` is in production. All improvement work happens on branch `grifo2` with local database `grifo2`.

**Read [docs/PLAN_GRIFO2.md](docs/PLAN_GRIFO2.md) before starting work.** It holds the audit, phases, pending decisions and checklist. Mark tasks done and record decisions there as work progresses.

Rules for this work:
- Don't change `main` unless the owner approves a specific production fix.
- Don't copy the DNI/RUC API token into docs, commits or chat. It is gone from the code but remains in git history until revoked (task 1.1).
- Every database change is a numbered migration in `database/migrations/`, applied with `php database/migrate.php`.
- Every controller and view starts with `require_once __DIR__ . '/../_bootstrap.php';` (adjust the path). Access is deny-by-default: a route not listed in `config/capacidades.php` or `config/permisos.php` is `ADMINISTRADOR` only. Add new routes to a capability and check `puede()` instead of comparing role names. It also enforces CSRF on POST, and `$.ajaxSetup` in `view/index.php` sends the token.
- Run `php tests/smoke.php` after changing auth, shifts or credits, and `php tests/roles.php` after changing permissions. It needs the demo data on a local database. Tests read `GRIFO_BASE` (base URL) and the `DB_*` variables from the environment, so the same suites run against XAMPP, `php -S ... tests/router.php` or Docker. `.github/workflows/pruebas.yml` runs them on PHP 8.2 and 8.4 against MySQL 8.4.
- Target is PHP 8.4 and MySQL 8.4 in strict mode. Local XAMPP (PHP 8.2, MariaDB 10.4) runs a lax `sql_mode` that hides errors such as an empty string in an integer column: run the suites against Docker before trusting a database change.
- Never leave `ANTHROPIC_BASE_URL` or `NUBEFACT_RUTA` in `.env` outside tests: requests would go to the mock.
- Invoicing depends on Nubefact only through `model/facturacion/proveedor_nubefact.php`. A sale is saved before its document is sent, and a retry keeps the same number.
- Configurable behavior belongs in the database and the admin UI, not in code.
- Optional modules (electronic invoicing, AI chatbot) stay behind an on/off switch and must never block core shift operations.

## Running the Application

**Docker (recommended):**
```bat
install.bat         # builds and starts containers (PHP 8.4 + MySQL 8.4 + phpMyAdmin)
stop.bat            # stops containers, keeps data
reset-database.bat  # deletes the Docker database and reinstalls it
```
The web container waits for the database, creates `.env` and `model/model_conexion.php` if missing and runs `php database/migrate.php` on every start (`GRIFO_DEMO=1` adds demo data to an empty database). The app uses user `grifo_user`, not root. Ports can be changed with `GRIFO_PUERTO_WEB`, `GRIFO_PUERTO_PMA` and `GRIFO_PUERTO_DB`.

**Manual (XAMPP):**
- Place project under `htdocs/grifo_grau`
- MySQL on port 3307, user `root`, no password. Database `grifo2` on branch `grifo2`
- Copy `.env.example` to `.env` and `model/model_conexion.example.php` to `model/model_conexion.php` (both gitignored), then run `composer install`
- Create the empty database and run `php database/migrate.php --demo`. An empty database gets `database/schema/000_base_provisional.sql`, **reconstructed from code, not a copy of production**. `database/init.sql`, `tipos_pago.sql` and `fix_ventas_credito.sql` are legacy
- Legacy modules not in the menu (gastos, ingresos, indicadores, pagos) call stored procedures the provisional schema doesn't include
- Access at `http://localhost/grifo_grau` (the login is `login.php`; `index.php` shows the public page when the `pagina_publica` module is on)

**Default credentials:**
- Admin: `admin` / `admin123`
- Grifero: `grifero1` / `grifero123`

## Architecture

**Pattern:** Custom MVC — no framework, no URL routing, no ORM.

```
model/          PDO data access layer (one class per domain entity)
view/           HTML templates loaded dynamically via AJAX
controller/     One PHP file per action (~100+ files), returns JSON
js/             jQuery AJAX layer (console_*.js files, one per module)
```

**Request flow:**
1. `index.php` → login → AJAX → `controller/usuario/controlador_iniciar_sesion.php`
2. Session created with `S_ID`, `S_ROL`, `S_COMPLETOS`, etc.
3. `view/index.php` is the post-login shell; views load into `#contenido_principal` via `cargar_contenido()` (no page reloads)
4. JS files call controller endpoints → controllers instantiate models → return `json_encode()`

## Database Connection

`conexionBD` lives in `model/conexion_bd.php`; the gitignored `model/model_conexion.php` only requires it:
```php
$pdo = conexionBD::conexionPDO();  // returns PDO instance
```
- Every call opens its own connection. Do not share one per request: stored procedures leave pending result sets and the next query on that connection fails (error 2014)
- Connection values come from `.env` or real environment variables (which win), charset `utf8mb4`
- `controlador_guardar_turno_manual.php` and `controlador_cerrar_turno_manual.php` open their own mysqli connection and use tables the models don't (plan task 0.5)
- Timezone forced to `America/Lima` (UTC-5) on every connection

## Key Domain Concepts

- **Turno:** A shift (DÍA or NOCHE). Auto-generates `DOC-XXXX` numbers. Captures initial/final readings from 12 pumps across 2 machines. Closing a turno calculates gallons sold and updates pump readings.
- **Surtidores:** 12 pumps in 2 machines with codes BS1/BS2, R1/R2, P1/P2. Each assigned a fuel type: Diesel B5, Regular 84, or Premium 95.
- **Créditos:** Customer credit sales tied to a turno, tracked with vale numbers, payment history, and overdue alerts.
- **Pagos:** 6 payment methods — YAPE, BCP, VISA (require `codigo_operacion`), EFECTIVO, DESCUENTO, OTROS_GASTOS (no code required).
- **Roles:** editable in the UI (`roles`, `roles_capacidades`). `ADMINISTRADOR` always has everything. The sidebar comes from `menu_items`: new screens need a row there.

## Adding a New Feature

Follow the existing pattern exactly:
1. **Model** — add a method to the relevant `model/model_*.php` class using PDO prepared statements
2. **Controller** — create `controller/<module>/controlador_<action>.php`; instantiate the model, call the method, `echo json_encode($result)`
3. **View** — add a view file in `view/<module>/`; load it by adding a menu item that calls `cargar_contenido('view/<module>/view_*.php')`
4. **JS** — add AJAX calls in `js/console_<module>.js`

## Public page and Tailwind

- `publico/landing.php` is the public page, content edited in Configuración → Página pública (`model/model_pagina_publica.php`). Visual world: PRODUCT.md and `.impeccable/surfaces/index-php.md`.
- Tailwind 3.4 is a dev-only tool: `npm install` then `npm run css` rebuilds `css/publico.css`, which is committed. The hosting never runs Node. Rebuild after changing classes in `publico/` or `js/publico.js`.
- The public fleet form (`publico/solicitud_flota.php`) is a public route: it checks the module, its own CSRF token, a honeypot and a per-IP limit itself.

## PDF & Excel Export

- PDF: `view/MPDF/` — instantiate `\Mpdf\Mpdf`, write HTML, call `Output()`
- Excel: native PHP with `header('Content-Type: application/vnd.ms-excel')` pattern used in existing report controllers

## Configuration and Optional Modules

- Settings live in the `configuracion` table and are edited in Configuración (`view/configuracion/`). `Modelo_Configuracion::CLAVES` is the whitelist of editable keys. Secrets are AES-256-GCM encrypted with `APP_KEY` from `.env` and are never returned to the browser.
- Modules are switched in the `modulos` table. To gate a route, add it under `modulos` in `config/permisos.php`.
- The chatbot (`model/model_chatbot.php`) calls Claude through the official SDK. It can only run the read-only queries in `model/model_ia_herramientas.php`; the user and role come from the session.

## Composer

Dependencies: `phpmailer/phpmailer` (unused), `anthropic-ai/sdk` and `guzzlehttp/guzzle` (the HTTP client the SDK needs). `vendor/` is committed. Run `composer install` if it is absent.
