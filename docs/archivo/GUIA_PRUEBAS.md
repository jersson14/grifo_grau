# 🧪 GUÍA DE PRUEBAS - SISTEMA DE GRIFO

## 📋 CHECKLIST DE PRUEBAS

---

## 1️⃣ CONFIGURACIÓN INICIAL

### Verificar Conexión a Base de Datos
```
✅ Puerto: 3307
✅ Base de datos: grifo_grau2
✅ Usuario: root
✅ Contraseña: (vacía)
```

### Verificar Datos Precargados
- ✅ 3 Productos (Diesel, Regular, Premium)
- ✅ 12 Surtidores (6 por máquina)
- ✅ 9 Clientes
- ✅ 5 Tipos de pago
- ✅ Al menos 1 usuario ADMINISTRADOR

---

## 2️⃣ MÓDULO: PRODUCTOS

### Prueba 1: Listar Productos
1. Iniciar sesión como ADMINISTRADOR
2. Ir a menú "Productos"
3. Verificar que se muestren 3 productos
4. Verificar precios actuales

**Resultado esperado:** ✅ Tabla con 3 productos

### Prueba 2: Actualizar Precio
1. Click en botón "Editar" de un producto
2. Cambiar precio (ej: 16.50)
3. Click en "Actualizar"
4. Verificar mensaje de éxito
5. Verificar que el precio se actualizó

**Resultado esperado:** ✅ Precio actualizado correctamente

### Prueba 3: Ver Historial
1. Expandir sección "Historial de Cambios de Precios"
2. Verificar que aparece el cambio reciente

**Resultado esperado:** ✅ Historial muestra el cambio

---

## 3️⃣ MÓDULO: CLIENTES

### Prueba 1: Registrar Cliente
1. Ir a menú "Clientes"
2. Click en "Nuevo Cliente"
3. Llenar datos:
   - Nombre: CLIENTE PRUEBA
   - DNI: 12345678
   - Teléfono: 987654321
   - Dirección: Av. Prueba 123
4. Click en "Registrar"

**Resultado esperado:** ✅ Cliente registrado

### Prueba 2: Ver Detalle
1. Click en botón "Ver Detalle" del cliente creado
2. Verificar información
3. Ver sección de créditos (debe estar vacía)

**Resultado esperado:** ✅ Modal con información completa

### Prueba 3: Editar Cliente
1. Click en "Editar"
2. Cambiar teléfono
3. Guardar cambios

**Resultado esperado:** ✅ Cliente actualizado

---

## 4️⃣ MÓDULO: SURTIDORES

### Prueba 1: Listar Surtidores
1. Ir a menú "Surtidores"
2. Verificar que se muestren 12 surtidores
3. Verificar vista por máquinas (abajo)

**Resultado esperado:** ✅ 12 surtidores listados

### Prueba 2: Actualizar Lectura
1. Click en botón "Actualizar Lectura"
2. Ingresar nueva lectura (mayor a la actual)
3. Confirmar actualización

**Resultado esperado:** ✅ Lectura actualizada

### Prueba 3: Cambiar Estado
1. Click en "Cambiar Estado"
2. Seleccionar "MANTENIMIENTO"
3. Confirmar

**Resultado esperado:** ✅ Estado cambiado

---

## 5️⃣ MÓDULO: USUARIOS

### Prueba 1: Crear Usuario GRIFERO
1. Ir a menú "Usuarios"
2. Click en "Nuevo Usuario"
3. Llenar datos:
   - DNI: 87654321
   - Nombres: GRIFERO
   - Apellidos: PRUEBA
   - Email: grifero@test.com
   - Usuario: grifero1
   - Contraseña: 123456
   - Rol: GRIFERO
4. Subir foto (opcional)
5. Click en "Registrar"

**Resultado esperado:** ✅ Usuario GRIFERO creado

### Prueba 2: Cambiar Contraseña
1. Click en botón "Cambiar Contraseña"
2. Ingresar nueva contraseña
3. Confirmar

**Resultado esperado:** ✅ Contraseña actualizada

---

