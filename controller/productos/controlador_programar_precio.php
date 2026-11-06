<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_precios.php';

responder_json(function () {
    $m = new Modelo_Precios();
    $m->Programar((int) ($_POST['id_producto'] ?? 0), (float) ($_POST['precio'] ?? 0), (string) ($_POST['vigente_desde'] ?? ''), (int) $_SESSION['S_ID']);
});
