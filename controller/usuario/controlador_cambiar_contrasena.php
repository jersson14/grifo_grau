<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_usuario = $_POST['id_usuario'];
$contrasena_actual = $_POST['contrasena_actual'];
$contrasena_nueva = $_POST['contrasena_nueva'];

// Verificar contraseña actual
$sql = "SELECT usu_contrasenia FROM usuario WHERE id_usuario = ?";
$stmt = $c->prepare($sql);
$stmt->execute(array($id_usuario));
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (password_verify($contrasena_actual, $usuario['usu_contrasenia'])) {
    // Actualizar contraseña
    $nueva_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
    
    $sql_update = "UPDATE usuario SET usu_contrasenia = ?, updated_at = NOW() WHERE id_usuario = ?";
    $stmt_update = $c->prepare($sql_update);
    $resultado = $stmt_update->execute(array($nueva_hash, $id_usuario));
    
    if ($resultado) {
        echo 1; // Éxito
    } else {
        echo 0; // Error al actualizar
    }
} else {
    echo 2; // Contraseña actual incorrecta
}
?>
