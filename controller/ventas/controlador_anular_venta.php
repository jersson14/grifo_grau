<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_ventas.php';

responder_json(function () {
    $m = new Modelo_Ventas();
    $m->Anular_Venta((int) ($_POST['id_venta'] ?? 0), trim((string) ($_POST['motivo'] ?? '')));
});
