# Manual del administrador

Esta guía explica cómo poner en marcha el sistema, configurarlo y controlar el día a día del grifo. Para lo que hace el grifero en su turno, vea el [Manual del grifero](manual_grifero.md): conviene leerlo también, porque usted revisará lo que él registra.

Todo lo que se describe aquí se hace desde la interfaz. No hace falta tocar archivos ni la base de datos.

---

## 1. Primer arranque

Con la base de datos vacía, al abrir el sistema aparece el **asistente de instalación**. Pide, en orden:

1. **Datos de la empresa**: razón social, RUC, dirección y teléfono.
2. **Administrador**: su usuario y su contraseña. Guárdela bien: es la cuenta con acceso total.
3. **Combustibles**: los productos que vende y su precio.
4. **Surtidores**: cuántos tiene, en qué máquina está cada uno y qué combustible despacha.

Cuando existe el primer usuario, el asistente se bloquea y ya no se puede volver a abrir. Lo demás se completa desde el menú **Configuración**.

---

## 2. Configuración

Menú → **Configuración**. Cada sección se guarda por separado.

| Sección | Qué contiene |
|---|---|
| **Módulos** | Activa o apaga los módulos opcionales: inventario, ventas individuales, caja, tienda, gastos, flotas, personal, facturación electrónica, página pública y chatbot. Un módulo apagado desaparece del menú y sus funciones dejan de responder. El registro de turnos funciona siempre, con cualquier combinación. |
| **Empresa** | Razón social, RUC, correo, teléfono, dirección y logo (aparece en el menú y en los reportes). |
| **Operación e impuestos** | IGV, días de crédito por defecto, tolerancia de diferencia en caja, tolerancia de varillaje y símbolo de moneda. También la regla de **contraseñas débiles o asignadas**: quien entra con una contraseña de ejemplo, muy común o asignada por un administrador debe cambiarla antes de usar el sistema. Déjela activada. |
| **Página principal** | Qué bloques muestra el Dashboard a cada rol y qué avisos aparecen en la campana: créditos vencidos, stock bajo, compras por vencer, diferencias de varillaje, turnos sin arqueo, diferencias de caja, comprobantes con problemas, flotas sobre su límite y copia de seguridad atrasada. |
| **Chatbot de IA** | Clave del servicio, modelo, límite mensual de gasto y roles con acceso. Ver la sección 9. |
| **Integraciones** | Facturación electrónica (Nubefact) y consulta de DNI/RUC. Ver la sección 6. |
| **Respaldos** | Copias de la base de datos. Ver la sección 10. |

> Las claves y tokens se guardan cifrados. Después de guardarlos, la pantalla solo indica «configurado»: nunca los vuelve a mostrar. Para cambiarlos, escriba el nuevo; si deja el campo vacío, se conserva el anterior.

---

## 3. Usuarios, roles y menú

### Usuarios

Menú → **Usuarios**. Para cada persona que usa el sistema cree un usuario con su DNI, nombre, usuario, contraseña y **rol**.

- Cada persona debe tener **su propio usuario**. Si dos griferos comparten uno, los reportes y la auditoría no sirven para saber quién hizo qué.
- Cuando alguien olvida su contraseña, asígnele una nueva desde **Usuarios**. Al entrar, el sistema le obliga a cambiarla, así usted no conoce la definitiva.
- Cuando alguien deja de trabajar en el grifo, **desactive** su usuario en vez de borrarlo: sus turnos y ventas siguen a su nombre.

### Roles y permisos

Menú → **Roles y permisos**. El sistema trae cuatro roles:

| Rol | Para quién | Qué puede hacer |
|---|---|---|
| **Administrador** | Dueño o gerente | Todo. No se puede recortar. |
| **Supervisor** | Encargado de turno | Controla turnos, caja, inventario, ventas, créditos, flotas, gastos y personal de todos los griferos; valida reportes. |
| **Contador** | Contabilidad | Consulta ventas, compras, créditos, caja, comprobantes y auditoría; registra gastos y cobros. |
| **Grifero** | Personal de playa | Registra su turno, ventas, cobros de créditos, varillaje y ventas de tienda. Solo ve sus propios datos. |

