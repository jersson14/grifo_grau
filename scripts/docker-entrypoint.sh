#!/bin/sh
# Prepara el contenedor web: archivos de configuración, espera a la base y aplica las migraciones.
set -e
cd /var/www/html

if [ ! -f model/model_conexion.php ]; then
    cp model/model_conexion.example.php model/model_conexion.php
fi

# Primer arranque: .env con una clave de cifrado nueva. Los datos de conexión llegan como
# variables de entorno desde docker-compose y tienen prioridad sobre .env.
if [ ! -f .env ]; then
    sed "s/^APP_KEY=.*/APP_KEY=$(php -r 'echo bin2hex(random_bytes(32));')/" .env.example > .env
    echo "Se creó .env con una clave de cifrado nueva"
fi

mkdir -p respaldos
chown www-data:www-data respaldos

echo "Esperando a la base de datos ${DB_HOST}:${DB_PORT}..."
intentos=0
until php -r 'new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT"), getenv("DB_USER"), getenv("DB_PASS"));' 2>/dev/null; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 60 ]; then
        echo "La base de datos no respondió en 2 minutos" >&2
        exit 1
    fi
    sleep 2
done

# Con GRIFO_DEMO=1 una base vacía recibe datos de demostración. Con datos, solo migra.
if [ "${GRIFO_DEMO:-0}" = "1" ]; then
    php database/migrate.php --demo
else
    php database/migrate.php
fi

exec docker-php-entrypoint "$@"
