<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

// Obtener ventas de los últimos 7 días
$sql = "SELECT 
            DATE(fecha_apertura) as fecha,
            COALESCE(SUM(monto_total), 0) as total
        FROM turnos
        WHERE fecha_apertura >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        AND estado = 'CERRADO'
        GROUP BY DATE(fecha_apertura)
        ORDER BY fecha ASC";

$resultado = $conexion->query($sql);

$labels = [];
$values = [];

// Crear array con los últimos 7 días
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d/m', strtotime($fecha));
    $values[$fecha] = 0;
}

// Llenar con datos reales
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $values[$fila['fecha']] = floatval($fila['total']);
    }
}

$response = [
    'labels' => $labels,
    'values' => array_values($values)
];

echo json_encode($response);

$conexion->close();
?>
