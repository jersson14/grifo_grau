<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    $m = new Modelo_Caja();
    $m->Registrar_Deposito((string) ($_POST['fecha'] ?? ''), trim((string) ($_POST['banco'] ?? '')), trim((string) ($_POST['numero_operacion'] ?? '')), (float) ($_POST['monto'] ?? 0), trim((string) ($_POST['observaciones'] ?? '')), (int) $_SESSION['S_ID']);
});
