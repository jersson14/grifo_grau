# 🎨 EJEMPLO VISUAL - REGISTRO DE TURNO MANUAL

## 📸 VISTA PREVIA DEL SISTEMA

Este documento muestra cómo se verá el sistema de Registro de Turno Manual.

---

## 1️⃣ ENCABEZADO DEL REPORTE

```
╔════════════════════════════════════════════════════════════════════╗
║  REPORTE DE VENTAS DIARIAS                          DOC: 0001      ║
╠════════════════════════════════════════════════════════════════════╣
║  GRIFERO: JHALIA MOTTA PALOMINO                                    ║
║  TURNO: NOCHE (Del 31/10 al 01/11 de noviembre)                   ║
║  FECHA: 01/11/2025                                                 ║
║  HORARIO: 7:00 PM - 7:00 AM                                        ║
╚════════════════════════════════════════════════════════════════════╝
```

---

## 2️⃣ TABLA DE LECTURAS - MÁQUINA 1

```
╔═══════════════════════════════════════════════════════════════════════════════════╗
║                           🔶 ISLA - MÁQUINA 1                                     ║
╠═══════════╦═══════════╦═════════════╦═══════════════╦═══════════╦═════════╦═══════╣
║  FECHA    ║ PRODUCTO  ║  LECTURA    ║   LECTURA     ║  GALONES  ║ PRECIO  ║ TOTAL ║
║           ║           ║  ANTERIOR   ║   ACTUAL      ║ VENDIDOS  ║   S/.   ║  S/.  ║
╠═══════════╬═══════════╬═════════════╬═══════════════╬═══════════╬═════════╬═══════╣
║ 01/11/25  ║ DS1-DISEL ║  18943.062  ║ [19038.290]   ║   95.228  ║  15.69  ║1494.13║
║ 01/11/25  ║ DS2-DIS1  ║  43582.573  ║ [43698.255]   ║  115.682  ║  15.69  ║1815.04║
║ 01/11/25  ║ R1-REGULAR║  31310.400  ║ [31314.402]   ║    4.002  ║  14.99  ║  59.99║
║ 01/11/25  ║ R2-REGULAR║  31891.077  ║ [31891.077]   ║    0.000  ║  14.99  ║   0.00║
║ 01/11/25  ║ P1-PREMIUM║  24414.271  ║ [24426.921]   ║   12.650  ║  15.89  ║ 201.01║
║ 01/11/25  ║ P2-PREMIUM║  19611.329  ║ [19611.329]   ║    0.000  ║  15.89  ║   0.00║
╠═══════════╩═══════════╩═════════════╩═══════════════╩═══════════╩═════════╬═══════╣
║                                                            TOTAL 1 ║ 3570.17║
╚════════════════════════════════════════════════════════════════════╩═══════╝

Nota: Los valores entre [ ] son campos editables
```

---

## 3️⃣ TABLA DE LECTURAS - MÁQUINA 2

```
╔═══════════════════════════════════════════════════════════════════════════════════╗
║                           🔶 ISLA - MÁQUINA 2                                     ║
╠═══════════╦═══════════╦═════════════╦═══════════════╦═══════════╦═════════╦═══════╣
║  FECHA    ║ PRODUCTO  ║  LECTURA    ║   LECTURA     ║  GALONES  ║ PRECIO  ║ TOTAL ║
║           ║           ║  ANTERIOR   ║   ACTUAL      ║ VENDIDOS  ║   S/.   ║  S/.  ║
╠═══════════╬═══════════╬═════════════╬═══════════════╬═══════════╬═════════╬═══════╣
║ 01/11/25  ║ DS1-DISEL ║  42568.215  ║ [42810.633]   ║  242.418  ║  15.69  ║3803.54║
║ 01/11/25  ║ DS2-DIS1  ║  28561.400  ║ [28653.854]   ║   92.454  ║  15.69  ║1450.40║
║ 01/11/25  ║ R1-REGULAR║  24106.990  ║ [24112.326]   ║    5.336  ║  14.99  ║  79.99║
║ 01/11/25  ║ R2-REGULAR║  10651.636  ║ [10653.126]   ║    1.490  ║  14.99  ║  22.33║
║ 01/11/25  ║ P1-PREMIUM║  28461.038  ║ [28466.038]   ║    5.000  ║  15.89  ║  79.45║
║ 01/11/25  ║ P2-PREMIUM║  33471.638  ║ [33473.188]   ║    1.550  ║  15.89  ║  24.63║
╠═══════════╩═══════════╩═════════════╩═══════════════╩═══════════╩═════════╬═══════╣
║                                                            TOTAL 2 ║ 5460.34║
╚════════════════════════════════════════════════════════════════════╩═══════╝
```

