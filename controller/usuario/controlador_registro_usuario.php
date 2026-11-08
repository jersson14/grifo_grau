<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU = new Modelo_Usuario();

$dni    = strtoupper(trim((string) ($_POST['dni'])));
$nombre = strtoupper(trim((string) ($_POST['nombre'])));
$apelli = strtoupper(trim((string) ($_POST['apelli'])));
$correo = trim((string) ($_POST['correo']));
$tele   = strtoupper(trim((string) ($_POST['tele'])));
$dire   = strtoupper(trim((string) ($_POST['dire'])));

$usu    = trim((string) ($_POST['usu']));
$contra = password_hash($_POST['contra'], PASSWORD_DEFAULT, ['cost' => 12]);
$rol    = trim((string) ($_POST['rol']));

$MU->Validar_Cambio_Rol(null, $rol);

$ruta = 'img/avatar.png';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $nombreFoto = guardar_imagen_subida($_FILES['foto'], __DIR__ . '/fotos');
    if ($nombreFoto === null) {
        echo 0;
        exit;
    }
    $ruta = 'controller/usuario/fotos/' . $nombreFoto;
}

$resultado = $MU->Registrar_Usuario($dni, $nombre, $apelli, $correo, $tele, $dire, $ruta, $usu, $contra, $rol);
// La clave la eligió el administrador: el usuario la cambia al entrar por primera vez.
if ((string) $resultado === '1') {
    Modelo_Usuario::Marcar_Cambio_Clave($usu);
}
echo $resultado;
