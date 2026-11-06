<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_configuracion.php';

header('Content-Type: application/json; charset=utf-8');

$config = new Modelo_Configuracion();
echo json_encode($config->Guardar($_POST, (int) $_SESSION['S_ID']));
