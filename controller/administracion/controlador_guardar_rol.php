<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_roles.php';

responder_json(function () {
    $capacidades = json_decode((string) ($_POST['capacidades'] ?? '[]'), true);
    $m = new Modelo_Roles();
    $m->Guardar((string) ($_POST['codigo'] ?? ''), (string) ($_POST['nombre'] ?? ''), trim((string) ($_POST['descripcion'] ?? '')), is_array($capacidades) ? $capacidades : [], (string) ($_POST['vista_inicio'] ?? ''), (int) $_SESSION['S_ID']);
});
