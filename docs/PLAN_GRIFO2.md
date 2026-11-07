# Plan Grifo 2

Evolucionar el sistema actual a un sistema completo de gestión de grifo, administrable desde el menú, con facturación electrónica y chatbot de IA opcionales.

- **Rama de trabajo:** `grifo2` (sale de `main`, que está en producción y no se toca)
- **Base de datos local:** `grifo2` en MariaDB de XAMPP, puerto 3307, `utf8mb4` (hoy vacía)
- **Auditoría:** 2026-11-06, sobre el commit `53d3c12`

Marca cada tarea al terminarla: `[ ]` pendiente, `[x]` hecha, `[~]` a medias (se indica qué falta). Registra las decisiones en la sección final.

---

## 0. Estado actual

Actualizado el 2026-11-07. Todo está en commits locales, sin publicar.

| Área | Estado |
|---|---|
| Fases 0 y 1 | Base y seguridad hechas y probadas sobre el **esquema provisional**. Falta contrastar con producción |
| Fase 3 | Hecho: Configuración, módulos activables, avisos en el Dashboard, menú editable, roles con permisos editables y auditoría. Falta: admitir cualquier cantidad de surtidores en el formulario de turno (3.6), notificaciones (3.8), asistente de instalación (3.9) y widgets por rol |
| Fase 6 | Chatbot funcional probado contra una **API simulada**. Falta: probarlo con una clave real (6.7) y añadir streaming |
| Fase 4 | Hecho y probado: tanques, kardex, varillaje, compras con cuentas por pagar, ventas individuales, precios (historial y programación) y caja con arqueo y depósitos (4.1 a 4.6). Falta: tienda, flotas, gastos, personal, reportes y cumplimiento sectorial |
| Fase 5 | Facturación con Nubefact: boletas, facturas, anulaciones, reintentos y bandeja, probados contra un **simulador**. Falta probar en su modo demo y notas de crédito, facturar vales y validar con el contador |
| Fase 2 | Hecho: PHP 8.4 y MySQL 8.4 en Docker, sin avisos de obsolescencia, mPDF 8.3, CI en GitHub Actions y copias de seguridad (2.6). Todas las pruebas pasan en XAMPP, PHP 8.4 y Docker. Falta PHPStan, `utf8mb4` (depende del esquema de producción) y confirmar el motor de producción (D3) |
| Fase 8 | Hecha: estilo unificado, celular, errores junto al campo, valores por defecto, contraste AA medido en 15 pantallas y datos mostrados como texto. Pruebas: `tests/ui.cjs` y `tests/contraste.cjs` |
| Fase 9 | En curso: página pública con Tailwind (precios en vivo, servicios, promociones, solicitudes de flota) y su administración, hechas y probadas. Falta: el resto de pantallas del panel y los módulos de gastos, tienda, flotas y personal |
| Fase 7 | Sin empezar |

**Pruebas:** `php tests/smoke.php`, `php tests/inventario.php`, `php tests/caja.php`, `tests/chatbot.php` y `tests/ui.cjs` en Edge real (estos dos, ver su cabecera). `php tests/roles.php` cubre roles, menú y auditoría. `php tests/permisos_grifero.php` lista lo que las pantallas del grifero piden y no se le permite: hay que revisarlo al cambiar permisos.

### Para el final: configuración a cargo del dueño

Decidido por el dueño el 2026-11-06: se retoma al terminar el desarrollo, antes de pasar a producción.

1. [ ] **Token de Decolecta:** revocarlo y pegar el nuevo en Configuración → Integraciones. Sigue en el historial de git. Al revocarlo, la consulta DNI/RUC deja de funcionar en producción hasta aplicar el parche.
2. [ ] **Esquema de producción:** en cPanel → phpMyAdmin → Exportar → Personalizado, solo Estructura y con rutinas. Guardarlo como `database/schema/produccion.sql` y contrastar con el esquema provisional.
3. [ ] **Nubefact en modo demo:** pedir la activación de la API (soporte@nubefact.com o WhatsApp 955 598762), pegar la ruta y el token en Configuración → Integraciones y hacer la primera emisión de prueba.
4. [ ] **Contador:** validar los puntos indicados abajo.
5. [ ] **Clave de API de Anthropic:** pegarla en Configuración → Chatbot de IA y probar el chatbot real.

Las credenciales se pegan siempre en Configuración, nunca en el chat.

**Validar en el modo demo de Nubefact, porque sus ejemplos no lo cubren:**

- Boleta sin cliente: se envía tipo `-`, documento `-` y denominación `VARIOS`.
- Unidad de medida `GLL` para galones. Es configurable.
- Precio unitario con 10 decimales, derivado de los importes.
- La respuesta de una anulación y los campos `enlace_del_pdf`, `enlace_del_xml` y `enlace_del_cdr`, que no están en su ejemplo PHP; sin ellos se usa `enlace`.

