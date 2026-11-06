<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    [$d, $h] = rango_fechas();
    $m = new Modelo_Caja();
    return ['data' => $m->Listar_Turnos($d, $h)];
});
