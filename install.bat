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

REM Detener contenedores existentes
echo Deteniendo contenedores existentes...
docker-compose down >nul 2>&1

REM Construir e iniciar contenedores
echo.
echo Construyendo e iniciando contenedores...
echo Esto puede tardar varios minutos la primera vez...
echo.
docker-compose up -d --build

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Hubo un problema al iniciar los contenedores.
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo   INSTALACIÓN COMPLETADA
echo ========================================
echo.
echo El sistema está corriendo en:
echo.
echo   - Aplicación Web:  http://localhost:8080
echo   - phpMyAdmin:      http://localhost:8081
echo.
echo Credenciales de Base de Datos:
echo   - Usuario: root
echo   - Contraseña: rootpassword
echo   - Base de datos: grifo_grau
echo.
echo Usuarios del Sistema:
echo   - Admin:    admin / admin123
echo   - Grifero:  grifero1 / grifero123
echo.
echo Para detener el sistema ejecuta: docker-compose down
echo Para ver logs ejecuta: docker-compose logs -f
echo.
pause
