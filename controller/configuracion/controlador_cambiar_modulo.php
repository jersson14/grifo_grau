<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_configuracion.php';

header('Content-Type: application/json; charset=utf-8');

$config = new Modelo_Configuracion();
$ok = $config->Cambiar_Modulo((string) ($_POST['codigo'] ?? ''), ($_POST['activo'] ?? '') === '1', (int) $_SESSION['S_ID']);
echo json_encode(['success' => $ok]);