**Validar con el contador:** tope de boleta sin DNI (configurado en S/ 700), tratamiento de combustibles (ISC) y plazos de anulación. Hoy se emite como venta gravada con IGV.

**Pendiente parcial en estas fases:**

- **0.5:** las vistas `turno manual` usan tablas que los modelos no usan, y los controladores `dashboard/*` de `turnos` solo los llama código sin uso. Se conservan hasta confirmar con el esquema real.
- **3.6:** el formulario y el PDF del turno admiten cualquier cantidad de máquinas, pero los totales siguen fijos en tres combustibles (diesel, regular y premium), porque `reportes_turno` tiene una columna por cada uno. Un cuarto combustible no sumaría en los totales del turno.
- **1.8:** las contraseñas ya no pasan por `htmlspecialchars` y las pantallas ya escapan al mostrar. Los controladores antiguos aún escapan al guardar; dejar de hacerlo exige migrar los datos guardados.
- **1.10:** sigue habiendo controladores que muestran `getMessage()`.
- **Ventas y caja:** las ventas individuales no alteran el cuadre del turno. Se concilian en galones y, en Caja → Medios, en soles contra lo declarado. Falta decidir si deben prellenar los pagos al cerrar.
- **Precios:** un cambio de precio se aplica a todo el turno que se registre después. No se puede dividir un turno con dos precios; por eso conviene programar los cambios al inicio de un turno.
- **Permisos:** por defecto, los griferos no pueden editar, anular ni eliminar créditos, ni activar o desactivar clientes. Se cambia en Roles y permisos, sin tocar código.
- **Auditoría:** registra quién hizo qué y con qué datos, no el valor anterior de cada campo.

### Parche de seguridad para producción

La rama local `hotfix/seguridad` parte de `main` y aplica las Fases 0 y 1, además de corregir precios alterables, fechas un día antes y foto rota. **No está desplegada ni probada con la base real.** Para aplicarla:

1. Hacer copia de seguridad de la base y de los archivos.
2. Probarla primero en una copia del sitio con una copia de la base.
3. Crear `.env` desde `.env.example`, con los datos reales de conexión y un token nuevo.
4. Ejecutar `database/migrations/001_seguridad.sql`. Solo crea tablas nuevas.
5. Comprobar el login, un turno completo y un crédito. Después, desplegar en horario de baja actividad.

---

## 1. Decisiones pendientes del dueño

Estas respuestas condicionan el trabajo. La primera bloquea el arranque.

| # | Decisión | Recomendación | Bloquea |
|---|---|---|---|
| D1 | Exportar el esquema de producción | Ejecutar el comando de la tarea 0.1 | Todo |
| D2 | ¿`grifo2` lleva datos reales o solo de prueba? | Copia de producción con clientes y teléfonos anonimizados | 0.2 |
| D3 | ¿Qué versión de PHP y de base de datos hay en producción? El código sugiere un hosting compartido (`/home/estaciongoyo/public_html`) | Confirmar con `php -v` y `SELECT VERSION()` | Fase 2 |
| D4 | ¿Un grifo o varios? | Si puede haber más sedes o más clientes, definirlo antes de la Fase 3 | Fase 3 |
| D5 | ~~Proveedor de facturación~~ | **Decidido: Nubefact** (2026-11-06) | Hecho |
| D6 | ¿Enviar datos del grifo a un proveedor de IA externo? | Sí, con los límites de la Fase 6 y solo si el dueño lo acepta | Fase 6 |

---

## 2. Resultado de la auditoría

### Críticos: afectan a producción hoy

Se confirmaron leyendo el código, sin explotarlos. Producción tiene estos fallos ahora mismo, así que **conviene corregir S1 a S5 también en `main`** sin esperar a Grifo 2.

