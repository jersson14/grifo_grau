<?php
session_start();
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_reporte = $_POST['id_reporte'];
$observaciones = $_POST['observaciones'] ?? '';
$id_administrador = $_SESSION['S_IDUSUARIO'] ?? null;

// Actualizar observaciones si se proporcionaron
if (!empty($observaciones)) {
    $sql_obs = "UPDATE reportes_turno 
                SET observaciones = ?
                WHERE id_reporte = ?";
    $stmt_obs = $c->prepare($sql_obs);
    $stmt_obs->execute(array($observaciones, $id_reporte));
}

// Marcar como validado/aprobado
$sql = "UPDATE reportes_turno 
        SET id_administrador = ?,
            fecha_aprobacion = NOW()
        WHERE id_reporte = ?";

$stmt = $c->prepare($sql);
$resultado = $stmt->execute(array($id_administrador, $id_reporte));

if ($resultado) {
    echo 1;
} else {
    echo 0;
}
?>
