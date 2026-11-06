<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $lineas = json_decode((string) ($_POST['lineas'] ?? '[]'), true);
    $m = new Modelo_Compras();
    return $m->Registrar_Compra($_POST, is_array($lineas) ? $lineas : [], (int) $_SESSION['S_ID']);
});