## 6️⃣ MÓDULO: TURNOS (CRÍTICO)

### Prueba 1: Abrir Turno
1. Cerrar sesión
2. Iniciar sesión como GRIFERO (grifero1 / 123456)
3. Ir a "Abrir Turno"
4. Verificar que se carguen las lecturas automáticas
5. Seleccionar:
   - Fecha: Hoy
   - Turno: DÍA
   - Hora inicio: 07:00
   - Hora fin: 19:00
6. Click en "Abrir Turno"

**Resultado esperado:** ✅ Turno abierto, redirige a "Mi Turno"

### Prueba 2: Actualizar Lecturas
1. En "Mi Turno", ver tabla de lecturas
2. Cambiar lectura actual de un surtidor (ej: BS1)
3. Verificar que se calculen galones y total automáticamente

**Resultado esperado:** ✅ Cálculos automáticos funcionando

### Prueba 3: Registrar Pago - Efectivo
1. Click en "Agregar Pago"
2. Seleccionar:
   - Tipo: Efectivo
   - Monto: 500.00
3. Click en "Agregar"

**Resultado esperado:** ✅ Pago registrado en tabla

### Prueba 4: Registrar Pago - Yape
1. Click en "Agregar Pago"
2. Seleccionar:
   - Tipo: Yape
   - Código: 123456789
   - Monto: 300.00
3. Click en "Agregar"

**Resultado esperado:** ✅ Pago con código registrado

### Prueba 5: Registrar Crédito
1. Click en "Agregar Crédito"
2. Seleccionar:
   - Cliente: CLIENTE PRUEBA
   - N° Vale: 1001
   - Monto: 200.00
   - Fecha vencimiento: (7 días después)
3. Click en "Agregar"

**Resultado esperado:** ✅ Crédito registrado en tabla

### Prueba 6: Verificar Cuadre de Caja
1. Scroll hasta "Cuadre de Caja"
2. Verificar:
   - Total Ventas (suma de lecturas)
   - Total Pagos (500 + 300 = 800)
   - Total Créditos (200)
   - Faltante/Sobrante (calculado)

**Resultado esperado:** ✅ Cálculos correctos

### Prueba 7: Cerrar Turno
1. Verificar que todas las lecturas estén actualizadas
2. Ingresar descuentos: 0
3. Ingresar otros gastos: 0
4. Click en "CERRAR TURNO"
5. Confirmar

**Resultado esperado:** ✅ Turno cerrado, redirige a historial

### Prueba 8: Ver Historial
1. Verificar que el turno aparece en historial
2. Ver estado: CERRADO
3. Ver totales calculados

**Resultado esperado:** ✅ Turno en historial con datos correctos

---

## 7️⃣ MÓDULO: CRÉDITOS PENDIENTES

### Prueba 1: Ver Créditos Pendientes
1. Iniciar sesión como ADMINISTRADOR
2. Ir a "Créditos Pendientes"
3. Verificar resumen:
   - Créditos Pendientes: 1
   - Saldo Pendiente: S/. 200.00

**Resultado esperado:** ✅ Resumen correcto

### Prueba 2: Registrar Pago Parcial
1. Click en botón "Registrar Pago" del crédito
2. Seleccionar:
   - Tipo: Efectivo
   - Monto: 100.00
3. Click en "Registrar Pago"

**Resultado esperado:** ✅ Pago registrado, saldo: S/. 100.00

### Prueba 3: Ver Historial de Pagos
1. Click en botón "Ver Historial"
2. Verificar que aparece el pago de S/. 100.00
3. Ver saldo anterior y nuevo

**Resultado esperado:** ✅ Historial muestra el pago

### Prueba 4: Pagar Saldo Completo
1. Click en "Registrar Pago" nuevamente
2. Click en "Pagar Saldo Completo"
3. Seleccionar tipo de pago
4. Registrar

**Resultado esperado:** ✅ Crédito pagado, estado: PAGADO

### Prueba 5: Filtrar Créditos
1. Cambiar filtro a "PAGADO"
2. Click en "Buscar"
3. Verificar que aparece el crédito pagado