Puede crear roles nuevos o cambiar las capacidades de los existentes marcando o desmarcando permisos. Los cambios se aplican en la siguiente pantalla que abra el usuario.

### Editar menú

Menú → **Editar menú**. Cambie el título, el ícono, el orden o la visibilidad de cada opción, o limítela a ciertos roles. Ocultar una opción del menú **no quita el permiso**: para eso use **Roles y permisos**.

### Auditoría

Menú → **Auditoría**. Registra quién hizo cada acción importante (registrar o eliminar turnos, anular pagos, cambiar precios, emitir y anular comprobantes, cambiar permisos…), con fecha, hora y dirección IP. Revísela cuando algo no cuadre.

---

## 4. Combustibles, precios y surtidores

### Precios

Menú → **Productos (Combustibles)**.

- Para cambiar un precio desde ya, edite el producto.
- Para **programar** un cambio, elija el combustible, el **nuevo precio** y la fecha y hora **vigente desde**, y toque **Programar**. El precio cambia solo a esa hora.

Un turno se valoriza con el precio vigente **en el momento en que el grifero lo registra**. Por eso conviene programar los cambios para la hora de cambio de turno. Los turnos y ventas ya registrados conservan el precio con el que se guardaron.

El sistema admite cualquier combustible (Diesel, Regular, Premium, GLP u otros): los reportes los muestran todos.

### Surtidores

Menú → **Surtidores**. Cada surtidor tiene un código, una máquina y un combustible. Si cambia el combustible de una manguera, actualícelo aquí **antes** del siguiente turno, para que las lecturas se valoricen con el precio correcto.

### Clientes

Menú → **Clientes**. Datos de los clientes que compran al crédito o piden factura. Con la consulta de DNI/RUC configurada, el nombre y la dirección se completan solos al escribir el documento.

---

## 5. El día a día

### Validar los turnos

Cada turno que registra un grifero queda **Pendiente**. Menú → **Reportes** → **Validar Reportes**:

1. Toque el ojo para ver el detalle: lecturas por surtidor, galones y soles por combustible, pagos, vales y cuadre.
2. Compare las lecturas con los contómetros y el efectivo con lo que recibió.
3. Si está correcto, toque **Validar** y, si quiere, deje una observación. El turno pasa a **Validado** y el grifero lo ve así en **Mis Reportes**.

En **Gestión de Turnos** → **Historial de Turnos** ve todos los turnos con su cuadre, y en **Todos los Reportes** puede imprimir cualquiera en PDF.

### Caja y arqueo

Menú → **Caja y arqueo**.

- **Arqueo**: por cada turno, compare el **efectivo esperado** con el **efectivo contado** y toque **Guardar arqueo**. Las diferencias mayores que la tolerancia configurada quedan **pendientes de resolver**.
- **Depósitos**: registre cada depósito al banco con su número de operación y monto.
- Cada diferencia pendiente se cierra con **Resolver**: *se descuenta al grifero*, *se condona* o *está justificada*. Las que se descuentan pasan a **Personal** → **Faltantes de caja por descontar**, a nombre del grifero, hasta que registre el descuento.

### Créditos y vales

Menú → **Créditos/Vales**. Lista los vales por cliente, con su saldo y vencimiento, y los 10 clientes con más deuda.

- **Registrar Pago**: cobra un vale total o parcialmente.
- **Ver Historial**: muestra los pagos de un vale.
- **Anular Pago**: deshace un pago mal registrado. Queda en la auditoría.

Los créditos vencidos aparecen en los avisos (la campana de arriba) y en el Dashboard.

### Flotas

