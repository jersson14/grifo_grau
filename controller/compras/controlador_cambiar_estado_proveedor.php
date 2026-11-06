<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_compras.php';

responder_json(function () {
    $m = new Modelo_Compras();
    $m->Cambiar_Estado_Proveedor((int) ($_POST['id_proveedor'] ?? 0), (string) ($_POST['estado'] ?? ''));
});
