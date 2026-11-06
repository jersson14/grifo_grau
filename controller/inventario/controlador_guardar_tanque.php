<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    $m = new Modelo_Inventario();
    $m->Guardar_Tanque((int) ($_POST['id_tanque'] ?? 0), trim((string) ($_POST['nombre'] ?? '')), (int) ($_POST['id_producto'] ?? 0), (float) ($_POST['capacidad'] ?? 0), (float) ($_POST['stock_minimo'] ?? 0), (float) ($_POST['stock_inicial'] ?? 0), (int) $_SESSION['S_ID']);
});
