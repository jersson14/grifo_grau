<?php
/**
 * Envía los comprobantes pendientes y actualiza los que SUNAT aún no confirma.
 * Opcional, para una tarea programada del hosting (cron). También se hace desde la pantalla Comprobantes.
 *
 *   php scripts/procesar_comprobantes.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/model/model_comprobantes.php';

if (!Modelo_Configuracion::Modulo_Activo('facturacion')) {
    exit("El módulo de facturación está desactivado.\n");
}
$m = new Modelo_Comprobantes();
echo json_encode($m->Procesar_Cola(50)) . "\n";
