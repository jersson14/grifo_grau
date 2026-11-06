<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $m = new Modelo_Compras();
    return $m->Detalle_Compra((int) ($_POST['id_compra'] ?? 0));
});
