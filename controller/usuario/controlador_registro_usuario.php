<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU = new Modelo_Usuario();

$dni    = strtoupper(htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8'));
$nombre = strtoupper(htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8'));
$apelli = strtoupper(htmlspecialchars($_POST['apelli'], ENT_QUOTES, 'UTF-8'));
$correo = htmlspecialchars($_POST['correo'], ENT_QUOTES, 'UTF-8');
$tele   = strtoupper(htmlspecialchars($_POST['tele'], ENT_QUOTES, 'UTF-8'));
$dire   = strtoupper(htmlspecialchars($_POST['dire'], ENT_QUOTES, 'UTF-8'));

$usu    = htmlspecialchars($_POST['usu'], ENT_QUOTES, 'UTF-8');
$contra = password_hash($_POST['contra'], PASSWORD_DEFAULT, ['cost' => 12]);
$rol    = htmlspecialchars($_POST['rol'], ENT_QUOTES, 'UTF-8');

$ruta = 'controller/usuario/fotos/';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $nombreFoto = guardar_imagen_subida($_FILES['foto'], __DIR__ . '/fotos');
    if ($nombreFoto === null) {
        echo 0;
        exit;
    }
    $ruta .= $nombreFoto;
}

echo $MU->Registrar_Usuario($dni, $nombre, $apelli, $correo, $tele, $dire, $ruta, $usu, $contra, $rol);
