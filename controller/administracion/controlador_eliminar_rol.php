<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_roles.php';

responder_json(function () {
    $m = new Modelo_Roles();
    $m->Eliminar((string) ($_POST['codigo'] ?? ''));
});
