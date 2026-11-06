<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $m = new Modelo_Compras();
    $m->Registrar_Pago((int) ($_POST['id_compra'] ?? 0), (float) ($_POST['monto'] ?? 0), (string) ($_POST['fecha_pago'] ?? ''), trim((string) ($_POST['medio'] ?? '')), trim((string) ($_POST['referencia'] ?? '')), (int) $_SESSION['S_ID']);
});
