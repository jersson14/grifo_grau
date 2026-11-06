<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

set_time_limit(120);

responder_json(function () {
    $m = new Modelo_Comprobantes();
    return $m->Probar_Conexion();
});