| ID | Problema | Dónde | Consecuencia |
|---|---|---|---|
| S1 | La sesión se crea con los datos que envía el navegador, sin comprobar nada | `controller/usuario/controlador_crear_sesion.php` | Cualquiera puede entrar como `ADMINISTRADOR` sin contraseña |
| S2 | Ningún controlador usa un control central de sesión: solo 6 de 148 mencionan la sesión | `controller/**` | Se puede leer y modificar datos sin iniciar sesión |
| S3 | Crear usuarios no exige sesión ni rol | `controller/usuario/controlador_registro_usuario.php` | Cualquiera puede crearse una cuenta de administrador |
| S4 | Subida de archivos sin validar nombre ni extensión | `controlador_registro_usuario.php`, `controlador_modificar_usuario.php`, `controller/empresa/controlador_empresa_modificar_foto.php` | Posible subida de un `.php` y ejecución de código en el servidor |
| S5 | Token de la API DNI/RUC escrito en el código y en el historial de git | `consulta-dni-ajax.php`, `consultar-ruc-ajax.php`, `test-dni.php`, y las copias en `view/` | Uso indebido de la cuenta. **Hay que revocarlo**: borrarlo del código no basta |
| S6 | El login devuelve al navegador toda la fila del usuario, incluido el hash de la contraseña | `controlador_iniciar_sesion.php`, `model_usuario.php:15` | Exposición de hashes |
| S7 | Scripts de prueba publicados que consultan datos sin sesión | `test_creditos.php`, `test_vales_cliente.php`, `test_conexion_turno.php`, `test-dni.php` | Exposición de datos de clientes y créditos |
| S8 | Sin protección CSRF en ningún formulario y sin límite de intentos de login | Todo el sistema | Acciones forzadas y fuerza bruta |

### Integridad y datos

| ID | Problema | Dónde |
|---|---|---|
| D-1 | **El esquema no está en el repo.** `init.sql` es un marcador vacío; faltan las tablas base y los `SP_*`. Sin producción no se puede reconstruir el sistema | `database/` |
| D-2 | Turno manual usa tablas que no coinciden con los modelos (`reporte_lecturas`, `reporte_pagos`, `creditos`) y tiene credenciales propias escritas en el código. Su vista no aparece en el menú | `controlador_guardar_turno_manual.php`, `controlador_cerrar_turno_manual.php` |
| D-3 | El dashboard consulta `creditos`, pero el módulo de créditos usa `ventas_credito`. Confirmar con el esquema si los indicadores son correctos | `controller/dashboard/controlador_estado_creditos.php`, `controlador_total_creditos_pendientes.php` |
| D-4 | Herencia de otro sistema: `SP_*_EXPEDIENTES`, `SP_CARGAR_SEGUIMIENTO_TRAMITE`, `SP_LISTAR_TOTAL_REUNIONES` | `model_usuario.php`, `model_pagos.php` |
| D-5 | Transacciones solo en turnos y créditos, y sin bitácora general de quién cambió qué | `model/**` |
| D-6 | `conexionPDO()` imprime el error de conexión y devuelve `null`, lo que muestra detalles internos y rompe el JSON | `model_conexion.php` |

### Mantenimiento

- `model_conexion.php` está en `.gitignore`, sin plantilla ni variables de entorno. Docker define `DB_HOST` y similares, pero el código no los lee.
- Código duplicado: `consulta-*` en la raíz y en `view/`, `PHPMailer-master` junto a `vendor/` (no se encontró ningún uso de PHPMailer), y un dashboard oculto dentro de `view/index.php`.
- 24 archivos `.md` en la raíz, varios contradictorios (algunos citan `grifo_grau2`).
- `htmlspecialchars` se aplica al guardar, no al mostrar, y también a contraseñas. Provoca datos alterados y puede romper contraseñas con `&` o comillas.
- Fotos subidas versionadas en git, `CURLOPT_SSL_VERIFYPEER => false` y `CURLOPT_TIMEOUT => 0` en las consultas externas.
- Sin pruebas automáticas ni CI. El análisis de sintaxis sobre `model/` y `controller/` en PHP 8.2 no dio errores.

### Plataforma

| Componente | Actual | Estado | Objetivo propuesto |
|---|---|---|---|
| PHP en Docker | 8.4.x (antes 8.1) | Con soporte | 8.4 |
| PHP en XAMPP | 8.2.12 | Parches de seguridad hasta el 31-12-2026 | 8.4 |
| MariaDB en XAMPP | 10.4.32 | Sin soporte | 11.4 LTS |
| MySQL en Docker | 8.4 LTS (antes 8.0) | Con soporte; modo estricto, a diferencia de XAMPP | Un solo motor en todos los entornos, según D3 |
| AdminLTE / jQuery | 3.2.0 / 3.6.0 | Funcionan | Mantener y actualizar jQuery a la última 3.x |

Comprobar estas fechas de soporte al iniciar la Fase 2.

### Módulos que no existen hoy

Inventario de tanques, compras, proveedores, varillaje, cierre de caja, comprobantes y configuración general: la búsqueda de esos términos en modelos, controladores y vistas no encontró ningún módulo.

Ingresos, gastos, indicadores y empresa tienen código, pero no figuran en el menú actual.

### Lo que conviene conservar

- Cálculo de turnos por lecturas, con transacciones y reversión al eliminar.
- Créditos con historial de pagos.
- Contraseñas con `password_hash`.
- Consultas preparadas: no se detectó SQL armado con variables en la búsqueda realizada, aunque la Fase 1 debe revisarlo a fondo.

---

