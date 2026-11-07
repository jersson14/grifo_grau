<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    [$desde, $hasta] = rango_fechas();
    $m = new Modelo_Tienda();
    // Sin la capacidad de gestionar la tienda, cada vendedor ve solo sus ventas.
    $solo = puede("tienda_gestionar") ? null : (int) $_SESSION["S_ID"];
    return ["ventas" => $m->Ventas($desde, $hasta, $solo), "resumen" => $m->Resumen($desde, $hasta, $solo)];
});
