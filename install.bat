@echo off
chcp 65001 >nul
echo ========================================
echo   INSTALADOR SISTEMA GRIFO GRAU
echo ========================================
echo.

REM Verificar si Docker está instalado
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker no está instalado.
    echo.
    echo Por favor instala Docker Desktop desde:
    echo https://www.docker.com/products/docker-desktop
    echo.
    pause
    exit /b 1
)

echo [OK] Docker detectado
echo.

REM Verificar si Docker está corriendo
docker ps >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker no está corriendo.
    echo.
    echo Por favor inicia Docker Desktop y vuelve a ejecutar este script.
    echo.
    pause
    exit /b 1
)

echo [OK] Docker está corriendo
echo.

REM Detener contenedores existentes (los datos se conservan)
echo Deteniendo contenedores existentes...
docker compose down >nul 2>&1

REM Construir e iniciar. El contenedor web espera a la base y aplica las migraciones;
REM una base vacía recibe datos de demostración.
echo.
echo Construyendo e iniciando contenedores...
echo Esto puede tardar varios minutos la primera vez...
echo.
docker compose up -d --build

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Hubo un problema al iniciar los contenedores.
    echo Si un puerto está ocupado, por ejemplo el 8080:  set GRIFO_PUERTO_WEB=8090  y vuelva a ejecutar.
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo   INSTALACIÓN COMPLETADA
echo ========================================
echo.
echo El sistema (PHP 8.4 y MySQL 8.4) está en:
echo.
echo   - Aplicación Web:  http://localhost:8080
echo   - phpMyAdmin:      http://localhost:8081  (solo desde este equipo)
echo.
echo Base de datos:
echo   - Base: grifo_grau   Usuario: grifo_user   Contraseña: grifo_pass
echo.
echo Usuarios del sistema (datos de demostración):
echo   - Admin:    admin / admin123
echo   - Grifero:  grifero1 / grifero123
echo.
echo Para detener el sistema ejecute: stop.bat
echo Para ver el avance de la instalación: docker compose logs -f web
echo.
pause
