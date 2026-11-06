<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_ventas.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $m = new Modelo_Ventas();
    return ['data' => $m->Conciliacion($desde, $hasta)];
});
