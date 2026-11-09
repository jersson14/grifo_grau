<?php
/**
 * Carga el archivo .env de la raíz del proyecto una sola vez.
 * Las variables reales del entorno (Docker, hosting) tienen prioridad.
 */
function env(string $clave, ?string $porDefecto = null): ?string
{
    static $valores = null;

    if ($valores === null) {
        $valores = [];
        $ruta = dirname(__DIR__) . '/.env';
        if (is_readable($ruta)) {
            foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
                $linea = trim($linea);
                if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) {
                    continue;
                }
                [$nombre, $valor] = explode('=', $linea, 2);
                $valores[trim($nombre)] = trim(trim($valor), "\"'");
            }
        }
    }

    $delEntorno = getenv($clave);
    if ($delEntorno !== false) {
        return $delEntorno;
    }
    return $valores[$clave] ?? $porDefecto;
}

/** Si el sistema corre como demostración (MODO_DEMO=1). Ver config/demo.php. */
function modo_demo(): bool
{
    return env('MODO_DEMO', '0') === '1';
}

/** Lo que el modo demostración bloquea, o listas vacías fuera de él. */
function bloqueos_demo(): array
{
    static $bloqueos = null;
    if ($bloqueos === null) {
        $bloqueos = modo_demo() ? require dirname(__DIR__) . '/config/demo.php' : ['modulos' => [], 'rutas' => []];
    }
    return $bloqueos;
}
