<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_auditoria.php';

responder_json(function () {
    [$d, $h] = rango_fechas();
    $m = new Modelo_Auditoria();
    return ['data' => $m->Listar($d, $h, (string) ($_POST['usuario'] ?? ''), trim((string) ($_POST['texto'] ?? ''))), 'usuarios' => $m->Usuarios()];
});
