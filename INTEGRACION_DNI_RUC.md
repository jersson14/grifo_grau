# INTEGRACIÓN DE CONSULTA DNI/RUC EN EL SISTEMA

## Resumen
Se ha integrado la funcionalidad de consulta automática de DNI (RENIEC) y RUC (SUNAT) en los módulos que requieren datos de personas o empresas.

## Archivos Base Utilizados
- **view/consulta-dni-ajax.php**: Consulta datos de RENIEC por DNI (8 dígitos)
- **view/consultar-ruc-ajax.php**: Consulta datos de SUNAT por RUC (11 dígitos)

Ambos archivos utilizan la API de Decolecta con el token configurado.

**IMPORTANTE**: Los archivos están ubicados en la carpeta `view/` para que sean accesibles desde los módulos con la ruta relativa `../`

## Módulos Integrados

### 1. MÓDULO DE CLIENTES
**Archivos modificados:**
- `view/clientes/view_clientes.php`
- `js/console_clientes_grifo.js`

**Funcionalidad agregada:**
- Botón de búsqueda junto al campo DNI/RUC en modal de registro
- Botón de búsqueda junto al campo DNI/RUC en modal de edición
- Detección automática: 8 dígitos = DNI, 11 dígitos = RUC
- Autocompletado de nombre completo (DNI) o razón social (RUC)
- Autocompletado de dirección (solo para RUC)

**Funciones JavaScript agregadas:**
- `Buscar_DNI_RUC()`: Búsqueda en modal de registro
- `Buscar_DNI()`: Consulta específica a RENIEC
- `Buscar_RUC()`: Consulta específica a SUNAT
- `Buscar_DNI_RUC_Editar()`: Búsqueda en modal de edición
- `Buscar_DNI_Editar()`: Consulta RENIEC en edición
- `Buscar_RUC_Editar()`: Consulta SUNAT en edición

### 2. MÓDULO DE USUARIOS
**Archivos modificados:**
- `view/usuario/view_usuario.php`
- `js/console_usuario.js`

**Funcionalidad agregada:**
- Botón de búsqueda junto al campo DNI en modal de registro
- Botón de búsqueda junto al campo DNI en modal de edición
- Autocompletado de nombres y apellidos desde RENIEC

**Funciones JavaScript agregadas:**
- `Buscar_DNI_Usuario()`: Búsqueda en modal de registro
- `Buscar_DNI_Usuario_Editar()`: Búsqueda en modal de edición

## Características de la Integración

### Validaciones Implementadas
- Verificación de longitud del documento (8 para DNI, 11 para RUC)
- Validación de campos vacíos
- Manejo de errores de conexión
- Mensajes informativos con SweetAlert2

### Experiencia de Usuario
- Indicador de carga mientras se consulta la API
- Mensajes de éxito cuando se encuentran datos
- Mensajes de error cuando no se encuentran datos o hay problemas
- Autocompletado inmediato de campos relacionados

### Datos Autocompletados

**Para DNI (RENIEC):**
- Nombres completos (nombres + apellido paterno + apellido materno)

**Para RUC (SUNAT):**
- Razón social
- Dirección completa

## Módulos que NO Requieren Integración

Los siguientes módulos no necesitan búsqueda DNI/RUC porque:

- **Gastos**: Maneja indicadores y montos, no datos de personas
- **Ingresos**: Maneja transacciones financieras, no datos de personas
- **Créditos**: Trabaja con clientes ya registrados
- **Productos**: Maneja inventario de combustibles
- **Surtidores**: Maneja equipos del grifo
- **Turnos**: Maneja turnos de trabajo
- **Reportes**: Solo visualiza información

## Cómo Usar la Funcionalidad

### En Clientes:
1. Abrir modal de registro o edición de cliente
2. Ingresar DNI (8 dígitos) o RUC (11 dígitos)
3. Hacer clic en el botón de búsqueda (ícono de lupa)
4. El sistema consultará automáticamente RENIEC o SUNAT
5. Los datos se autocompletarán en los campos correspondientes

### En Usuarios:
1. Abrir modal de registro o edición de usuario
2. Ingresar DNI (8 dígitos)
3. Hacer clic en el botón de búsqueda (ícono de lupa)
4. El sistema consultará RENIEC
5. Nombres y apellidos se autocompletarán

## Notas Técnicas

- Las consultas se realizan mediante AJAX
- Se utiliza SweetAlert2 para mensajes
- El token de API está configurado en los archivos PHP
- Las respuestas se procesan en formato JSON
- Se manejan errores de conexión y respuestas vacías
- **IMPORTANTE**: Las rutas de los archivos PHP son relativas a la ubicación de las vistas:
  - Desde `view/clientes/`: usar `../consulta-dni-ajax.php` y `../consultar-ruc-ajax.php`
  - Desde `view/usuario/`: usar `../consulta-dni-ajax.php`
  - Los archivos están en `view/` (un nivel arriba de los módulos)
- Se incluyen console.log para depuración en desarrollo
- Token API: `sk_11678.HdeHGplwfvrLVqBOrFwH2fspxdwFoTOT`

## Próximas Mejoras Sugeridas

1. Agregar caché local para evitar consultas repetidas
2. Implementar límite de consultas por día
3. Agregar log de consultas realizadas
4. Considerar agregar más proveedores de API como respaldo
