<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $m = new Modelo_Compras();
    $m->Guardar_Proveedor((int) ($_POST['id_proveedor'] ?? 0), trim((string) ($_POST['ruc'] ?? '')), trim((string) ($_POST['razon_social'] ?? '')), trim((string) ($_POST['telefono'] ?? '')), trim((string) ($_POST['direccion'] ?? '')));
});
