<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_roles.php';

responder_json(function () {
    require_once '../../model/model_menu.php';
    $m = new Modelo_Roles();
    $menu = new Modelo_Menu();
    $catalogo = [];
    foreach (Modelo_Roles::Catalogo() as $codigo => [$grupo, $nombre]) {
        $catalogo[$codigo] = ['grupo' => $grupo, 'nombre' => $nombre];
    }
    return ['roles' => $m->Listar(), 'catalogo' => $catalogo, 'vistas' => $menu->Vistas()];
});
