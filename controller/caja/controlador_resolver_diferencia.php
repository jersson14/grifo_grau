<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_caja.php';

responder_json(function () {
    $m = new Modelo_Caja();
    $m->Resolver_Diferencia((int) ($_POST['id_arqueo'] ?? 0), (string) ($_POST['resolucion'] ?? ''), trim((string) ($_POST['nota'] ?? '')), (int) $_SESSION['S_ID']);
});
