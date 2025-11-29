# 🚗 Sistema de Gestión de Grifo

Sistema web para gestión de ventas diarias, turnos y reportes de estación de servicio.

## 🛠️ Tecnologías

- **Backend:** PHP 8.0+ (MVC)
- **Frontend:** HTML5, CSS3, JavaScript
- **Template:** AdminLTE 3.2
- **Base de Datos:** MySQL 8.0+
- **PDF:** mPDF
- **Gráficos:** Chart.js
- **Contenedores:** Docker & Docker Compose

## 📁 Estructura del Proyecto

```
GRIFO_GRAU/
├── controller/
├── database/           # Scripts SQL de inicialización
├── img/
├── js/
├── model/
├── PHPMailer-master/
├── plantilla/
├── utilitario/
├── vendor/
├── view/
├── docker-compose.yml  # Configuración de contenedores
├── Dockerfile          # Imagen Docker del proyecto
├── install.bat         # Instalador automático para Windows
├── stop.bat            # Script para detener el sistema
└── README.md
```

## 🚀 Instalación Rápida con Docker (Recomendado)

### Requisitos Previos
- **Docker Desktop** instalado ([Descargar aquí](https://www.docker.com/products/docker-desktop))
- Windows 10/11

### Pasos de Instalación

1. **Clonar o copiar el proyecto** en tu PC
   ```bash
   git clone https://github.com/tu-usuario/GRIFO_GRAU.git
   cd GRIFO_GRAU
   ```

2. **Colocar tu script SQL** en la carpeta `database/`
   - Renombra tu archivo SQL a `init.sql` o colócalo en `database/`
   - Este script se ejecutará automáticamente al iniciar

3. **Ejecutar el instalador**
   - Doble clic en `install.bat`
   - El script verificará Docker y levantará todos los servicios automáticamente

4. **Acceder al sistema**
   - **Aplicación:** http://localhost:8080
   - **phpMyAdmin:** http://localhost:8081

### Comandos Útiles

```bash
# Detener el sistema
docker-compose down

# O usar el script
stop.bat

# Ver logs en tiempo real
docker-compose logs -f

# Reiniciar servicios
docker-compose restart

# Ver estado de contenedores
docker-compose ps
```

## 📦 Instalación en Otra PC

### Opción 1: Con Docker (Más Fácil)
1. Copia toda la carpeta del proyecto a la nueva PC
2. Instala Docker Desktop
3. Ejecuta `install.bat`
4. ¡Listo!

### Opción 2: Manual (Sin Docker)
Sigue las instrucciones de instalación manual más abajo.

## 🔧 Instalación Manual (Sin Docker)

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

# Importar estructura
source database/init.sql
```

### 4. Configurar Conexión
Editar `model/model_conexion.php`:
```php
$host = "localhost";
$port = "3306";
$usuario = "root";
$contrasena = "";
$bdName = "grifo_grau";
```

### 5. Instalar Dependencias
```bash
composer install
```

### 6. Permisos (Linux/Mac)
```bash
chmod -R 755 view/MPDF/
chmod -R 755 img/
chmod -R 755 plantilla/
```

### 7. Configurar Apache
Apuntar el DocumentRoot a la carpeta del proyecto.

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

## 🐳 Configuración Docker

### Servicios Incluidos

1. **Web (PHP + Apache)**
   - Puerto: 8080
   - PHP 8.1 con extensiones necesarias

2. **Base de Datos (MySQL 8.0)**
   - Puerto: 3306
   - Usuario: root
   - Contraseña: rootpassword
   - Base de datos: grifo_grau

3. **phpMyAdmin**
   - Puerto: 8081
   - Gestión visual de la base de datos

### Variables de Entorno

Puedes modificar las credenciales en `docker-compose.yml`:

```yaml
environment:
  MYSQL_ROOT_PASSWORD: rootpassword
  MYSQL_DATABASE: grifo_grau
  MYSQL_USER: grifo_user
  MYSQL_PASSWORD: grifo_pass
```

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

## 🐛 Solución de Problemas

### Docker no inicia
- Verifica que Docker Desktop esté corriendo
- Reinicia Docker Desktop
- Ejecuta `docker-compose down` y luego `install.bat` nuevamente

### Puerto 8080 ocupado
Cambia el puerto en `docker-compose.yml`:
```yaml
ports:
  - "8090:80"  # Cambia 8080 por otro puerto
```

### Error de conexión a base de datos
- Espera 30 segundos después de ejecutar `install.bat`
- Verifica que el contenedor de MySQL esté corriendo: `docker-compose ps`

## 📞 Soporte

Desarrollado por: **ING. JERSSON JORGE CORILLA MIRANDA**  
Email: jersson14071996@gmail.com  

## 📝 Licencia

MIT License

---

**Versión:** 2.0.0  
**Fecha:** Enero 2025  
**Dockerizado:** ✅
