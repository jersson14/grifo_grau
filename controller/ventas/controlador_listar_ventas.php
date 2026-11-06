<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_ventas.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $soloPropias = puede('ver_todos') ? null : (int) $_SESSION['S_ID'];
    $m = new Modelo_Ventas();
    return ['data' => $m->Listar_Ventas($desde, $hasta, $soloPropias)];
});
