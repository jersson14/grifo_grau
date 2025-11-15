<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

$sql = "SELECT COUNT(*) as total 
        FROM turnos 
        WHERE estado = 'ABIERTO' 
        AND DATE(fecha_apertura) = CURDATE()";

$resultado = $conexion->query($sql);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    echo $fila['total'];
} else {
    echo '0';
}

$conexion->close();
?>
