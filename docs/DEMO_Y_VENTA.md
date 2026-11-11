# Demo y venta: qué rama y qué base usar

Las ramas `grifo2`, `demo` y `venta` tienen **el mismo código**. Lo que cambia es la base de datos y el `.env`.

| | Demo pública | Venta a un cliente |
|---|---|---|
| Rama | `demo` | `venta` |
| Base de datos | `grifo_demo` (datos inventados) | **Base vacía nueva**, una por cliente |
| `.env` | `MODO_DEMO=1` | `MODO_DEMO=0` (o sin la línea) |
| `APP_KEY` | Propia de la demo | **Nueva para cada cliente** |
| Usuarios | `admin`, `supervisor1`, `contador1`, `grifero1` (claves públicas) | Los crea el cliente en el asistente |
| Dónde está | https://sistemagrifo.jcm-abc.com | Hosting del cliente |

## Bases que NO se usan para vender

| Base | Por qué no |
|---|---|
| `grifo2` | Base de desarrollo: restos de las pruebas automáticas y claves de servicios externos guardadas (DNI/RUC, Nubefact, IA). |
| `grifo_demo` | Todo es inventado: turnos, flotas, usuarios con claves públicas. |
| Producción del cliente actual | Son datos reales de ese grifo. |

---

## Demo

**Qué hace `MODO_DEMO=1`:** bloquea, incluso para el administrador, usuarios, roles, menú, configuración, auditoría, análisis, facturación, chatbot y personal (lista en `config/demo.php`). El login muestra la tabla "Accesos de demostración" con un botón **Entrar** por perfil.

**Datos:** `php database/demo_datos.php` llena una instalación `--demo` con 60 días de datos coherentes: turnos, vales de flotas y su cobranza, compras, inventario, arqueos, depósitos, gastos y tienda. Las fechas son relativas al día en que se ejecuta.

**Generar la base de la demo en local:**

```bash
# Crear antes la base vacía grifo_demo (utf8mb4_unicode_ci)
DB_NAME=grifo_demo php database/migrate.php --demo
DB_NAME=grifo_demo php database/demo_datos.php
```

**Exportarla para Hostinger:** exportar con `mysqldump --routines` y **quitar las líneas `DEFINER=...`**, porque Hostinger las rechaza. El ZIP armado (`respaldos/grifo_demo_hostinger.zip`) trae:

- `public_html/`: el sistema, con `.env` y `MODO_DEMO=1`.
- `base_de_datos/grifo_demo.sql`: la base para importar en phpMyAdmin.
- `LEEME.txt`: los pasos.

**Renovar cada noche (recomendado):** cron en hPanel → Avanzado → Cron Jobs:

```bash
cd /home/USUARIO/public_html && php database/demo_datos.php --reiniciar
```

Borra y recrea toda la base con fechas de hoy y limpia lo que cambien los visitantes. Solo funciona si `.env` tiene `MODO_DEMO=1`, para que nunca pueda borrar la base de un cliente.

---

## Venta a un cliente

1. Subir el código de la rama `venta`.
2. Crear una **base vacía** para el cliente.
3. Instalar las tablas **sin datos de ejemplo**, de una de estas dos formas:
   - **Con SSH:** `php database/migrate.php` (**sin** `--demo`).
   - **Sin SSH:** importar en phpMyAdmin `respaldos/grifo_limpio.sql` (53 tablas, rutinas y catálogos; sin usuarios, sin datos y sin claves). Está fuera de git. Si se agregan migraciones nuevas, hay que regenerarlo así:

     ```bash
     # Base vacía temporal grifo_limpio (utf8mb4_unicode_ci)
     DB_NAME=grifo_limpio php database/migrate.php
     mysqldump --routines --triggers --no-tablespaces grifo_limpio \
       | sed -E 's/DEFINER=`[^`]+`@`[^`]+`//g' > respaldos/grifo_limpio.sql
     ```
4. Crear el `.env` a partir de `.env.example` con los datos de la base del cliente, un `APP_KEY` nuevo (`php -r "echo bin2hex(random_bytes(32));"`) y **sin** `MODO_DEMO=1`.
5. Copiar `model/model_conexion.example.php` como `model/model_conexion.php`.
6. Abrir el sitio: aparece el **asistente de instalación** (`instalar.php`). El cliente registra su empresa, su administrador con su propia contraseña, sus combustibles y sus surtidores. Cuando ya hay un usuario, el asistente se bloquea solo.
7. Activar desde Configuración → Módulos solo lo que el cliente contrató. Facturación electrónica y chatbot necesitan sus propias claves (Nubefact, Anthropic), que se ponen en Configuración y se guardan cifradas.

**Pendiente antes de vender a varios clientes:** el esquema de la base es **provisional**: se reconstruyó desde el código, no se copió de producción. Conviene compararlo con la base real de producción (ver `docs/PLAN_GRIFO2.md`).

---

## Hostinger: lo que hay que saber

- **Limita las conexiones MySQL por segundo.** Si una pantalla abre muchas conexiones, responde `503` ("El sistema no está disponible") y salen bloques vacíos. Ya se corrigió en el tablero y en `Modulo_Activo`. Al programar algo que corre en cada petición, no abrir conexiones dentro de un bucle.
- `DB_HOST=localhost` y `DB_PORT=3306`. El nombre de la base y el usuario llevan un prefijo (por ejemplo `u123456789_grifo`).
- PHP 8.2 o superior.
- Comprobar que se subieron los archivos ocultos `.env` y `.htaccess`.
- `database/`, `config/`, `registros/` y `tests/` están protegidos por `.htaccess`: no se pueden abrir desde el navegador.
