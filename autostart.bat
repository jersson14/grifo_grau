@echo off
REM Esperar a que Docker Desktop inicie
timeout /t 30 /nobreak >nul

REM Iniciar contenedores
docker-compose up -d
