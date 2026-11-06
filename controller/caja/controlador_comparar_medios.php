<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    $m = new Modelo_Caja();
    return ['data' => $m->Comparar_Medios((int) ($_POST['id_reporte'] ?? 0))];
});
