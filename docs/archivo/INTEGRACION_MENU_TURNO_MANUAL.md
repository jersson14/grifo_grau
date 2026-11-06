# INTEGRACIÓN AL MENÚ - REGISTRO DE TURNO MANUAL

## 📋 INSTRUCCIONES

Para agregar la nueva vista de "Registro de Turno Manual" al menú del sistema, sigue estos pasos:

## 1️⃣ UBICAR EL ARCHIVO DE MENÚ

El menú del sistema generalmente se encuentra en uno de estos archivos:
- `view/index.php`
- `plantilla/menu.php`
- `view/plantilla/menu.php`

## 2️⃣ AGREGAR LA OPCIÓN AL MENÚ

Busca la sección de **TURNOS** en el menú y agrega la siguiente opción:

```php
<!-- MENÚ DE TURNOS -->
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-clock"></i>
        <p>
            Turnos
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <!-- Opción existente: Abrir Turno -->
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')">
                <i class="fas fa-plus-circle nav-icon"></i>
                <p>Abrir Turno</p>
            </a>
        </li>
        
        <!-- NUEVA OPCIÓN: Registro de Turno Manual -->
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
                <i class="fas fa-file-alt nav-icon"></i>
                <p>Registro de Turno</p>
            </a>
        </li>
        
        <!-- Opción existente: Cerrar Turno (si existe) -->
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_cerrar_turno.php')">
                <i class="fas fa-tasks nav-icon"></i>
                <p>Gestionar Turno</p>
            </a>
        </li>
        
        <!-- Opción existente: Historial -->
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_historial.php')">
                <i class="fas fa-history nav-icon"></i>
                <p>Historial de Turnos</p>
            </a>
        </li>
    </ul>
</li>
```

## 3️⃣ ALTERNATIVA: REEMPLAZAR LA VISTA EXISTENTE

Si prefieres **reemplazar** la vista de "Gestionar Turno" existente con la nueva vista manual, simplemente cambia la ruta:

```php
<!-- ANTES -->
<li class="nav-item">
    <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_cerrar_turno.php')">
        <i class="fas fa-tasks nav-icon"></i>
        <p>Gestionar Turno</p>
    </a>
</li>

<!-- DESPUÉS -->
<li class="nav-item">
    <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
        <i class="fas fa-file-alt nav-icon"></i>
        <p>Gestionar Turno</p>
    </a>
</li>
```

## 4️⃣ PERMISOS POR ROL (OPCIONAL)

Si el sistema maneja permisos por rol, asegúrate de que los roles apropiados tengan acceso:

```php
<?php if ($_SESSION['rol'] == 'ADMINISTRADOR' || $_SESSION['rol'] == 'GRIFERO'): ?>
    <li class="nav-item">
        <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
            <i class="fas fa-file-alt nav-icon"></i>
            <p>Registro de Turno</p>
        </a>
    </li>
<?php endif; ?>
```

## 5️⃣ VERIFICAR LA INTEGRACIÓN

1. Guardar los cambios en el archivo de menú
2. Recargar la página del sistema (F5)
3. Verificar que aparezca la nueva opción en el menú
4. Hacer clic en "Registro de Turno"
5. Verificar que cargue correctamente la vista

## 🎯 RECOMENDACIONES

### Opción 1: Mantener ambas vistas
- **Vista Antigua** (`view_cerrar_turno.php`): Para usuarios técnicos o administradores
- **Vista Nueva** (`view_registrar_turno_manual.php`): Para griferos o usuarios no técnicos

### Opción 2: Usar solo la vista nueva
- Reemplazar completamente la vista antigua
- Más simple y familiar para el cliente
- Formato Excel que ya conocen

## 📝 EJEMPLO COMPLETO DE MENÚ

```php
<!-- SECCIÓN DE TURNOS -->
<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-clock"></i>
        <p>
            Gestión de Turnos
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <?php if ($_SESSION['rol'] == 'ADMINISTRADOR'): ?>
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')">
                <i class="fas fa-plus-circle nav-icon"></i>
                <p>Abrir Turno</p>
            </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno_manual.php')">
                <i class="fas fa-file-alt nav-icon"></i>
                <p>Registro de Turno</p>
            </a>
        </li>
        
        <?php if ($_SESSION['rol'] == 'ADMINISTRADOR'): ?>
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_historial.php')">
                <i class="fas fa-history nav-icon"></i>
                <p>Historial</p>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="cargar_contenido('contenido_principal','turnos/view_validar_reportes.php')">
                <i class="fas fa-check-circle nav-icon"></i>
                <p>Validar Reportes</p>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
```

## ✅ VERIFICACIÓN FINAL

Después de integrar al menú, verifica:

- [ ] La opción aparece en el menú
- [ ] Al hacer clic, carga la vista correctamente
- [ ] Los estilos CSS se aplican correctamente
- [ ] Los cálculos automáticos funcionan
- [ ] Se pueden guardar los datos
- [ ] Se puede cerrar el turno

---

**Nota**: Si encuentras algún problema, revisa la consola del navegador (F12) para ver errores de JavaScript o rutas incorrectas.
