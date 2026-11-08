<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU = new Modelo_Usuario();

$id     = (int) $_POST['id'];
$dni    = strtoupper(trim((string) ($_POST['dni'])));
$nombre = strtoupper(trim((string) ($_POST['nombre'])));
$apelli = strtoupper(trim((string) ($_POST['apelli'])));
$correo = trim((string) ($_POST['correo']));
$tele   = strtoupper(trim((string) ($_POST['tele'])));
$dire   = strtoupper(trim((string) ($_POST['dire'])));
$usu    = trim((string) ($_POST['usu']));
$rol    = strtoupper(trim((string) ($_POST['rol'])));

$MU->Validar_Cambio_Rol($id, $rol);

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
