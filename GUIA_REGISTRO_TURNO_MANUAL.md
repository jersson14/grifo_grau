# GUÍA DE USO - REGISTRO DE TURNO MANUAL (ESTILO EXCEL)

## 📋 DESCRIPCIÓN

Se ha creado una nueva vista de registro de turno que replica el formato Excel que el cliente ya conoce. Esta vista permite ingresar datos manualmente en campos de texto, facilitando el trabajo para usuarios no técnicos.

## 🎯 CARACTERÍSTICAS PRINCIPALES

### 1. **FORMATO SIMILAR AL EXCEL**
- Diseño de tabla que replica el formato del Excel del cliente
- Campos de texto para entrada manual de datos
- Cálculos automáticos de totales
- Colores y estructura visual familiar

### 2. **LECTURAS DE SURTIDORES**
- Las lecturas aparecen **EN BLANCO** en cada turno nuevo
- Se pueden editar manualmente las lecturas actuales
- Los galones vendidos se calculan automáticamente
- Los totales en soles se calculan automáticamente

### 3. **REGISTRO DE PAGOS**
- **15 filas por defecto** para ingresar pagos manualmente
- Columnas para: YAPE, BCP, VISA, DESCUENTOS, EFECTIVO, OTROS GASTOS
- Cada método de pago tiene su campo de código de operación
- Los totales se calculan automáticamente

### 4. **REGISTRO DE CRÉDITOS**
- **15 filas por defecto** para ingresar créditos manualmente
- Campos: Monto, Nombre del Cliente, N° de Vale
- Autocompletado de nombres de clientes
- Total de créditos calculado automáticamente

### 5. **CÁLCULOS AUTOMÁTICOS**
- Total por máquina (Máquina 1 y Máquina 2)
- Total general de ventas
- Resumen por tipo de combustible (Diesel, Regular, Premium)
- Cuadre de caja con faltante/sobrante

## 📂 ARCHIVOS CREADOS

### Vistas
- `view/turnos/view_registrar_turno_manual.php` - Vista principal con formato Excel

### JavaScript
- `js/console_turnos_manual.js` - Lógica de cálculos y eventos

### Controladores
- `controller/turnos/controlador_guardar_turno_manual.php` - Guardar datos sin cerrar turno
- `controller/turnos/controlador_cerrar_turno_manual.php` - Cerrar turno y actualizar surtidores

### Base de Datos
- `database/tipos_pago.sql` - Tabla de tipos de pago (ejecutar si no existe)

## 🚀 CÓMO USAR

### 1. **Abrir Turno**
- Ir a "Abrir Turno" (vista existente)
- Completar datos del turno (fecha, grifero, horario)
- Las lecturas iniciales se cargan automáticamente

### 2. **Registrar Ventas**
- Ir a "Registro de Turno" (nueva vista)
- Ingresar las lecturas actuales en los campos de texto
- Los galones y totales se calculan automáticamente

### 3. **Registrar Pagos**
- En la sección "MÉTODOS DE PAGO":
  - Ingresar montos en las columnas correspondientes (YAPE, BCP, VISA, etc.)
  - Ingresar códigos de operación cuando aplique
  - Los totales se actualizan automáticamente

### 4. **Registrar Créditos**
- En la sección "VENTAS A CRÉDITO":
  - Ingresar monto del crédito
  - Escribir o seleccionar nombre del cliente (autocompletado)
  - Ingresar número de vale
  - El total se actualiza automáticamente

### 5. **Guardar Cambios**
- Hacer clic en "GUARDAR CAMBIOS" para guardar sin cerrar el turno
- Los datos se guardan en la base de datos

### 6. **Cerrar Turno**
- Hacer clic en "CERRAR TURNO"
- Se guardan todos los datos
- Se actualiza el estado del turno a CERRADO
- Se actualizan las lecturas de los surtidores
- Se calcula el faltante/sobrante

## 📊 CUADRE DE CAJA

El sistema calcula automáticamente:

```
Total Justificado = Pagos (Yape+BCP+Visa) + Créditos + Otros Gastos + Efectivo
Total Neto Ventas = Total Ventas - Descuentos
Diferencia = Total Justificado - Total Neto Ventas
```

- **FALTANTE**: Si la diferencia es negativa (falta dinero en caja)
- **SOBRANTE**: Si la diferencia es positiva (sobra dinero en caja)
- **CUADRADO**: Si la diferencia es cero (caja cuadrada)

## 🔧 INTEGRACIÓN CON EL MENÚ

Para agregar esta vista al menú del sistema, editar el archivo de menú correspondiente y agregar:

```php
<li class="nav-item">
    <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
        <i class="fas fa-file-alt nav-icon"></i>
        <p>Registro de Turno</p>
    </a>
</li>
```

## ⚠️ NOTAS IMPORTANTES

1. **Solo puede haber un turno abierto a la vez** en el sistema
2. **Las lecturas deben ser mayores o iguales** a las lecturas anteriores
3. **Los créditos requieren** que el cliente exista en el sistema
4. **Los códigos de operación** son obligatorios para Yape, BCP y Visa
5. **Guardar cambios frecuentemente** para no perder datos
6. **Al cerrar el turno** se actualizan automáticamente las lecturas de los surtidores

## 🎨 VENTAJAS DE ESTA VISTA

✅ **Familiar**: Formato similar al Excel que el cliente ya conoce
✅ **Simple**: Entrada manual de datos en campos de texto
✅ **Rápido**: Cálculos automáticos en tiempo real
✅ **Flexible**: 15 filas para pagos y créditos (se pueden agregar más si es necesario)
✅ **Visual**: Colores y estructura clara
✅ **Intuitivo**: No requiere conocimientos técnicos

## 🔄 DIFERENCIAS CON LA VISTA ANTERIOR

| Característica | Vista Anterior | Vista Nueva (Manual) |
|----------------|----------------|----------------------|
| Formato | Tablas DataTables | Estilo Excel |
| Entrada de datos | Modales | Campos de texto directos |
| Pagos | Agregar uno por uno | 15 filas predefinidas |
| Créditos | Agregar uno por uno | 15 filas predefinidas |
| Lecturas | Editar con eventos | Editar directamente |
| Cálculos | Al guardar | En tiempo real |

## 📞 SOPORTE

Si necesitas agregar más filas o modificar el formato, editar:
- `js/console_turnos_manual.js` - Cambiar el número 15 en las funciones `Generar_Filas_Pagos()` y `Generar_Filas_Creditos()`

---

**Fecha de creación**: Diciembre 2025
**Versión**: 1.0
