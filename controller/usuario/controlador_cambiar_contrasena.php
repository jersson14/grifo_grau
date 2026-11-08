<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_usuario.php';

// El usuario en sesión cambia su propia contraseña. El id nunca se toma del formulario.
header('Content-Type: application/json; charset=utf-8');
echo json_encode((new Modelo_Usuario())->Cambiar_Clave_Propia(
    (int) $_SESSION['S_ID'],
    (string) ($_POST['contrasena_actual'] ?? ''),
    (string) ($_POST['contrasena_nueva'] ?? ''),
    $_SERVER['REMOTE_ADDR'] ?? ''
));
