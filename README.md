# 🚗 Sistema de Gestión de Grifo

Sistema web para gestión de ventas diarias, turnos y reportes de estación de servicio.

## 🛠️ Tecnologías

- **Backend:** PHP 8.0+ (MVC)
- **Frontend:** HTML5, CSS3, JavaScript
- **Template:** AdminLTE 3.2
- **Base de Datos:** MySQL 8.0+
- **PDF:** mPDF
- **Gráficos:** Chart.js

## 📁 Estructura del Proyecto

```
GRIFO_GRAU/
├── controller/
│   ├── clientes/
│   ├── gastos/
│   ├── indicadores/
│   ├── ingresos/
│   ├── pagos/
│   └── usuario/
├── img/
├── js/
├── model/
├── PHPMailer-master/
├── plantilla/
├── utilitario/
├── vendor/
├── view/
│   ├── clientes/
│   ├── gastos/
│   ├── indicadores/
│   ├── ingresos/
│   ├── MPDF/
│   ├── reportes/
│   │   ├── view_expediente_fecha_distritos.php
│   │   ├── view_expedientes_fecha_provincia.php
│   │   └── view_fechas_estado.php
│   ├── usuario/
│   ├── consulta-dni-ajax.php
│   └── index.php
├── .gitignore
├── composer.json
├── composer.lock
├── consulta-dni-ajax.php
├── index.php
└── README.md
```

## 🚀 Instalación

### 1. Requisitos Previos
- PHP >= 8.0
- MySQL >= 8.0
- Apache/Nginx
- Composer (opcional)

### 2. Clonar Repositorio
```bash
git clone https://github.com/tu-usuario/GRIFO_GRAU.git
cd GRIFO_GRAU
```

### 3. Configurar Base de Datos
```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE grifo_grau;
USE grifo_grau;

# Importar estructura (coloca los archivos SQL en una carpeta database/)
source database/schema.sql
source database/triggers.sql
source database/procedures.sql
```

### 4. Configurar Conexión
Editar `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'grifo_grau');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Instalar mPDF
```bash
composer require mpdf/mpdf
```

O si ya tienes la carpeta `view/MPDF/`, verifica que esté completa.

### 6. Configurar Plantilla AdminLTE
Verifica que la carpeta `plantilla/` contenga AdminLTE 3.2 completo.

### 7. Permisos
```bash
chmod -R 755 view/MPDF/
chmod -R 755 img/
chmod -R 755 plantilla/
```

## 👤 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| admin | admin123 | Administrador |
| grifero1 | grifero123 | Grifero |

## 📋 Funcionalidades

### Administrador
- Abrir y cerrar turnos
- Asignar griferos a turnos
- Validar reportes
- Gestionar créditos
- Configuración general

### Grifero
- Registrar lecturas
- Registrar pagos
- Registrar créditos
- Ver su turno asignado

## 🔧 Stored Procedures Principales

```sql
CALL sp_abrir_turno(fecha, turno_id, usuario_id, hora_inicio, @reporte_id);
CALL sp_cerrar_turno(reporte_id, hora_fin, observaciones);
CALL sp_validar_turno(reporte_id, admin_id);
```

## 📄 Generación de PDF

Archivo: `view/MPDF/reporte_turno.php`
```php
require_once __DIR__ . '/../../vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('reporte.pdf', 'D');
```

## 🎨 Personalización AdminLTE

Editar: `plantilla/css/custom.css`

## 📞 Soporte

Desarrollado por: [ING. JERSSON JORGE CORILLA MIRANDA]  
Email: jersson14071996@gmail.com  

## 📝 Licencia

MIT License

---

**Versión:** 1.0.0  
**Fecha:** Octubre 2025