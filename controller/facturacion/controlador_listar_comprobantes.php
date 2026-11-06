<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

set_time_limit(120);

responder_json(function () {
    [$d, $h] = rango_fechas();
    $m = new Modelo_Comprobantes();
    return ['data' => $m->Listar($d, $h, (string) ($_POST['estado'] ?? ''))];
});
