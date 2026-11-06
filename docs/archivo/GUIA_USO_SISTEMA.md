# 📘 GUÍA DE USO DEL SISTEMA DE GRIFO

## 🎯 ROL: ADMINISTRADOR

El administrador es quien gestiona TODO el sistema. No tiene turnos propios, sino que gestiona los turnos de los griferos.

---

## 📝 FLUJO COMPLETO DE TRABAJO

### 1️⃣ ABRIR TURNO

**Ubicación**: Menú → Turnos → Abrir Turno

**Pasos**:
1. Seleccionar la **fecha del turno**
2. Seleccionar el **tipo de turno** (DÍA o NOCHE)
3. Seleccionar el **grifero** que trabajará
4. Ingresar **hora de inicio** y **hora fin**
5. Revisar las **lecturas iniciales** de los surtidores (se cargan automáticamente)
6. Clic en **"Abrir Turno"**

**Importante**: 
- Solo puede haber UN turno abierto a la vez
- Si intentas abrir otro turno, el sistema te avisará que debes cerrar el actual primero

---

### 2️⃣ GESTIONAR TURNO ACTUAL

**Ubicación**: Menú → Turnos → Gestionar Turno

Aquí el administrador gestiona el turno que está abierto actualmente.

#### A) ACTUALIZAR LECTURAS DE SURTIDORES
- Ingresar las **lecturas actuales** de cada surtidor
- El sistema calcula automáticamente:
  - Galones vendidos
  - Total en soles por surtidor
  - Totales por tipo de combustible (Diesel, Regular, Premium)

#### B) REGISTRAR VALES/CRÉDITOS (Ventas a Crédito)
**Ejemplo**: Un cliente lleva combustible pero pagará después

1. Clic en **"Agregar Crédito"**
2. Seleccionar el **cliente**
3. Ingresar **número de vale** (ej: 1291, 1292, etc.)
4. Ingresar **monto** del crédito
5. Seleccionar **fecha de vencimiento**
6. Agregar **observaciones** si es necesario
7. Clic en **"Registrar"**

**Resultado**: 
- El vale queda registrado en el turno
- Se suma al total de créditos del turno
- El cliente queda con deuda pendiente

#### C) REGISTRAR PAGOS DEL TURNO
**Ejemplo**: Pagos en efectivo, Yape, BCP, Visa que se recibieron durante el turno

1. Clic en **"Agregar Pago"**
2. Seleccionar **tipo de pago** (Efectivo, Yape, BCP, Visa)
3. Si es Yape/BCP/Visa, ingresar **código de operación**
4. Ingresar **monto**
5. Agregar **observaciones** si es necesario
6. Clic en **"Registrar"**

**Resultado**:
- Se suma al total de pagos del turno
- Se actualiza el cuadre de caja

#### D) CUADRE DE CAJA
El sistema muestra automáticamente:
- **Total de ventas** (suma de todos los combustibles vendidos)
- **Total de pagos** (efectivo + yape + bcp + visa)
- **Total de créditos** (vales registrados)
- **Descuentos** (si aplica)
- **Otros gastos** (si aplica)
- **Faltante o Sobrante** (diferencia entre ventas y pagos)

---

### 3️⃣ CERRAR TURNO

**Ubicación**: Desde "Gestionar Turno"

**Pasos**:
1. Verificar que todas las **lecturas estén actualizadas**
2. Verificar que todos los **pagos estén registrados**
3. Verificar que todos los **vales/créditos estén registrados**
4. Revisar el **cuadre de caja**
5. Ingresar **descuentos** si aplica
6. Ingresar **otros gastos** si aplica
7. Clic en **"Cerrar Turno"**

**Resultado**:
- El turno cambia a estado CERRADO
- Las lecturas de los surtidores se actualizan
- Ya se puede abrir un nuevo turno

---

### 4️⃣ GESTIONAR PAGOS DE CRÉDITOS

**Ubicación**: Menú → Créditos → Créditos Pendientes

Esta sección es INDEPENDIENTE de los turnos. Aquí se gestionan los pagos de los clientes que tienen deuda.

