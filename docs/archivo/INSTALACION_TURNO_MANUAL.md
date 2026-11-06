# 🚀 INSTALACIÓN - REGISTRO DE TURNO MANUAL

## 📋 REQUISITOS PREVIOS

- Sistema de gestión de grifo funcionando
- Base de datos MySQL configurada
- Acceso al servidor web (Apache/Nginx)
- Acceso a la base de datos (phpMyAdmin o MySQL Workbench)

---

## 📦 PASO 1: VERIFICAR ARCHIVOS

Asegúrate de que los siguientes archivos estén en su ubicación correcta:

```
grifo_grau/
├── view/
│   └── turnos/
│       └── view_registrar_turno_manual.php ✅
├── js/
│   └── console_turnos_manual.js ✅
├── controller/
│   └── turnos/
│       ├── controlador_guardar_turno_manual.php ✅
│       └── controlador_cerrar_turno_manual.php ✅
└── database/
    └── tipos_pago.sql ✅
```

---

## 🗄️ PASO 2: CONFIGURAR BASE DE DATOS

### Opción A: Usando phpMyAdmin

1. Abrir phpMyAdmin en el navegador
2. Seleccionar la base de datos `grifo_grau`
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido de `database/tipos_pago.sql`
5. Hacer clic en "Continuar" o "Ejecutar"

### Opción B: Usando MySQL Workbench

1. Abrir MySQL Workbench
2. Conectar a la base de datos
3. Abrir el archivo `database/tipos_pago.sql`
4. Ejecutar el script (⚡ icono de rayo)

### Opción C: Usando línea de comandos

```bash
# Navegar a la carpeta del proyecto
cd grifo_grau

# Ejecutar el script SQL
mysql -u root -p grifo_grau < database/tipos_pago.sql

# Ingresar la contraseña cuando se solicite
```

### Verificar instalación

Ejecutar esta consulta para verificar:

```sql
SELECT * FROM tipos_pago;
```

Deberías ver 6 registros:
- YAPE (id: 1)
- BCP (id: 2)
- VISA (id: 3)
- EFECTIVO (id: 4)
- DESCUENTO (id: 5)
- OTROS_GASTOS (id: 6)

---

## 🔧 PASO 3: CONFIGURAR CONEXIÓN A BASE DE DATOS

Verificar que los archivos de controlador tengan la configuración correcta:

### Archivo: `controller/turnos/controlador_guardar_turno_manual.php`
### Archivo: `controller/turnos/controlador_cerrar_turno_manual.php`

```php
$host = "localhost";
$port = "3307";  // ⚠️ Verificar puerto (3306 o 3307)
$usuario = "root";
$contrasena = "";  // ⚠️ Agregar contraseña si es necesario
$bdName = "grifo_grau";
```

**IMPORTANTE**: Ajustar estos valores según tu configuración:
- Si usas XAMPP: puerto `3306`
- Si usas WAMP: puerto `3306`
- Si usas Docker: puerto `3307` (como está configurado)

---

## 🎨 PASO 4: INTEGRAR AL MENÚ

### Ubicar el archivo de menú

El menú puede estar en:
- `view/index.php`
- `plantilla/menu.php`
- `view/plantilla/menu.php`

### Agregar la opción

Buscar la sección de **TURNOS** y agregar:

```php
<li class="nav-item">
    <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
        <i class="fas fa-file-alt nav-icon"></i>
        <p>Registro de Turno</p>
    </a>
</li>
```

---

## ✅ PASO 5: PROBAR LA INSTALACIÓN

### 5.1 Verificar acceso a la vista

1. Iniciar sesión en el sistema
2. Buscar la opción "Registro de Turno" en el menú
3. Hacer clic en la opción
4. Verificar que cargue la vista correctamente

### 5.2 Probar funcionalidad básica

**Si NO hay turno abierto:**
- Debería mostrar mensaje: "No hay un turno abierto en el sistema"
- Botón para "Abrir Turno"

**Si HAY turno abierto:**
- Debería mostrar el formulario completo
- Información del turno en la parte superior
- Tablas de lecturas (Máquina 1 y 2)
- Tabla de pagos (15 filas)
- Tabla de créditos (15 filas)
- Resumen y cuadre de caja

### 5.3 Probar cálculos automáticos

1. Ingresar una lectura actual en cualquier producto
2. Verificar que se calculen automáticamente:
   - Galones vendidos
   - Total en soles
   - Total por máquina
   - Total general

3. Ingresar un monto en YAPE, BCP o VISA
4. Verificar que se actualicen los totales de pagos

5. Ingresar un monto de crédito
6. Verificar que se actualice el total de créditos

7. Verificar que el cuadre de caja muestre:
   - Total ventas
   - Total pagos
   - Total créditos
   - Faltante/Sobrante

### 5.4 Probar guardado

1. Hacer clic en "GUARDAR CAMBIOS"
2. Verificar mensaje de éxito
3. Recargar la página
4. Verificar que los datos se mantienen

