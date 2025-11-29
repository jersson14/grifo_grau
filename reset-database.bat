@echo off
chcp 65001 >nul
echo ========================================
echo   REINICIAR BASE DE DATOS
echo ========================================
echo.
echo ADVERTENCIA: Esto eliminará TODOS los datos
echo y volverá a ejecutar init.sql
echo.
pause

docker-compose down -v
docker-compose up -d --build

echo.
echo Base de datos reiniciada desde cero.
echo.
pause
