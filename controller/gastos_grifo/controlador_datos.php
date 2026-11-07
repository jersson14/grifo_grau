<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

responder_json(function () {
    $medios = [];
    foreach (Modelo_Gastos_Grifo::MEDIOS as $codigo => $nombre) {
        $medios[] = ["codigo" => $codigo, "nombre" => $nombre];
    }
    return ["categorias" => (new Modelo_Gastos_Grifo())->Categorias(false), "medios" => $medios];
});