### 5.5 Probar cierre de turno

1. Completar todos los datos
2. Hacer clic en "CERRAR TURNO"
3. Confirmar la acción
4. Verificar mensaje de éxito
5. Verificar que redirija al historial de turnos

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema 1: No carga la vista

**Síntoma**: Al hacer clic en el menú, no pasa nada o muestra error 404

**Solución**:
- Verificar que el archivo `view/turnos/view_registrar_turno_manual.php` existe
- Verificar la ruta en el menú: `turnos/view_registrar_turno_manual.php`
- Verificar permisos del archivo (debe ser legible)

### Problema 2: No se cargan las lecturas

**Síntoma**: Las tablas de lecturas aparecen vacías

**Solución**:
- Verificar que hay un turno abierto
- Abrir consola del navegador (F12) y buscar errores
- Verificar que el controlador `controlador_obtener_lecturas_turno.php` existe
- Verificar conexión a la base de datos

### Problema 3: No se calculan los totales

**Síntoma**: Al ingresar datos, los totales no se actualizan

**Solución**:
- Verificar que el archivo `js/console_turnos_manual.js` está cargado
- Abrir consola del navegador (F12) y buscar errores de JavaScript
- Verificar que jQuery está cargado
- Limpiar caché del navegador (Ctrl + F5)

### Problema 4: Error al guardar

**Síntoma**: Al hacer clic en "GUARDAR CAMBIOS" muestra error

**Solución**:
- Verificar configuración de conexión a base de datos
- Verificar que la tabla `tipos_pago` existe
- Verificar permisos de escritura en la base de datos
- Revisar logs de error de PHP

### Problema 5: Error al cerrar turno

**Síntoma**: Al hacer clic en "CERRAR TURNO" muestra error

**Solución**:
- Verificar que todos los datos están completos
- Verificar que el controlador `controlador_cerrar_turno_manual.php` existe
- Verificar permisos de actualización en la base de datos
- Revisar logs de error de PHP

---

## 🔍 VERIFICACIÓN DE LOGS

### Logs de PHP

**Windows (XAMPP)**:
```
C:\xampp\apache\logs\error.log
```

**Linux**:
```
/var/log/apache2/error.log
```

### Logs de MySQL

**Windows (XAMPP)**:
```
C:\xampp\mysql\data\mysql_error.log
```

**Linux**:
```
/var/log/mysql/error.log
```

### Consola del navegador

1. Presionar F12
2. Ir a la pestaña "Console"
3. Buscar errores en rojo
4. Copiar el mensaje de error para diagnóstico

---

## 📊 VERIFICACIÓN DE TABLAS

Ejecutar estas consultas para verificar que las tablas existen:

```sql
-- Verificar tabla de reportes
DESCRIBE reportes;

-- Verificar tabla de lecturas
DESCRIBE reporte_lecturas;

-- Verificar tabla de pagos
DESCRIBE reporte_pagos;

-- Verificar tabla de créditos
DESCRIBE creditos;

-- Verificar tabla de tipos de pago
DESCRIBE tipos_pago;

-- Verificar tabla de surtidores
DESCRIBE surtidores;

-- Verificar tabla de clientes
DESCRIBE clientes;
```

---

## 🎓 CAPACITACIÓN DE USUARIOS

### Materiales de capacitación

1. **Guía de uso**: `GUIA_REGISTRO_TURNO_MANUAL.md`
2. **Video tutorial**: Grabar un video mostrando el proceso completo
3. **Manual impreso**: Imprimir la guía para referencia rápida

### Puntos clave a enseñar

1. Cómo abrir un turno
2. Cómo ingresar lecturas
3. Cómo registrar pagos
4. Cómo registrar créditos
5. Cómo interpretar el cuadre de caja
6. Cómo guardar cambios
7. Cómo cerrar el turno

---

## 📞 SOPORTE

Si encuentras problemas durante la instalación:

1. Revisar esta guía completa
2. Verificar los logs de error
3. Consultar la documentación adicional:
   - `GUIA_REGISTRO_TURNO_MANUAL.md`
   - `INTEGRACION_MENU_TURNO_MANUAL.md`
   - `RESUMEN_REGISTRO_TURNO_MANUAL.md`

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] Archivos copiados en ubicaciones correctas
- [ ] Script SQL ejecutado correctamente
- [ ] Tabla `tipos_pago` creada con 6 registros
- [ ] Configuración de base de datos verificada
- [ ] Opción agregada al menú
- [ ] Vista carga correctamente
- [ ] Lecturas se muestran correctamente
- [ ] Cálculos automáticos funcionan
- [ ] Guardado funciona correctamente
- [ ] Cierre de turno funciona correctamente
- [ ] Usuarios capacitados

---

**¡Instalación completada! 🎉**

El sistema de Registro de Turno Manual está listo para usar.
