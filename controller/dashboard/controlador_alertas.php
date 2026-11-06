<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_alertas.php';

responder_json(function () {
    if (!puede('avisos')) {
        return ['data' => []];
    }
    $m = new Modelo_Alertas();
    return ['data' => $m->Listar()];
});
