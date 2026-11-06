<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_configuracion.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    $medida = ($_POST['medida_cm'] ?? '') === '' ? null : (float) $_POST['medida_cm'];
    $m = new Modelo_Inventario();
    return $m->Registrar_Varillaje((int) ($_POST['id_tanque'] ?? 0), (string) ($_POST['fecha'] ?? ''), $medida, (float) ($_POST['galones_fisicos'] ?? -1), (int) $_SESSION['S_ID'], trim((string) ($_POST['observaciones'] ?? '')));
});
