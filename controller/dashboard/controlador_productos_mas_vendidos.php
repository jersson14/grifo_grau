<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

$sql = "SELECT 
            p.nombre_producto as producto,
            COALESCE(SUM(dt.cantidad), 0) as total
        FROM productos p
        LEFT JOIN detalle_turno dt ON p.id_producto = dt.id_producto
        LEFT JOIN turnos t ON dt.id_turno = t.id_turno
        WHERE t.fecha_apertura >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY p.id_producto, p.nombre_producto
        ORDER BY total DESC
        LIMIT 5";

$resultado = $conexion->query($sql);

$labels = [];
$values = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $labels[] = $fila['producto'];
        $values[] = floatval($fila['total']);
    }
}

// Si no hay datos, mostrar productos por defecto
if (empty($labels)) {
    $labels = ['Sin datos'];
    $values = [0];
}

$response = [
    'labels' => $labels,
    'values' => $values
];

echo json_encode($response);

$conexion->close();
?>
