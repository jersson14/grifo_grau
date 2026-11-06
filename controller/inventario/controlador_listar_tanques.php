<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inventario.php';

responder_json(function () {
    $m = new Modelo_Inventario();
    return ['data' => $m->Listar_Tanques(!empty($_POST['solo_activos']) || !puede('inventario_gestionar'))];
});
