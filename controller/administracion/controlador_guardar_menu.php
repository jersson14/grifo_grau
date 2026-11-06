<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_menu.php';

responder_json(function () {
    $roles = json_decode((string) ($_POST['roles'] ?? '[]'), true);
    $m = new Modelo_Menu();
    $m->Guardar((int) ($_POST['id_item'] ?? 0), (string) ($_POST['titulo'] ?? ''), trim((string) ($_POST['icono'] ?? '')), ($_POST['visible'] ?? '') === '1', is_array($roles) ? $roles : []);
});
