# Librerías del navegador

Copias fijas de las librerías que antes se cargaban desde un CDN, para que el panel funcione sin
internet externo y no cambie solo cuando el proveedor publica una versión nueva.

| Librería | Versión | Archivo |
|---|---|---|
| jQuery | 3.7.1 | `jquery-3.7.1/jquery.min.js` |
| SweetAlert2 | 11.26.25 | `sweetalert2-11.26.25/sweetalert2.all.min.js` (incluye su CSS) |
| Select2 | 4.1.0-rc.0 | `select2-4.1.0-rc.0/select2.min.js` y `select2.min.css` |
| Chart.js | 4.6.1 | `chart.js-4.6.1/chart.umd.min.js` |

Cada archivo es el publicado en npm, comprobado con el SHA-256 que informa jsDelivr y contra unpkg.
Para actualizar una: crear una carpeta nueva con la versión, cambiar la ruta en `view/index.php`
(y `login.php` para jQuery y SweetAlert2), correr `tests/ui.cjs` y borrar la carpeta anterior.
Bootstrap, AdminLTE, DataTables y Font Awesome siguen en `plantilla/` y `utilitario/`.
