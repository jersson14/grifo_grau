@echo off
chcp 65001 >nul
echo Deteniendo el sistema Grifo (los datos se conservan)...
docker compose down
echo.
echo Sistema detenido. Para iniciarlo de nuevo ejecute install.bat
pause
