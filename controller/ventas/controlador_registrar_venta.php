<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_ventas.php';

responder_json(function () {
    $m = new Modelo_Ventas();
    return $m->Registrar_Venta($_POST, (int) $_SESSION['S_ID']);
});