## 3. Principios de diseño

1. **`main` no se toca** salvo los parches de seguridad que el dueño apruebe.
2. **Se mantiene el patrón actual** (MVC propio, AdminLTE, jQuery). Reescribir con un framework retrasaría el resultado sin aportar funciones al grifo.
3. **Todo se administra desde la interfaz.** Lo configurable se guarda en base de datos, no en el código. Los secretos van cifrados y nunca se muestran completos.
4. **Los módulos opcionales no afectan al núcleo.** Con facturación o IA desactivadas, o con su proveedor caído, el turno se registra y cierra con normalidad.
5. **Cada cambio de base de datos es una migración numerada** en `database/migrations/`.
6. **Nada de rutas nuevas sin `require_auth()`** y permiso explícito.

---

## 4. Fases

El orden importa: 0 y 1 son prerrequisito de todo. Después de la Fase 3, las fases 4, 5 y 6 pueden reordenarse según prioridad del negocio. La facturación depende de la tarea 4.6.

### Fase 0. Base de trabajo

Objetivo: que `grifo2` funcione igual que producción y sea reproducible.

- [x] Crear la rama `grifo2` y la base `grifo2`.
- [x] Apuntar `model/model_conexion.php` local a `grifo2`.
- [ ] **0.1** Exportar el esquema de producción y guardarlo como `database/schema/000_base.sql`:
  ```
  mysqldump -u USUARIO -p --no-data --routines --triggers --events --skip-definer grifo_grau > 000_base.sql
  ```
  `--skip-definer` solo existe en algunas versiones. Si da error, quitarlo y eliminar los `DEFINER=` del archivo.
- [ ] **0.2** Importar el esquema y datos según D2. Comprobar que el login, un turno completo y un crédito funcionan.
- [x] **0.3** Crear el ejecutor de migraciones y la tabla `schema_migrations`.
- [x] **0.4** Configuración por entorno: `.env` y `.env.example`. Unificar la conexión en un único punto y eliminar las credenciales de los controladores de turno manual.
- [ ] **0.5** Comparar el código con el esquema real. Resolver D-2, D-3 y D-4: lo que no se use se elimina.
- [x] **0.6** Limpieza: scripts `test*`, duplicados `consulta-*`, `PHPMailer-master`, documentación antigua movida a `docs/archivo/`.
- [x] **0.7** Pruebas de humo del flujo crítico: login, abrir y cerrar turno, crédito, pago.

**Listo cuando:** se puede instalar desde cero con un solo comando y el flujo crítico pasa.

### Fase 1. Seguridad

Objetivo: cerrar S1 a S8.

- [ ] **1.1** Revocar el token DNI/RUC y crear uno nuevo fuera del código.
- [x] **1.2** Rehacer el login: la sesión se crea en el servidor, tras verificar la contraseña, con `session_regenerate_id` y cookies `HttpOnly`, `SameSite` y `Secure`. Respuesta sin hash. Eliminar `controlador_crear_sesion.php`.
- [x] **1.3** Crear `controller/_bootstrap.php` con `require_auth()` y `require_permiso()`, y aplicarlo a todos los controladores. Una prueba automática falla si alguno queda sin protección.
- [x] **1.4** Validar permisos y propiedad de los datos en el servidor: que un grifero solo acceda a lo suyo.
- [x] **1.5** CSRF en toda petición que modifique datos, mediante `$.ajaxSetup` para no tocar cada llamada.
- [x] **1.6** Límite de intentos de login y cierre de sesión por inactividad.
- [x] **1.7** Subidas seguras: extensión y MIME reales, nombre generado por el servidor, carpeta sin ejecución de PHP.
- [ ] **1.8** Dejar de aplicar `htmlspecialchars` al guardar (contraseñas primero). Escapar al mostrar, incluido el HTML generado desde JS. Planificar la corrección de datos ya guardados.
- [ ] **1.9** Usuario de base de datos con permisos mínimos en lugar de `root`.
- [ ] **1.10** Registro de errores en archivo y mensajes genéricos al usuario.
- [ ] **1.11** Forzar el cambio de las credenciales por defecto.
- [ ] **1.12** Evaluar con el dueño la limpieza del historial de git. Depende de que el token esté revocado y de si el repo es público.

**Listo cuando:** una petición sin sesión a cualquier controlador recibe 401, y el rol `GRIFERO` recibe 403 en las acciones de administrador.

### Fase 2. Plataforma

