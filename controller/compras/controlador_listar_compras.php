<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $m = new Modelo_Compras();
    return ['data' => $m->Listar_Compras($desde, $hasta, (string) ($_POST['filtro'] ?? ''))];
});
