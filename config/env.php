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
