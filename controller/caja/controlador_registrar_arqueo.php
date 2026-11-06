<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    $cantidades = json_decode((string) ($_POST['cantidades'] ?? '{}'), true);
    $m = new Modelo_Caja();
    return $m->Registrar_Arqueo((int) ($_POST['id_reporte'] ?? 0), is_array($cantidades) ? $cantidades : [], trim((string) ($_POST['observaciones'] ?? '')), (int) $_SESSION['S_ID']);
});
