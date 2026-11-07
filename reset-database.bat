@echo off
chcp 65001 >nul
echo ========================================
echo   REINICIAR BASE DE DATOS
echo ========================================
echo.
echo ADVERTENCIA: Esto eliminará TODOS los datos de la base de Docker.
echo Se volverá a instalar el esquema con las migraciones y datos de demostración.
echo.
pause

docker compose down -v
docker compose up -d --build

echo.
echo Base de datos reiniciada desde cero.
echo.
pause
