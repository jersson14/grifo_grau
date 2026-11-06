<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

set_time_limit(120);

responder_json(function () {
    $m = new Modelo_Comprobantes();
    $f = $m->Procesar((int) ($_POST['id_comprobante'] ?? 0));
    return ['estado' => $f['estado'], 'ultimo_error' => $f['ultimo_error']];
});
