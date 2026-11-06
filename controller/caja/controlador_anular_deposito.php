<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    $m = new Modelo_Caja();
    $m->Anular_Deposito((int) ($_POST['id_deposito'] ?? 0), trim((string) ($_POST['motivo'] ?? '')), (int) $_SESSION['S_ID']);
});