---

## 4️⃣ TOTAL GENERAL

```
╔═══════════════════════════════════════════════════════════════════╗
║                    TOTALES (1+2): S/. 9,030.51                    ║
╚═══════════════════════════════════════════════════════════════════╝
```

---

## 5️⃣ TABLA DE MÉTODOS DE PAGO

```
╔═══════════════════════════════════════════════════════════════════════════════════════════════════════════════╗
║                                          💰 MÉTODOS DE PAGO                                                    ║
╠═══════╦═══════════════╦═══════╦═══════════════╦═══════╦═══════════════╦═══════════╦═══════════╦══════════════╣
║ YAPE  ║ COD. OPERAC.  ║  BCP  ║ COD. OPERAC.  ║ VISA  ║ COD. OPERAC.  ║DESCUENTOS ║ EFECTIVO  ║OTROS GASTOS  ║
╠═══════╬═══════════════╬═══════╬═══════════════╬═══════╬═══════════════╬═══════════╬═══════════╬══════════════╣
║ [171] ║    [021]      ║ [683] ║  [06831340]   ║ [140] ║   [308014]    ║    [5]    ║ [1059.5]  ║              ║
║ [280] ║    [778]      ║[505.74]║ [06856846]   ║[636.17]║   [360145]    ║           ║           ║              ║
║  [10] ║    [668]      ║[313.8]║ [MAXIMO JESUS]║       ║               ║           ║           ║              ║
║  [20] ║    [745]      ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
║       ║               ║       ║               ║       ║               ║           ║           ║              ║
╠═══════╬═══════════════╬═══════╬═══════════════╬═══════╬═══════════════╬═══════════╬═══════════╬══════════════╣
║ 481.00║               ║1,503.54║              ║776.17 ║               ║   5.00    ║ 1,059.50  ║     0.00     ║
╚═══════╩═══════════════╩═══════╩═══════════════╩═══════╩═══════════════╩═══════════╩═══════════╩══════════════╝
```

---

## 6️⃣ TABLA DE VENTAS A CRÉDITO

```
╔═══════════════════════════════════════════════════════════════════════════════╗
║                          💳 VENTAS A CRÉDITO                                  ║
╠═══════════════════╦═══════════════════════════════════════╦═══════════════════╣
║ MONTO DE CRÉDITO  ║      NOMBRE DEL CLIENTE               ║    N° DE VALE     ║
╠═══════════════════╬═══════════════════════════════════════╬═══════════════════╣
║      [335]        ║      [ANALI HUAMANI]                  ║      [1521]       ║
║      [196]        ║      [ADOLFO GOMEZ]                   ║      [1522]       ║
║      [779.2]      ║      [PEDRO PUMACAYO]                 ║      [1523]       ║
║      [911]        ║      [JAVIER PUMACAYO]                ║      [1524]       ║
║      [624.12]     ║      [CARLA MAYHUIRE]                 ║      [1525]       ║
║      [156.5]      ║      [YESICA ARENAS]                  ║      [1526]       ║
║      [386.2]      ║      [JOSE COAQUIRA]                  ║      [1527]       ║
║      [312.8]      ║      [JOSE COAQUIRA]                  ║      [1528]       ║
║      [668.1]      ║      [PAULINA LUNA]                   ║      [1529]       ║
║      [467]        ║      [JOSE COAQUIRA]                  ║      [1530]       ║
║      [212]        ║      [HECTOR ROJAS]                   ║      [1531]       ║
║      [222]        ║      [NILSON GOMEZ]                   ║      [1532]       ║
║      [1146]       ║      [HUBERT PUMACAYO]                ║      [1533]       ║
║      [634]        ║      [MIJAEL CAMARGO]                 ║      [1534]       ║
║               ║                                       ║               ║
╠═══════════════════╬═══════════════════════════════════════╬═══════════════════╣
║    7,050.42       ║                                       ║                   ║
╚═══════════════════╩═══════════════════════════════════════╩═══════════════════╝
```

---

## 7️⃣ RESUMEN POR COMBUSTIBLE

