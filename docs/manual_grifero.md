# Manual del grifero

Esta guía explica lo que usted hace en el sistema durante su turno: entrar, registrar ventas, cobrar créditos, medir tanques y cerrar el turno con su cuadre de caja. Todo funciona desde el celular.

Si una pantalla de esta guía no le aparece en el menú, su administrador no se la habilitó. Pídasela si la necesita.

---

## 1. Entrar al sistema

1. Abra en el navegador la dirección que le dio el administrador.
2. Escriba su **usuario** y su **contraseña** y toque **Entrar**.
3. La primera vez, o si el administrador le asignó una contraseña nueva, el sistema le pide cambiarla antes de seguir. Elija una que solo usted sepa: no use su DNI, su nombre ni `123456`.

Al entrar se abre directamente **Registrar Turno**. El menú está en el botón ☰ de arriba a la izquierda.

**Para salir**, toque su nombre arriba a la derecha y elija **Cerrar sesión**. Hágalo siempre si el celular o la computadora es compartido.

> Si olvidó su contraseña, pida al administrador que le asigne una nueva. Al entrar con ella, el sistema le pedirá cambiarla.

---

## 2. Durante el turno

### Registrar una venta (despacho)

Menú → **Ventas**.

1. Toque el **surtidor** donde despachó.
2. Escriba el **monto en soles**, o cambie a **Galones** si el cliente pidió una cantidad de galones. El sistema calcula lo otro con el precio vigente.
3. Elija el **medio de pago**. Con YAPE, BCP o VISA escriba el **código de operación** que aparece en el voucher o en la app del cliente: sin él no se guarda.
4. Si el cliente lo pide, marque **Boleta** o **Factura** y escriba su DNI o RUC. Para factura, el RUC es obligatorio.
5. Toque **Registrar venta**.

La venta queda guardada aunque el comprobante electrónico tarde en enviarse. Si el comprobante no sale, avise al administrador: se reintenta con el mismo número.

### Venta al crédito (vale)

Solo para clientes que el administrador autorizó a comprar al crédito.

- Si el cliente tiene **flota**, el sistema revisa la placa y el límite de crédito, y le avisa si la placa no está autorizada o si el vale supera el límite. Ante un aviso, consulte al supervisor antes de despachar.
- Anote siempre el **número de vale** y la **placa**.
- Los vales se registran en el **Paso 4** del turno (ver más abajo).

### Cobrar un crédito

Menú → **Créditos/Vales**.

1. Busque al cliente y toque el botón verde **Registrar Pago** (💵) en el vale.
2. Elija el **tipo de pago**, escriba el **monto** y, si corresponde, el **código de operación**.
3. Toque **Registrar Pago**. El saldo del vale baja de inmediato. Si el cliente paga todo, use **Pagar Saldo Completo**.

### Medir un tanque (varillaje)

Menú → **Inventario** → **Varillaje**.

1. Elija el **tanque** y la **fecha**.
2. Escriba la **medida en centímetros** que marca la varilla. El sistema la convierte a galones con la tabla del tanque.
3. Agregue una observación si notó algo raro (agua, olor, varilla dañada) y toque **Registrar medición**.

### Vender en la tienda

Menú → **Tienda**.

1. Busque el producto por nombre o código y tóquelo para agregarlo.
2. Ajuste la cantidad, elija el **medio de pago** y toque **Cobrar**.

Si un producto no aparece o no tiene stock, avise al administrador.

---

## 3. Cerrar el turno: Registrar Turno

Menú → **Gestión de Turnos** → **Registrar Turno**. El formulario tiene cinco pasos. Abajo de la pantalla siempre ve el **Total ventas** y el **Cuadre**.

### Paso 1. Información del turno

El número de documento se genera solo. Revise la **fecha**, elija **DÍA** o **NOCHE** y confirme la **hora de inicio** y la **hora de fin**.

### Paso 2. Lecturas de surtidores

