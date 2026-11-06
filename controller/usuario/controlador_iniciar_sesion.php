<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_usuario.php';

header('Content-Type: application/json; charset=utf-8');

$usuario    = trim($_POST['u'] ?? '');
$contrasena = $_POST['c'] ?? '';

$MU = new Modelo_Usuario();
echo json_encode($MU->Iniciar_Sesion($usuario, $contrasena, $_SERVER['REMOTE_ADDR'] ?? ''));