- [x] **2.1** Docker con PHP 8.4 y MySQL 8.4 LTS. El contenedor espera a la base, aplica las migraciones y usa un usuario sin privilegios de administrador; la imagen no incluye `.env`, copias ni historial. Si D3 revela MariaDB en producción, basta cambiar la imagen `db`.
- [~] **2.2** Hecho: revisión de sintaxis con PHP 8.4.27 de los 282 archivos propios y de las librerías, sin avisos; las pruebas corren con todos los avisos activos. `conexionBD::cerrar_conexion()` ahora es estático. Con MySQL en modo estricto apareció un fallo que MariaDB local ocultaba (registrar cliente sin `id_usuario`), corregido tomando el usuario de la sesión. Falta: PHPStan (su descarga requiere aprobación del dueño) y quitar el código inalcanzable tras `return`.
- [ ] **2.3** Migrar a `utf8mb4` y fijar `charset=utf8mb4` en la conexión.
- [~] **2.4** mPDF 8.1.5 → 8.3.1 y fpdi 2.3.7 → 2.6.9 (la versión anterior daba avisos en PHP 8.4); el PDF del turno se revisó a la vista. Falta servir localmente lo que depende de CDN y actualizar jQuery.
- [~] **2.5** CI en `.github/workflows/pruebas.yml`: sintaxis y pruebas HTTP con PHP 8.2 y 8.4 contra MySQL 8.4, con simuladores. Sus pasos se reprodujeron en un contenedor Linux; en GitHub correrá al publicar la rama. Las pruebas aceptan `GRIFO_BASE` y `DB_*`, y `tests/router.php` permite servir el sistema con `php -S`. Falta: pruebas unitarias del cálculo de turnos y créditos.
- [x] **2.7** La clase de conexión pasa a `model/conexion_bd.php`, versionado. Se probó reutilizar una conexión por petición y se revirtió (2026-11-07): los procedimientos almacenados dejan resultados pendientes y la siguiente consulta en la misma conexión falla (lo detectó el acceso). Cada llamada abre su conexión, como siempre.
- [x] **2.6** Copias de seguridad automáticas con restauración probada.

Si XAMPP no ofrece PHP 8.4 al llegar aquí, el desarrollo local pasa a Docker.

### Fase 3. Sistema administrable

Es la base de lo que pediste: que todo se maneje desde el menú.

- [ ] **3.1** Panel **Configuración** con pestañas:
  - **Empresa y sedes:** RUC, razón social, logo, dirección.
  - **Apariencia:** colores y logo de login.
  - **Operación:** horarios y nombres de turno, tolerancia de diferencias, días de crédito.
  - **Impuestos:** IGV parametrizable, sin valores fijos en el código.
  - **Integraciones:** DNI/RUC, facturación, IA, correo y copias de seguridad.
- [ ] **3.2** Tabla `modulos` con interruptores desde la interfaz. Un módulo apagado desaparece del menú **y** su controlador responde 403.
- [x] **3.3** Menú dinámico guardado en base de datos: orden, íconos y visibilidad por rol, reemplazando el HTML fijo de `view/index.php`.
- [x] **3.4** Roles y permisos editables. Además de `ADMINISTRADOR` y `GRIFERO`, añadir `SUPERVISOR` y `CONTADOR`.
- [ ] **3.5** *(parcial)* Página principal configurable: widgets por rol, ventas del día, stock, créditos vencidos, turnos abiertos y alertas. Se elimina el dashboard oculto y se corrigen los indicadores.
- [ ] **3.6** *(parcial: máquinas dinámicas hechas)* Editor de catálogos: productos, surtidores, mangueras, máquinas y tipos de pago. Hoy el sistema asume 12 surtidores en 2 máquinas; debe admitir cualquier cantidad.
- [x] **3.7** Bitácora de auditoría consultable: quién, qué, cuándo, antes y después.
- [ ] **3.8** Notificaciones internas: créditos vencidos, stock bajo y turnos sin validar.
- [ ] **3.9** Asistente de instalación inicial para un grifo nuevo.

### Fase 4. Grifo completo

Reglas legales y tributarias: validar con el contador antes de implementar.

- [x] **4.1 Tanques e inventario:** capacidad, stock teórico, alertas de mínimo y kardex de combustible.
- [x] **4.2 Varillaje y mermas:** medición diaria, stock físico contra teórico y tolerancias.
- [x] **4.3 Compras y proveedores:** pedidos, recepción de cisterna, descarga a tanque, documentos y cuentas por pagar.
- [x] **4.4 Precios:** historial por producto, vigencia programada, y turnos que conservan el precio aplicado. Revisar el caso de un cambio de precio durante un turno.
- [x] **4.5 Caja:** apertura, arqueo por turno, faltantes y sobrantes por grifero, depósitos bancarios y conciliación Yape/POS.
- [x] **4.6 Ventas individuales:** registro de cada venta con surtidor, cliente y placa, conciliado con las lecturas del turno. **Es requisito de la facturación.**
- [ ] **4.7 Tienda y lubricantes:** productos no combustibles con stock.
- [ ] **4.8 Flotas y créditos avanzados:** línea de crédito con bloqueo, placas y conductores autorizados, estado de cuenta en PDF y envío por correo.
- [ ] **4.9 Gastos e ingresos:** integrar al menú los módulos existentes y añadir categorías.
- [ ] **4.10 Personal:** horarios, asistencia y descuentos por faltantes. La planilla completa queda fuera del alcance.
- [ ] **4.11 Reportes:** rentabilidad por producto, ranking de griferos, morosidad, ventas por hora, y exportación real a Excel y PDF.
- [ ] **4.12 Cumplimiento sectorial:** el contador o responsable define qué se necesita para Osinergmin (SCOP, precios) y qué reportes debe generar el sistema.

