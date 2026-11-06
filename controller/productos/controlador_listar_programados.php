<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_precios.php';

responder_json(function () {
    $m = new Modelo_Precios();
    return ['data' => $m->Listar_Programados()];
});
