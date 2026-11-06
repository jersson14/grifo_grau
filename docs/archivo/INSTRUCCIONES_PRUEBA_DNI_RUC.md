# INSTRUCCIONES PARA PROBAR LA BÚSQUEDA DNI/RUC

## ✅ Archivos Configurados

Los siguientes archivos ya están configurados y listos:

1. **view/consulta-dni-ajax.php** - API de consulta DNI (RENIEC)
2. **view/consultar-ruc-ajax.php** - API de consulta RUC (SUNAT)
3. **js/console_clientes_grifo.js** - Funciones de búsqueda para clientes
4. **js/console_usuario.js** - Funciones de búsqueda para usuarios
5. **view/clientes/view_clientes.php** - Botones de búsqueda agregados
6. **view/usuario/view_usuario.php** - Botones de búsqueda agregados

## 🧪 Cómo Probar

### Opción 1: Probar en el Módulo de Clientes

1. Abre tu navegador y ve a: `http://localhost/grifo_grau/view/index.php`
2. Inicia sesión en el sistema
3. Ve al módulo de **Clientes**
4. Haz clic en **"Nuevo Cliente"**
5. En el campo **DNI/RUC**, ingresa:
   - Para DNI: 8 dígitos (ejemplo: `12345678`)
   - Para RUC: 11 dígitos (ejemplo: `20123456789`)
6. Haz clic en el botón de **búsqueda** (ícono de lupa) al lado del campo
7. Abre la **Consola del Navegador** (F12) para ver los logs
8. Deberías ver:
   - Un mensaje "Buscando..." mientras consulta
   - Los datos autocompletados si se encuentran
   - Un mensaje de éxito o error

### Opción 2: Probar en el Módulo de Usuarios

1. Ve al módulo de **Usuarios**
2. Haz clic en **"Nuevo Usuario"**
3. En el campo **DNI**, ingresa 8 dígitos
4. Haz clic en el botón de **búsqueda**
5. Los campos de **Nombres** y **Apellidos** se autocompletarán

### Opción 3: Prueba Directa de la API

Abre en tu navegador: `http://localhost/grifo_grau/test-api-simple.html`

Este archivo te permite probar directamente las APIs sin necesidad de entrar al sistema.

## 🔍 Qué Verificar en la Consola del Navegador

Presiona **F12** para abrir las herramientas de desarrollo y ve a la pestaña **Console**.

Deberías ver mensajes como:

```
Respuesta DNI: {"success":true,"data":{"nombres":"JUAN","apellido_paterno":"PEREZ",...}}
Data parseada: {success: true, data: {...}}
```

Si hay un error, verás:
```
Error AJAX: error Not Found
```

## ❌ Solución de Problemas

### Error: "Not Found" o 404

**Causa**: La ruta del archivo no es correcta.

**Solución**: Verifica que los archivos existan en:
- `C:\xampp\htdocs\grifo_grau\view\consulta-dni-ajax.php`
- `C:\xampp\htdocs\grifo_grau\view\consultar-ruc-ajax.php`

Puedes verificar ejecutando en PowerShell:
```powershell
dir C:\xampp\htdocs\grifo_grau\view\consulta*.php
```

### Error: "Error al procesar la respuesta"

**Causa**: La API devolvió un formato inesperado.

**Solución**: 
1. Abre la consola del navegador (F12)
2. Busca el mensaje "Respuesta DNI:" o "Respuesta recibida:"
3. Verifica qué está devolviendo la API
4. Puede ser que el token haya expirado o el DNI/RUC no exista

### No aparece el botón de búsqueda

**Causa**: El navegador tiene caché del archivo antiguo.

**Solución**:
1. Presiona **Ctrl + F5** para recargar sin caché
2. O limpia la caché del navegador

### El botón no hace nada

**Causa**: Error de JavaScript.

**Solución**:
1. Abre la consola del navegador (F12)
2. Ve a la pestaña **Console**
3. Busca errores en rojo
4. Verifica que jQuery y SweetAlert2 estén cargados

## 📝 Datos de Prueba

### DNI de Prueba
Puedes usar cualquier DNI de 8 dígitos. La API buscará en RENIEC.

### RUC de Prueba
Puedes usar cualquier RUC de 11 dígitos. La API buscará en SUNAT.

**Nota**: Si el DNI o RUC no existe en las bases de datos oficiales, recibirás un mensaje de "No encontrado".

## 🔧 Configuración del Token

El token de la API está configurado en:
- `view/consulta-dni-ajax.php` (línea 4)
- `view/consultar-ruc-ajax.php` (línea 4)

Token actual: `[TOKEN RETIRADO]`

Si el token expira, deberás actualizarlo en ambos archivos.

## ✨ Funcionalidades Implementadas

### En Clientes:
- ✅ Búsqueda DNI (8 dígitos) → Autocompleta nombre completo
- ✅ Búsqueda RUC (11 dígitos) → Autocompleta razón social y dirección
- ✅ Funciona en modal de registro
- ✅ Funciona en modal de edición

### En Usuarios:
- ✅ Búsqueda DNI (8 dígitos) → Autocompleta nombres y apellidos
- ✅ Funciona en modal de registro
- ✅ Funciona en modal de edición

## 📞 Soporte

Si después de seguir estas instrucciones aún tienes problemas:

1. Verifica que Apache y PHP estén corriendo en XAMPP
2. Verifica que tengas conexión a Internet (las APIs son externas)
3. Revisa los logs de la consola del navegador
4. Verifica que los archivos PHP no tengan errores de sintaxis
