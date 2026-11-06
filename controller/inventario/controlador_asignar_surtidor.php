<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    $m = new Modelo_Inventario();
    $m->Asignar_Surtidor((int) ($_POST['id_surtidor'] ?? 0), (int) ($_POST['id_tanque'] ?? 0));
});
