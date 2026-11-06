<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $m = new Modelo_Compras();
    return ['data' => $m->Listar_Proveedores(!empty($_POST['solo_activos']))];
});
