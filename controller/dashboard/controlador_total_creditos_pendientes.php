<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

$sql = "SELECT COUNT(*) as total 
        FROM creditos 
        WHERE estado = 'PENDIENTE' 
        AND saldo_pendiente > 0";

$resultado = $conexion->query($sql);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    echo $fila['total'];
} else {
    echo '0';
}

$conexion->close();
?>
