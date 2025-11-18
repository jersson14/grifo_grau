<?php
session_start();
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_usuario = $_POST['id_usuario'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];

$sql = "UPDATE usuario SET 
        usu_nombre = ?,
        usu_apellido = ?,
        usu_email = ?,
        usu_telefono = ?,
        usu_direccion = ?,
        updated_at = NOW()
        WHERE id_usuario = ?";

$stmt = $c->prepare($sql);
$resultado = $stmt->execute(array($nombre, $apellido, $email, $telefono, $direccion, $id_usuario));

if ($resultado) {
    // Actualizar sesión
    $_SESSION['S_NOMBRE'] = $nombre;
    $_SESSION['S_COMPLETOS'] = $nombre . ' ' . $apellido;
    echo 1;
} else {
    echo 0;
}
?>
