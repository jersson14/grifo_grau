# RESUMEN DEL SISTEMA DE VALES/CRÉDITOS

## ✅ ESTRUCTURA ACTUAL (CORRECTA)

### 1. REGISTRO DE VALES/CRÉDITOS
- **Dónde**: Desde el TURNO ACTUAL (view_cerrar_turno.php)
- **Cómo**: El administrador registra vales/créditos durante el turno
- **Tabla**: `ventas_credito`
- **Campos**:
  - id_credito
  - id_reporte (turno donde se generó)
  - id_cliente
  - numero_vale
  - monto
  - saldo_pendiente
  - estado (PENDIENTE, PAGADO, ANULADO)
  - fecha_vencimiento
  - observaciones

### 2. PAGOS DE CRÉDITOS
- **Dónde**: Módulo "Créditos Pendientes" (view_creditos_pendientes.php)
- **Cómo**: El administrador registra pagos/abonos de los clientes
- **Tabla**: `historial_pagos_credito`
- **Campos**:
  - id_pago_credito
  - id_credito
  - id_tipo_pago (Efectivo, Yape, BCP, Visa)
  - codigo_operacion
  - monto_pagado
  - saldo_anterior
  - saldo_nuevo
  - fecha_pago
  - id_usuario_registro
  - observaciones

## 📋 FLUJO DE TRABAJO

### PASO 1: Abrir Turno
1. Admin abre turno para un grifero
2. Se registran lecturas iniciales de surtidores

### PASO 2: Durante el Turno
1. Admin registra VALES/CRÉDITOS (ventas a crédito)
   - Selecciona cliente
   - Ingresa número de vale
   - Ingresa monto
   - Fecha de vencimiento
2. Admin registra PAGOS (efectivo, yape, bcp, visa)

### PASO 3: Cerrar Turno
1. Admin actualiza lecturas finales
2. Sistema calcula totales
3. Se cierra el turno

### PASO 4: Gestión de Créditos (Independiente del turno)
1. Admin ve lista de créditos pendientes
2. Cuando un cliente paga:
   - Selecciona el crédito
   - Registra el pago (puede ser pago parcial o total)
   - Sistema actualiza saldo pendiente
   - Si saldo = 0, estado cambia a PAGADO

## 🗂️ ARCHIVOS PRINCIPALES

### Modelos
- `model/model_turnos.php` - Gestión de turnos y vales
- `model/model_creditos.php` - Gestión de pagos de créditos

### Vistas
- `view/turnos/view_abrir_turno.php` - Abrir turno
- `view/turnos/view_cerrar_turno.php` - Gestionar turno (incluye registro de vales)
- `view/creditos/view_creditos_pendientes.php` - Gestión de pagos de créditos

### Controladores
- `controller/turnos/controlador_abrir_turno.php`
- `controller/turnos/controlador_registrar_credito.php`
- `controller/creditos/controlador_registrar_pago_credito.php`
- `controller/creditos/controlador_listar_creditos.php`

### JavaScript
- `js/console_turnos.js` - Funciones de turnos
- `js/console_creditos.js` - Funciones de créditos

## ❌ ARCHIVOS ELIMINADOS (YA NO SE USAN)

### Vistas
- `view/creditos/view_registrar_vale.php` - Los vales se registran desde el turno
- `view/turnos/view_registrar_pagos.php` - Los pagos se registran desde el turno

### Controladores
- `controller/creditos/controlador_registrar_vale.php`
- `controller/turnos/controlador_agregar_pago_reporte.php`
- `controller/turnos/controlador_listar_pagos_reporte.php`

### Menú actualizado
- ❌ Eliminado: "Registrar Pagos" del menú Turnos
- ❌ Eliminado: "Registrar Vale" del menú Créditos
- ✅ Agregado: "Gestionar Turno" en menú Turnos
- ✅ Renombrado: "Créditos Pendientes" → "Gestionar Créditos"

## 📊 REPORTES DISPONIBLES
1. Créditos pendientes
2. Historial de pagos por crédito
3. Top clientes con más deuda
4. Resumen de créditos (total, pagado, pendiente)

## 🎯 VENTAJAS DE ESTE SISTEMA
1. **Trazabilidad**: Cada vale está vinculado al turno donde se generó
2. **Control**: El admin gestiona todo desde un solo lugar
3. **Historial**: Se guarda cada pago realizado
4. **Flexibilidad**: Permite pagos parciales o totales
5. **Reportes**: Fácil identificar clientes morosos o vencidos
