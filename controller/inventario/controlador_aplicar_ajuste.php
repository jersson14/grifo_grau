<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    $m = new Modelo_Inventario();
    $m->Aplicar_Ajuste((int) ($_POST['id_varillaje'] ?? 0), (int) $_SESSION['S_ID']);
});