**Resultado esperado:** ✅ Filtro funcionando

### Prueba 6: Top Deudores
1. Scroll hasta "Top 10 Clientes con Más Deuda"
2. Verificar que la tabla esté vacía (todos pagados)

**Resultado esperado:** ✅ Tabla vacía o con datos correctos

---

## 8️⃣ PRUEBAS DE INTEGRACIÓN

### Flujo Completo: Turno con Crédito y Pago
1. Abrir nuevo turno
2. Actualizar lecturas
3. Registrar 2 pagos en efectivo
4. Registrar 1 crédito
5. Cerrar turno
6. Ir a Créditos Pendientes
7. Pagar el crédito
8. Verificar en historial

**Resultado esperado:** ✅ Flujo completo funciona

### Verificar Actualización de Surtidores
1. Ir a "Surtidores"
2. Verificar que las lecturas actuales coincidan con las del turno cerrado

**Resultado esperado:** ✅ Lecturas actualizadas

---

## 9️⃣ PRUEBAS DE VALIDACIÓN

### Validación 1: No Abrir Dos Turnos
1. Como GRIFERO con turno abierto
2. Intentar abrir otro turno
3. Debe mostrar alerta

**Resultado esperado:** ✅ No permite abrir segundo turno

### Validación 2: Lectura Mayor a Anterior
1. En turno abierto
2. Intentar poner lectura menor a la anterior
3. Debe rechazar

**Resultado esperado:** ✅ Validación funciona

### Validación 3: Pago Mayor a Saldo
1. En crédito con saldo S/. 50
2. Intentar pagar S/. 100
3. Debe mostrar error

**Resultado esperado:** ✅ Validación funciona

### Validación 4: Código Obligatorio
1. Registrar pago tipo Yape
2. No ingresar código
3. Debe solicitar código

**Resultado esperado:** ✅ Validación funciona

---

## 🔟 PRUEBAS DE PERMISOS

### Permiso 1: GRIFERO no ve Productos
1. Iniciar sesión como GRIFERO
2. Verificar que no aparece menú "Productos"

**Resultado esperado:** ✅ Menú oculto

### Permiso 2: ADMINISTRADOR ve todo
1. Iniciar sesión como ADMINISTRADOR
2. Verificar acceso a todos los módulos

**Resultado esperado:** ✅ Acceso completo

---

## ✅ CHECKLIST FINAL

Marcar cada prueba completada:

**Productos:**
- [ ] Listar productos
- [ ] Actualizar precio
- [ ] Ver historial

**Clientes:**
- [ ] Registrar cliente
- [ ] Ver detalle
- [ ] Editar cliente

**Surtidores:**
- [ ] Listar surtidores
- [ ] Actualizar lectura
- [ ] Cambiar estado

**Usuarios:**
- [ ] Crear GRIFERO
- [ ] Cambiar contraseña

**Turnos:**
- [ ] Abrir turno
- [ ] Actualizar lecturas
- [ ] Registrar pagos
- [ ] Registrar créditos
- [ ] Cerrar turno
- [ ] Ver historial

**Créditos:**
- [ ] Ver pendientes
- [ ] Registrar pago
- [ ] Ver historial
- [ ] Pagar completo

**Validaciones:**
- [ ] No dos turnos
- [ ] Lectura válida
- [ ] Pago válido
- [ ] Código obligatorio

**Permisos:**
- [ ] GRIFERO limitado
- [ ] ADMINISTRADOR completo

---

## 🐛 REPORTE DE ERRORES

Si encuentras errores, anota:

1. **Módulo:** _______________
2. **Acción:** _______________
3. **Error:** _______________
4. **Pasos para reproducir:** _______________

---

## 📊 RESULTADO FINAL

**Total de pruebas:** 30+
**Pruebas pasadas:** _____ / 30
**Porcentaje:** _____ %

---

**Fecha de pruebas:** _______________
**Probado por:** _______________
**Versión:** 1.0.0

---

## 🎉 SISTEMA LISTO

Si todas las pruebas pasan, el sistema está **100% listo para producción**.

¡Felicidades! 🚀
