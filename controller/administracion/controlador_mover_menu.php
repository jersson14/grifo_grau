<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_menu.php';

responder_json(function () {
    $m = new Modelo_Menu();
    $m->Mover((int) ($_POST['id_item'] ?? 0), (string) ($_POST['direccion'] ?? ''));
});