### Fase 5. Facturación electrónica (opcional)

**El requisito clave:** el sistema actual suma ventas por lecturas de surtidor, pero un comprobante necesita una venta individual con cliente e importe. Por eso depende de la tarea 4.6.

- [x] **5.1** Interruptor en Configuración → Integraciones, con selector de ambiente **Pruebas / Producción** y botón de prueba de conexión.
- [x] **5.2** Interfaz `ProveedorFacturacion` con `emitir()`, `anular()` y `consultar()`, para cambiar de proveedor sin tocar los módulos.
- [ ] **5.3** Primer adaptador según D5:
  - **PSE/OSE por API (recomendado):** el proveedor se encarga de firma y envío. Comparar precios y soporte vigentes antes de contratar.
  - **Emisión directa a SUNAT (por ejemplo, con la librería Greenter):** sin costo por proveedor, pero con certificado digital, firma, resúmenes y bajas a nuestro cargo.
- [ ] **5.4** Comprobantes: boleta, factura, notas de crédito y débito, series configurables, XML, CDR, PDF con QR, y envío por correo o WhatsApp.
- [x] **5.5** Cola con reintentos: si SUNAT o el proveedor no responden, la venta se guarda como pendiente y el turno sigue.
- [x] **5.6** Bandeja de comprobantes con estados: pendiente, aceptado, observado, rechazado y anulado.
- [ ] **5.7** Facturar vales de crédito acumulados de un cliente en un solo comprobante.
- [ ] **5.8** Validar con el contador los casos tributarios de combustibles antes de pasar a producción.

**Listo cuando:** se emite en ambiente de pruebas sin errores, y con el interruptor apagado no aparece nada de facturación ni cambia la operación.

### Fase 6. Chatbot de IA (opcional)

Permite preguntar en lenguaje natural, por ejemplo: «¿Cuántos galones de Premium se vendieron esta semana?» o «¿Qué clientes tienen créditos vencidos?».

- [x] **6.1** Interruptor en Configuración → Integraciones, con clave de API cifrada, modelo seleccionable, roles con acceso y límite mensual de gasto.
- [x] **6.2** Interfaz `ProveedorIA` con Claude como primer adaptador, usando el SDK oficial `anthropic-ai/sdk`. Comprobar la versión de PHP que exige antes de instalarlo.
- [x] **6.3** Herramientas predefinidas de solo lectura: `ventas_por_periodo`, `stock_tanques`, `creditos_vencidos`, `resumen_turno`, `ranking_griferos`, y otras según el uso real.
- [ ] **6.4** Después, si hace falta, `consulta_sql` para preguntas no previstas, con estas defensas:
  - Usuario de base de datos con `SELECT` solo sobre **vistas** `ia_*`.
  - Una sola sentencia `SELECT`, con límite de filas y tiempo.
- [ ] **6.5** Burbuja de chat en toda la interfaz con respuesta en streaming, tablas y gráficos simples, historial por usuario y aviso de que el resultado es orientativo.
- [x] **6.6** Bitácora de preguntas, consultas ejecutadas, tokens y costo. Panel de consumo en la interfaz.
- [ ] **6.7** Pruebas con un conjunto de preguntas reales y respuestas conocidas antes de habilitarlo a los usuarios.

**Reglas de seguridad, innegociables:**

- El modelo nunca accede a la tabla de usuarios, a hashes ni a secretos.
- Los permisos del usuario se aplican en el servidor: un grifero no puede obtener por el chat lo que no puede ver en pantalla.
- El contenido de la base de datos (como el nombre de un cliente) se trata como dato, nunca como instrucción. Las herramientas son solo de lectura, de modo que un texto malicioso no pueda modificar nada.
- La clave de API nunca llega al navegador.
- Los datos consultados se envían al proveedor de IA, y por eso D6 requiere la aceptación del dueño.

**Parámetros técnicos a usar:**

