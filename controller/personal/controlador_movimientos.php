<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    $m = new Modelo_Personal();
    return ["movimientos" => $m->Movimientos((string) ($_POST["mes"] ?? "")), "faltantes" => $m->Faltantes_Pendientes()];
});