```
╔═══════════════════════════════════════════════════════════╗
║           📊 RESUMEN POR COMBUSTIBLE                      ║
╠═══════════════════════════════════════╦═══════════════════╣
║  DIESEL:                              ║  S/. 6,563.11     ║
║  GASOLINA REGULAR:                    ║  S/. 162.31       ║
║  GASOLINA PREMIUM:                    ║  S/. 305.09       ║
╠═══════════════════════════════════════╬═══════════════════╣
║  TOTAL EN SOLES:                      ║  S/. 9,030.51     ║
╚═══════════════════════════════════════╩═══════════════════╝
```

---

## 8️⃣ CUADRE DE CAJA

```
╔═══════════════════════════════════════════════════════════╗
║              🧮 CUADRE DE CAJA                            ║
╠═══════════════════════════════════════╦═══════════════════╣
║  Total Ventas:                        ║  S/. 9,030.51     ║
║  Total Pagos (Yape+BCP+Visa):         ║  S/. 2,760.71     ║
║  Total Créditos:                      ║  S/. 7,050.42     ║
║  Descuentos:                          ║  S/. 5.00         ║
║  Otros Gastos:                        ║  S/. 0.00         ║
║  Efectivo:                            ║  S/. 1,059.50     ║
╠═══════════════════════════════════════╬═══════════════════╣
║  FALTANTE/SOBRANTE:                   ║  S/. 835.12       ║
║                                       ║  (SOBRANTE) ✅    ║
╚═══════════════════════════════════════╩═══════════════════╝
```

**Cálculo**:
```
Total Justificado = 2,760.71 + 7,050.42 + 0.00 + 1,059.50 = 10,870.63
Total Neto Ventas = 9,030.51 - 5.00 = 9,025.51
Diferencia = 10,870.63 - 9,025.51 = 1,845.12 (SOBRANTE)
```

---

## 9️⃣ BOTONES DE ACCIÓN

```
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║     [💾 GUARDAR CAMBIOS]    [✅ CERRAR TURNO]            ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
```

---

## 🎨 COLORES Y ESTILOS

### Encabezados
- **Naranja** (#ff9800): Títulos de máquinas (ISLA - MÁQUINA 1/2)
- **Verde** (#4caf50): Encabezados de columnas de lecturas
- **Gris** (#6c757d): Encabezados de pagos y créditos

### Filas
- **Amarillo** (#ffc107): Filas de totales
- **Blanco**: Filas de datos normales
- **Amarillo claro** (#ffffcc): Campo activo (al hacer clic)

### Bordes
- **Negro** (#000): Bordes de todas las celdas
- **1px sólido**: Grosor de bordes

---

## 📱 RESPONSIVE

El diseño se adapta a diferentes tamaños de pantalla:

### Desktop (> 1200px)
- Todas las columnas visibles
- Scroll horizontal si es necesario

### Tablet (768px - 1200px)
- Scroll horizontal automático
- Mantiene estructura de tabla

### Mobile (< 768px)
- Scroll horizontal obligatorio
- Zoom recomendado para mejor visualización

---

## 🖱️ INTERACTIVIDAD

### Campos editables
- **Lecturas actuales**: Input numérico con 3 decimales
- **Pagos**: Input numérico con 2 decimales
- **Códigos de operación**: Input de texto
- **Créditos**: Input numérico con 2 decimales
- **Nombres de clientes**: Input de texto con autocompletado
- **Números de vale**: Input de texto

### Cálculos automáticos
- Al escribir en cualquier campo, los totales se actualizan en tiempo real
- No es necesario hacer clic en ningún botón para ver los cálculos
- Los cambios se reflejan inmediatamente

### Validaciones
- Lecturas actuales deben ser >= lecturas anteriores
- Montos deben ser números positivos
- Códigos de operación obligatorios para Yape, BCP y Visa

---

## 💡 TIPS DE USO

1. **Usar TAB** para navegar entre campos rápidamente
2. **Copiar y pegar** desde Excel si es necesario
3. **Guardar frecuentemente** para no perder datos
4. **Verificar el cuadre** antes de cerrar el turno
5. **Revisar los totales** para detectar errores

---

## 📊 COMPARACIÓN CON EXCEL ORIGINAL

| Elemento | Excel Original | Sistema Web |
|----------|----------------|-------------|
| Formato | Celdas Excel | Tabla HTML |
| Entrada | Clic y escribir | Clic y escribir |
| Cálculos | Fórmulas Excel | JavaScript |
| Guardado | Ctrl+S | Botón "Guardar" |
| Colores | Similares | Idénticos |
| Estructura | Igual | Igual |

---

**El sistema replica fielmente el formato Excel que el cliente ya conoce, facilitando la transición y reduciendo la curva de aprendizaje.**

🎉 **¡Listo para usar!**