#### A) VER CRÉDITOS PENDIENTES
- Lista de todos los vales/créditos pendientes
- Muestra: cliente, monto total, monto pagado, saldo pendiente
- Indica si están vencidos

#### B) REGISTRAR PAGO DE UN CRÉDITO
**Ejemplo**: El cliente Jesica viene a pagar S/. 100 de su deuda

1. Buscar el **crédito del cliente**
2. Clic en **"Registrar Pago"**
3. Seleccionar **tipo de pago** (Efectivo, Yape, BCP, Visa)
4. Si es Yape/BCP/Visa, ingresar **código de operación** (ej: 0451329)
5. Ingresar **monto a pagar**
   - Puede ser pago parcial (ej: S/. 100 de S/. 300)
   - O pago total (clic en "Pagar Saldo Completo")
6. Agregar **observaciones** (ej: "Pago Yape cod. 0451329")
7. Clic en **"Registrar Pago"**

**Resultado**:
- Se descuenta del saldo pendiente
- Si paga todo, el estado cambia a PAGADO
- Si paga parcial, sigue en PENDIENTE con el nuevo saldo
- Queda registrado en el historial de pagos

#### C) VER HISTORIAL DE PAGOS
- Clic en **"Ver Historial"** de un crédito
- Muestra todos los pagos realizados:
  - Fecha de pago
  - Tipo de pago
  - Código de operación
  - Monto pagado
  - Saldo anterior y nuevo
  - Quién lo registró

---

## 📊 REPORTES DISPONIBLES

### 1. Historial de Turnos
- Ver todos los turnos (abiertos y cerrados)
- Filtrar por fecha, grifero, estado
- Ver detalle de cada turno

### 2. Créditos Pendientes
- Lista de clientes con deuda
- Saldo pendiente por cliente
- Créditos vencidos

### 3. Top Deudores
- Los 10 clientes con más deuda
- Útil para hacer seguimiento

### 4. Resumen de Créditos
- Total de créditos
- Monto total prestado
- Monto pagado
- Saldo pendiente

---

## ⚠️ REGLAS IMPORTANTES

1. **Solo UN turno abierto a la vez**: No puedes abrir un nuevo turno hasta cerrar el actual

2. **Los vales se registran EN el turno**: No hay módulo separado para registrar vales

3. **Los pagos de créditos son INDEPENDIENTES**: Se registran en cualquier momento, no necesariamente durante un turno

4. **Cada pago queda registrado**: Hay historial completo de todos los pagos

5. **El administrador gestiona TODO**: Abre turnos, registra vales, registra pagos, cierra turnos

---

## 💡 EJEMPLO PRÁCTICO

### Escenario: Turno de DÍA con Estefany

**07:00 AM - Abrir Turno**
- Admin abre turno DÍA para Estefany
- Lecturas iniciales: Surtidor 1A = 1000.000 gal

**Durante el turno**
- Cliente 1: Compra S/. 50 en efectivo → Registrar pago
- Cliente 2 (Jesica): Lleva S/. 126.90 a crédito → Registrar vale N° 1291
- Cliente 3: Paga S/. 80 con Yape → Registrar pago con código
- Cliente 4 (Jesica): Lleva S/. 119.10 a crédito → Registrar vale N° 1292

**19:00 PM - Cerrar Turno**
- Lectura final: Surtidor 1A = 1150.000 gal
- Total vendido: 150 galones
- Total en soles: S/. 376.00
- Pagos: S/. 130.00
- Créditos: S/. 246.00
- Cuadre: OK (376 = 130 + 246)

**Al día siguiente**
- Jesica viene a pagar S/. 100 del vale 1291
- Admin va a "Créditos Pendientes"
- Busca vale 1291 de Jesica
- Registra pago de S/. 100 con Yape cod. 0451329
- Saldo nuevo: S/. 26.90 (de S/. 126.90)

---

## 🎯 RESUMEN

✅ **Turnos**: Abrir → Gestionar → Cerrar
✅ **Vales**: Se registran DURANTE el turno
✅ **Pagos de créditos**: Se registran CUANDO el cliente paga
✅ **Todo lo gestiona**: El ADMINISTRADOR