- Modelo por defecto `claude-opus-5-5`, con esfuerzo `low` o `medium`. Sonnet y Haiku quedan como opciones más baratas en el selector: medir calidad y costo con las pruebas de 6.7 antes de cambiarlo.
- `tool_choice` en `auto` con `strict: true` en las herramientas: este modelo rechaza el uso forzado de herramientas.
- Prompt caching con el system prompt y las herramientas estables al inicio. La fecha y hora van fuera del prefijo cacheado.
- Revisar `stop_reason` antes de leer el contenido, manejar `refusal` y activar el parámetro `fallbacks` en el servidor.

### Fase 7. Salida a producción

- [ ] **7.1** Copia completa de producción y ensayo de la migración sobre esa copia.
- [ ] **7.2** Conciliar totales antes y después: ventas, créditos, saldos y lecturas.
- [ ] **7.3** Piloto en paralelo con griferos reales.
- [ ] **7.4** Plan de reversión probado y cambio en horario de baja actividad.
- [ ] **7.5** Manuales para administrador y grifero, y capacitación.
- [ ] **7.6** Fusionar `grifo2` en `main` y publicar.

---

### Fase 8. Interfaz

Pedida por el dueño el 2026-11-07. Las pantallas nuevas usan un estilo propio y las antiguas, otro: unificarlas sin rehacer la lógica.

- [x] **8.1** Revisión de diseño de todas las pantallas: jerarquía, espaciado, colores y textos coherentes. Hoy conviven estilos en línea, DataTables y tablas simples.
- [x] **8.2** Uso en celular y tablet para el grifero: registrar turno y venta con campos grandes, teclado numérico y sin desplazamiento horizontal.
- [x] **8.3** Formularios más rápidos: valores por defecto razonables, validación al escribir y mensajes de error junto al campo, no solo en ventanas emergentes.
- [x] **8.4** Reemplazar los `alert()` y avisos de DataTables por mensajes integrados, y mostrar estados de carga y de lista vacía.
- [x] **8.5** Accesibilidad: etiquetas en todos los campos, contraste suficiente, navegación con teclado y foco visible.
- [x] **8.6** Escape de datos al mostrar en las pantallas antiguas (1.8), aprovechando la revisión.
- [x] **8.7** Pantalla de inicio de sesión con el logo y nombre de la empresa configurables.

**Listo cuando:** la prueba en navegador recorre todas las pantallas a ancho de celular sin desplazamiento horizontal, y cada formulario se puede completar solo con teclado.

### Fase 9. Interfaz nueva, página pública y módulos que faltan

Pedida por el dueño el 2026-11-07: interfaz que sorprenda, con Tailwind; página pública administrable desde el panel; agregar módulos que faltan y mejorar los existentes. Estilo visual: convencional tipo shadcn/ui (Inter, grises zinc, acento azul), elegido por el dueño el 2026-11-07 tras descartar el mundo «Carrocería y cinta reflectiva». Producto en `PRODUCT.md`; sistema en `DESIGN.md`.

- [x] **9.1** Tailwind 3.4 compilado con `npm run css` (solo en desarrollo) a `css/publico.css`, que se versiona. Fuente propia en `fuentes/` (Inter, licencia OFL).
- [x] **9.2** Página pública (`publico/landing.php`): precios en vivo desde Productos con sello si cambiaron hoy, servicios, promociones vigentes, solicitud de crédito para flotas y cómo llegar. Módulo `pagina_publica` apagado por defecto: entonces la dirección del sistema muestra el acceso. El acceso pasa a `login.php`.
- [x] **9.3** Administración en Configuración → Página pública: datos con validación, servicios con icono y orden, promociones con vigencia, bandeja de solicitudes de flota con estado y nota. Permiso `pagina_publica_gestionar`. Prueba: `tests/pagina_publica.php`.
- [~] **9.4** Panel con el mismo estilo. Hecho: armazón (cabecera y barra lateral claras, pantalla actual marcada), paleta y letra en todas las pantallas antiguas mediante `tailwind/panel.css`, acceso (`login.php`), tablero con indicadores y gráficos, y registrar turno con pasos numerados, tarjetas de surtidor compactas y barra de cuadre fija (de unos 9 100 px a 6 100 px de alto en celular). Contraste AA: 0 fallas en 15 pantallas. Falta: ventas, cerrar turno y el resto de pantallas con su propia composición.
- [ ] **9.5** Gastos del grifo: categorías, registro diario y comparación con las ventas.
- [ ] **9.6** Tienda / market: productos no combustibles, stock y venta rápida.
- [ ] **9.7** Flotas y vales: empresas con varias placas, límite de crédito, consumo por placa y estado de cuenta mensual. Recibe las solicitudes de 9.3.
- [ ] **9.8** Personal: datos, turnos asignados, asistencia y adelantos.

## 5. Riesgos