Las **lecturas iniciales** ya vienen cargadas: son las finales del turno anterior. Usted escribe la **lectura final** de cada surtidor, tal como la marca el contómetro.

- Revise dos veces cada número antes de pasar al siguiente. Un dígito de más cambia todo el cuadre.
- Si una lectura final es menor que la inicial, los galones de esa fila salen negativos y el sistema no deja registrar el turno: revise el contómetro.
- El **Resumen por combustible** se calcula solo: galones y soles de cada producto.

Mientras falten lecturas, la barra de abajo dice **Faltan lecturas**.

### Paso 3. Métodos de pago (opcional)

Toque **Agregar Fila** por cada pago que no fue en efectivo (YAPE, BCP, VISA) y escriba su **código de operación** y su **monto**.

### Paso 4. Ventas a crédito (opcional)

Toque **Agregar Fila** por cada vale del turno y escriba **cliente**, **número de vale**, **placa**, **monto** y **fecha de vencimiento**.

### Paso 5. Otros conceptos y cuadre de caja

- **Descuentos**: rebajas autorizadas a clientes.
- **Otros gastos**: lo que pagó de la caja con autorización (por ejemplo, una compra urgente). Guarde el comprobante.
- **Efectivo**: el dinero que entrega al final del turno. Cuéntelo dos veces.

El **Cuadre de caja** compara lo vendido con lo que usted declara:

| Resultado | Qué significa |
|---|---|
| **S/ 0.00** | La caja cuadra. |
| **Faltante** (en rojo, con signo negativo) | Entrega menos de lo vendido. Revise lecturas, pagos y vales antes de registrar. |
| **Sobrante** | Entrega más de lo vendido. Suele ser un pago o un vale que olvidó anotar. |

Cuando todo esté revisado, toque **Registrar turno**. Se guardan todos los datos y las lecturas finales pasan a ser las iniciales del siguiente turno.

> Después de registrar ya no puede cambiar el turno. Si se equivocó, avise de inmediato al supervisor o al administrador.

---

## 4. Después del turno

### Mis reportes

Menú → **Reportes** → **Mis Reportes**. Ahí ve sus turnos, con sus galones, ventas, cuadre y estado:

- **Pendiente**: el supervisor aún no lo revisa.
- **Validado**: el supervisor lo revisó y lo aprobó.

Toque el ojo para ver el detalle o la impresora para sacar el reporte en PDF.

### Faltantes

Si su turno quedó con faltante, el supervisor lo revisa en el **arqueo de caja**. Un faltante que no se aclara puede descontarse de su pago, según lo que acordó con el grifo. Por eso conviene revisar bien el cuadre antes de registrar.

---

## 5. Problemas frecuentes

| Problema | Qué hacer |
|---|---|
| «Usuario o contraseña incorrectos» | Revise mayúsculas. Tras 5 intentos fallidos el acceso se bloquea 15 minutos. |
| La pantalla le devuelve al inicio de sesión | Su sesión venció por inactividad. Vuelva a entrar; lo que ya guardó no se pierde. |
| «Acceso denegado» | Esa acción no está permitida para su usuario. Pídasela al administrador si la necesita. |
| No le deja guardar un pago con YAPE, BCP o VISA | Falta el código de operación. |
| Las lecturas iniciales no coinciden con el contómetro | No las cambie por su cuenta: avise al supervisor antes de registrar el turno. |
| Se cortó el internet mientras registraba | Espere a que vuelva y revise en **Mis Reportes** si el turno quedó guardado antes de intentarlo otra vez. |

---

## 6. Asistente de datos (si está activo)

Si el administrador lo activó para su rol, verá abajo a la derecha el botón **Asistente**. Puede preguntarle, por ejemplo, «¿Cuántos galones vendí en mi último turno?». Solo consulta datos, no cambia nada, y sus respuestas son orientativas: para cuadrar, use siempre sus reportes.
