<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

$sql = "SELECT COALESCE(SUM(monto_total), 0) as total 
        FROM turnos 
        WHERE DATE(fecha_apertura) = CURDATE()";

$resultado = $conexion->query($sql);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    echo number_format($fila['total'], 2);
} else {
    echo '0.00';
}

$conexion->close();
?>