| Riesgo | Mitigación |
|---|---|
| El esquema de producción difiere de lo que el código espera | Tarea 0.5, con el esquema real |
| Cambiar el login interrumpe a los usuarios | Hacerlo en horario de baja actividad y avisar |
| Datos ya guardados con `htmlspecialchars` | Migración probada sobre una copia |
| Los griferos no adoptan el registro por venta | Interfaz rápida y piloto; las lecturas siguen como control |
| Gasto de IA descontrolado | Límite mensual y bitácora de consumo |
| Reglas tributarias mal aplicadas | Validación del contador y ambiente de pruebas |
| Alcance demasiado grande | Fases con entregas usables por separado |

---

## 6. Registro de decisiones

| Fecha | Decisión | Motivo |
|---|---|---|
| 2026-11-06 | Rama `grifo2`, base `grifo2` | Nombre pedido por el dueño, sin tocar `main` |
| 2026-11-06 | Mantener MVC propio sin framework | Entregar funciones al grifo antes que reescribir |
| 2026-11-06 | Facturación e IA detrás de interfaces de proveedor | Son opcionales y deben poder cambiar de proveedor |
| 2026-11-06 | Esquema provisional reconstruido desde el código | No hay volcado de producción disponible; se contrasta en 0.5 |
| 2026-11-06 | El chatbot usa solo consultas predefinidas, sin SQL libre | Menos riesgo; 6.4 queda para cuando haga falta |
| 2026-11-06 | Stock y ventas se aplican al cierre en un punto de guardado propio | Un fallo del módulo no debe impedir cerrar un turno |
| 2026-11-06 | Una compra que excede la capacidad del tanque avisa, pero no bloquea | El stock teórico puede estar desfasado respecto al real |
| 2026-11-06 | Nuevas tablas sin claves foráneas hacia tablas heredadas | Sus tipos no están contrastados con producción |
| 2026-11-06 | Al registrar un turno, el precio y la lectura inicial los decide el servidor | El navegador podía alterarlos y cambiar las ventas |
| 2026-11-06 | Los precios programados se aplican al usarlos, no con una tarea programada | El hosting compartido puede no tener cron |
| 2026-11-06 | Se ocultan al grifero los botones de crédito y cliente que no puede usar | Pendiente de que el dueño confirme esos permisos |
| 2026-11-06 | Los permisos son capacidades definidas en código y asignadas a roles desde la interfaz | Una ruta nueva exige una decisión en el catálogo; los roles se administran sin programar |
| 2026-11-07 | Se agrega la Fase 8 de interfaz | Pedida por el dueño |
| 2026-11-07 | La interfaz se mejora con una capa común (`css/grifo.css` y `console_utilidades.js`) en vez de tocar cada pantalla | Llega a las pantallas antiguas sin rehacer su lógica |
| 2026-11-07 | Se retira `css/colores-suaves.css` | Sus tonos pastel con texto blanco no cumplían el contraste mínimo |
| 2026-11-07 | Las copias se generan con PHP, sin mysqldump | El hosting compartido suele no ofrecerlo |
| 2026-11-06 | Facturación con Nubefact, elegida por el dueño | Menor carga técnica que la emisión directa |
| 2026-11-06 | La venta se guarda antes de emitir y conserva su número al reintentar | Una caída del proveedor no debe perder ventas ni duplicar comprobantes |
| 2026-11-06 | El rol y el estado del usuario se leen en cada petición | Un cambio o una baja surte efecto de inmediato, a cambio de una consulta más |
| 2026-11-07 | Docker con PHP 8.4 y MySQL 8.4 LTS | Ambos con soporte; el modo estricto de MySQL descubre errores que MariaDB local oculta |
| 2026-11-07 | Se revierte la conexión compartida por petición | Con procedimientos almacenados, los resultados pendientes bloquean la siguiente consulta; el código antiguo no los cierra |
| 2026-11-07 | Tailwind compilado y versionado, no React + shadcn/ui | Funciona en el hosting compartido sin Node; se adopta el lenguaje visual de shadcn, no su código. Elegido por el dueño |
| 2026-11-07 | Mundo visual «Carrocería y cinta reflectiva» | Elegido por el dueño entre las direcciones presentadas; reemplazado el mismo día |
| 2026-11-07 | Estilo convencional tipo shadcn/ui en todo el sistema | El dueño pidió «algo normal como los sistemas normales»; referencias Vercel, Linear y Stripe |
| 2026-11-07 | La página pública es un módulo apagado por defecto y tiene vista previa | Se prepara el contenido antes de publicarla, sin mostrar una página vacía |
| 2026-11-07 | La clase de conexión pasa a un archivo versionado | `model_conexion.php` está fuera de git y sus mejoras no llegaban a las instalaciones |
