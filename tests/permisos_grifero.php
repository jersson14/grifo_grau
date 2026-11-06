<?php
/**
 * Análisis estático: qué rutas llaman las pantallas del grifero que config/permisos.php le niega.
 * No es una prueba que pase o falle: lista los casos para revisarlos.
 *
 *   php tests/permisos_grifero.php
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$raiz = dirname(__DIR__);
chdir($raiz);
$permisos = require 'config/permisos.php';

/** Rutas del rol GRIFERO con sus capacidades por defecto. */
function rutas_grifero(array $permisos): array
{
    $catalogo = require 'config/capacidades.php';
    $rutas = array_merge($permisos['base'], $permisos['propios'], $permisos['publico']);
    foreach (['turnos_registrar', 'dashboard_ver', 'clientes_gestionar', 'creditos_cobrar', 'ventas_registrar', 'varillaje_registrar'] as $capacidad) {
        $rutas = array_merge($rutas, $catalogo[$capacidad][2]);
    }
    return array_unique($rutas);
}

function permitido(string $ruta, array $permisos): bool
{
    foreach (rutas_grifero($permisos) as $patron) {
        if ($patron === $ruta || (substr($patron, -1) === '*' && strpos($ruta, substr($patron, 0, -1)) === 0)) {
            return true;
        }
    }
    return false;
}

$vistas = array_filter(rutas_grifero($permisos), fn($p) => strpos($p, 'view/') === 0 && substr($p, -4) === '.php');

foreach ($vistas as $vista) {
    if (!is_file($vista)) {
        continue;
    }
    $contenido = file_get_contents($vista);
    $fuentes = [$vista => $contenido];
    if (preg_match_all('#\.\./js/([\w.-]+\.js)#', $contenido, $m)) {
        foreach (array_unique($m[1]) as $js) {
            $fuentes["js/$js"] = (string) @file_get_contents("js/$js");
        }
    }

    $negados = [];
    foreach ($fuentes as $origen => $texto) {
        if (preg_match_all('#controller/[\w/-]+\.php#', $texto, $m)) {
            foreach (array_unique($m[0]) as $ruta) {
                if (!permitido($ruta, $permisos)) {
                    $negados[$ruta] = is_file($ruta) ? 'existe' : 'NO EXISTE';
                }
            }
        }
        if (preg_match_all("#cargar_contenido\(\s*'contenido_principal'\s*,\s*'([\w/.-]+)'#", $texto, $m)) {
            foreach (array_unique($m[1]) as $v) {
                $ruta = "view/$v";
                if (!permitido($ruta, $permisos)) {
                    $negados[$ruta] = is_file($ruta) ? 'vista' : 'VISTA NO EXISTE';
                }
            }
        }
    }

    if ($negados) {
        echo "\n$vista\n";
        ksort($negados);
        foreach ($negados as $ruta => $estado) {
            echo "  $ruta ($estado)\n";
        }
    }
}