Menú → **Flotas**. Para clientes con varios vehículos: límite de crédito, placas autorizadas, vales del mes, consumo por placa y pagos recibidos. Cuando un grifero registra un vale de flota, el sistema le avisa si la placa no está autorizada o si el vale supera el límite.

### Inventario

Menú → **Inventario**.

- **Tanques y kardex**: cada tanque con su capacidad y stock mínimo, y el kardex de entradas (compras) y salidas (ventas por lecturas).
- **Varillaje**: las mediciones de los griferos comparadas con el stock del kardex. Las diferencias que superan la **tolerancia de varillaje** aparecen en los avisos; usted decide si registra un **ajuste**, que queda en la auditoría.
- **Compras y proveedores**: registre cada compra de combustible (proveedor, galones, costo) y sus pagos. La compra suma al stock del tanque.

### Gastos

Menú → **Gastos**. Gastos del grifo por categoría (luz, agua, mantenimiento, sueldos…). Un gasto mal registrado se **anula**, no se borra; marque **Incluir anulados** para verlos.

### Personal

Menú → **Personal**. Ficha de cada trabajador (DNI, cargo, fecha de ingreso, sueldo y su usuario del sistema), horario de la semana y faltantes de caja por descontar en el mes.

---

## 6. Facturación electrónica

Requiere el módulo **Facturación electrónica** activo y una cuenta en Nubefact.

### Configurarla

Configuración → **Integraciones** → **Facturación electrónica (Nubefact)**:

- **Ruta** y **Token**: los entrega Nubefact en el panel de su cuenta. Use los de **producción** solo cuando haya probado con los de prueba.
- **Series**: factura, boleta y las de notas de crédito y débito (deben empezar con F para facturas y con B para boletas). Deben coincidir con las que registró en Nubefact.
- **Boleta sin DNI: monto máximo**: por encima de ese monto, la boleta exige el documento del cliente.

### Comprobantes

Menú → **Comprobantes**. Lista todo lo emitido, con su estado:

| Estado | Qué hacer |
|---|---|
| **Pendiente de envío** | No se pudo enviar (por ejemplo, sin internet). Toque **Reintentar** o **Enviar pendientes y actualizar**. Se reenvía con el mismo número. |
| **Por confirmar en SUNAT** | Enviado; falta la respuesta de SUNAT. **Enviar pendientes y actualizar** la consulta. |
| **Aceptado** | Listo. Puede abrir el PDF, el XML y la constancia (CDR). |
| **Rechazado** | Lea el detalle, corrija el dato (por ejemplo, el RUC del cliente) y toque **Reintentar**. |

Una venta **siempre se guarda primero** y el comprobante se envía después. Si Nubefact o SUNAT no responden, la venta y el turno no se detienen.

- **Anular**: pide el motivo y envía la baja a SUNAT. Si el comprobante facturaba vales, esos vales vuelven a quedar disponibles.
- **Nota**: emite una **nota de crédito** (anulación, devolución, descuento, corrección) o de **débito** (intereses, aumento de valor) sobre una factura o boleta aceptada, con el motivo del catálogo de SUNAT. Para anulación, error en el RUC o devolución total, la nota va por todo lo que queda del comprobante; para los demás motivos, usted indica el monto.
- **Facturar vales de un cliente**: elija el cliente y las fechas, marque los vales y emita **un solo comprobante** con una línea por vale. Cada vale solo puede estar en un comprobante.

---

## 7. Reportes

Menú → **Reportes**.

| Reporte | Para qué sirve |
|---|---|
| **Análisis y rentabilidad** | Rentabilidad por combustible (precio promedio, costo por galón y margen bruto), ranking de griferos con sus faltantes y sobrantes, morosidad de créditos por antigüedad y ventas por hora y día de la semana. |
| **Ventas por Fecha** | Galones y soles por día y por combustible en un rango de fechas, con gráficos. |
| **Todos los Reportes** | Todos los turnos, con su detalle e impresión en PDF. |
| **Validar Reportes** | Turnos pendientes de revisión (sección 5). |

