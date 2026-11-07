<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $m = new Modelo_Gastos_Grifo();
    $categoria = (int) ($_POST["id_categoria"] ?? 0);
    return [
        "gastos"  => $m->Listar($desde, $hasta, $categoria ?: null, ($_POST["anulados"] ?? "0") === "1"),
        "resumen" => $m->Resumen($desde, $hasta),
    ];
});
