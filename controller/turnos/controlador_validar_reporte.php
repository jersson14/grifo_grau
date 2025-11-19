<?php
session_start();
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_reporte = $_POST['id_reporte'];
$observaciones = $_POST['observaciones'] ?? '';

// Obtener el ID del usuario de la sesión (la variable correcta es S_ID)
$id_administrador = $_SESSION['S_ID'] ?? null;

// Verificar que tengamos un ID de administrador
if ($id_administrador === null) {
    echo 0;
    exit;
}

try {
    // Iniciar transacción
    $c->beginTransaction();
    
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
    
    // Verificar que se actualizó correctamente
    if ($resultado && $stmt->rowCount() > 0) {
        $c->commit();
        echo 1;
    } else {
        $c->rollBack();
        echo 0;
    }
} catch (Exception $e) {
    $c->rollBack();
    echo 0;
}
?>