El **Dashboard** muestra a cada rol los bloques que eligió en Configuración → Página principal: turnos abiertos, ventas de hoy, créditos por cobrar, ventas de la semana y por combustible, entre otros.

---

## 8. Página pública

Requiere el módulo **Página pública** activo. Menú → **Página pública**: edite los textos, los servicios, el horario, la ubicación, el contacto y las promociones de la página que ven sus clientes en internet. Los **precios** se toman solos de los combustibles, así que siempre están al día.

Las empresas pueden pedir una cuenta de flota desde esa página. Las solicitudes aparecen en **Página pública** → **Solicitudes de flota**, donde puede revisarlas y convertirlas en flota.

---

## 9. Asistente de datos (chatbot)

Opcional. Responde preguntas como «¿Cuántos galones de Premium vendimos esta semana?» consultando los datos del sistema. **Solo consulta**: no puede crear, cambiar ni borrar nada.

Para activarlo:

1. Cree una clave en la consola de Anthropic (console.anthropic.com) y fije allí también un límite de gasto.
2. Configuración → **Chatbot de IA**: pegue la clave, elija el modelo, un **límite mensual** en dólares y los **roles con acceso**.
3. Configuración → **Módulos**: active **Chatbot de IA**.

Aparece el botón **Asistente** abajo a la derecha, en todas las pantallas. Cada usuario ve su propia conversación, que se conserva entre sesiones; **Nueva conversación** (el botón +) la limpia. Un grifero solo puede consultar sus propios datos. En **Consumo de este mes** ve cuánto se gastó; al llegar al límite, el asistente deja de responder hasta el mes siguiente.

> Las preguntas y los datos consultados se envían al servicio de Anthropic para generar la respuesta. Las respuestas son orientativas: las cifras para decisiones importantes se confirman en los reportes.

---

## 10. Respaldos y seguridad

### Respaldos

Configuración → **Respaldos**:

- **Generar copia ahora** crea una copia completa de la base de datos.
- **Conservar las últimas** indica cuántas copias se guardan en el servidor; las más antiguas se borran solas.
- **Descargue copias con frecuencia** y guárdelas fuera del servidor (USB, nube). Si el hosting falla, las copias que quedaron dentro se pierden con él. El sistema le avisa cuando pasa una semana sin copia.

Para restaurar una copia se importa el archivo en phpMyAdmin. Pida ayuda técnica para hacerlo: reemplaza todos los datos actuales.

### Buenas prácticas

- Cambie la contraseña de `admin` el primer día y no la comparta. Si varias personas administran, cree un usuario para cada una.
- Mantenga activada la regla de **contraseñas débiles o asignadas**.
- Tras 5 intentos fallidos, el acceso de ese usuario se bloquea 15 minutos. Si alguien queda bloqueado seguido, revise la **Auditoría**.
- Dé a cada rol solo lo que necesita. Un grifero no necesita ver los reportes de los demás.

---

## 11. Problemas frecuentes

| Problema | Qué hacer |
|---|---|
| Un grifero no ve una opción del menú | Revise su rol en **Roles y permisos** y que el módulo esté activo en **Configuración** → **Módulos**. |
| Un turno quedó con un error de lectura | No lo valide. En **Historial de Turnos** puede eliminarlo (queda en la auditoría) para que el grifero lo registre de nuevo con las lecturas correctas. |
| El turno salió con un precio distinto al esperado | El turno toma el precio vigente al registrarlo. Revise **Vigente desde** en **Productos** y programe los cambios para el cambio de turno. |
| Comprobantes quedan en «Pendiente de envío» | Revise la conexión a internet y la ruta y el token en **Integraciones**; luego **Enviar pendientes y actualizar**. |
| El asistente responde «aún no configuró la clave» o «límite mensual» | Configuración → **Chatbot de IA**. |
| Aviso «sin una copia de seguridad» | Configuración → **Respaldos** → **Generar copia ahora**, y descárguela. |
