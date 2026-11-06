<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_menu.php';

responder_json(function () {
    require_once '../../model/model_roles.php';
    $m = new Modelo_Menu();
    return ['data' => $m->Listar_Para_Editor(), 'roles' => Modelo_Roles::Codigos()];
});
