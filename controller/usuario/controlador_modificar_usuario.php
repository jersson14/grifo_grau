<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU = new Modelo_Usuario();

$id     = (int) $_POST['id'];
$dni    = strtoupper(htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8'));
$nombre = strtoupper(htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8'));
$apelli = strtoupper(htmlspecialchars($_POST['apelli'], ENT_QUOTES, 'UTF-8'));
$correo = htmlspecialchars($_POST['correo'], ENT_QUOTES, 'UTF-8');
$tele   = strtoupper(htmlspecialchars($_POST['tele'], ENT_QUOTES, 'UTF-8'));
$dire   = strtoupper(htmlspecialchars($_POST['dire'], ENT_QUOTES, 'UTF-8'));
$usu    = htmlspecialchars($_POST['usu'], ENT_QUOTES, 'UTF-8');
$rol    = strtoupper(htmlspecialchars($_POST['rol'], ENT_QUOTES, 'UTF-8'));

// La foto actual se toma de la base de datos, nunca de lo que envía el navegador.
$fotoActual = $MU->Obtener_Foto_Usuario($id);
$ruta       = $fotoActual;
$fotoNueva  = false;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $nombreFoto = guardar_imagen_subida($_FILES['foto'], __DIR__ . '/fotos');
    if ($nombreFoto === null) {
        echo 0;
        exit;
    }
    $ruta      = 'controller/usuario/fotos/' . $nombreFoto;
    $fotoNueva = true;
}

$consulta = $MU->Modificar_Usuario($id, $dni, $nombre, $apelli, $correo, $tele, $dire, $ruta, $usu, $rol);
echo $consulta;

if ($consulta == 1 && $fotoNueva && is_string($fotoActual) && preg_match('#^controller/usuario/fotos/[\w.-]+$#', $fotoActual)) {
    @unlink(__DIR__ . '/fotos/' . basename($fotoActual));
}
