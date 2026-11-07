<?php
/**
 * Genera una copia de seguridad. Para una tarea programada del hosting (cron), por ejemplo cada noche.
 * También se hace desde Configuración → Respaldos.
 *
 *   php scripts/respaldo.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/model/model_respaldos.php';

$m = new Modelo_Respaldos();
$r = $m->Generar(null);
echo $r['archivo'] . ' (' . round($r['bytes'] / 1024, 1) . " KB)\n";
