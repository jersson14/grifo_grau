# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sistema de Gestión de Grifo — a gas station management web application built with plain PHP (no framework). Manages shifts, fuel sales, customer credits, pump readings, and reports for a service station in Peru.

## Running the Application

**Docker (recommended):**
```bat
install.bat     # builds and starts containers (web + MySQL 8.0 + phpMyAdmin)
stop.bat        # stops containers
```

**Manual (XAMPP):**
- Place project under `htdocs/grifo_grau`
- MySQL on port 3307, database `grifo_grau`, user `root`, no password
- Import `database/init.sql`, then `database/tipos_pago.sql`, then `database/fix_ventas_credito.sql`
- Access at `http://localhost/grifo_grau`

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

All models extend `conexionBD` from `model/model_conexion.php`:
```php
$pdo = conexionBD::conexionPDO();  // returns PDO instance
```
- Host: `localhost:3307`, DB: `grifo_grau`, charset: `utf8`
- Timezone forced to `America/Lima` (UTC-5) on every connection

## Key Domain Concepts

- **Turno:** A shift (DÍA or NOCHE). Auto-generates `DOC-XXXX` numbers. Captures initial/final readings from 12 pumps across 2 machines. Closing a turno calculates gallons sold and updates pump readings.
- **Surtidores:** 12 pumps in 2 machines with codes BS1/BS2, R1/R2, P1/P2. Each assigned a fuel type: Diesel B5, Regular 84, or Premium 95.
- **Créditos:** Customer credit sales tied to a turno, tracked with vale numbers, payment history, and overdue alerts.
- **Pagos:** 6 payment methods — YAPE, BCP, VISA (require `codigo_operacion`), EFECTIVO, DESCUENTO, OTROS_GASTOS (no code required).
- **Roles:** `ADMINISTRADOR` (full access) and `GRIFERO` (limited to shift operations).

## Adding a New Feature

Follow the existing pattern exactly:
1. **Model** — add a method to the relevant `model/model_*.php` class using PDO prepared statements
2. **Controller** — create `controller/<module>/controlador_<action>.php`; instantiate the model, call the method, `echo json_encode($result)`
3. **View** — add a view file in `view/<module>/`; load it by adding a menu item that calls `cargar_contenido('view/<module>/view_*.php')`
4. **JS** — add AJAX calls in `js/console_<module>.js`

## PDF & Excel Export

- PDF: `view/MPDF/` — instantiate `\Mpdf\Mpdf`, write HTML, call `Output()`
- Excel: native PHP with `header('Content-Type: application/vnd.ms-excel')` pattern used in existing report controllers

## Composer

Only one dependency: `phpmailer/phpmailer ^6.9`. Run `composer install` after cloning if `vendor/` is absent.
