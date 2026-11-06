<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $m = new Modelo_Inventario();
    return ['data' => $m->Listar_Varillajes($desde, $hasta)];
});
