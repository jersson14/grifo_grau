<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

// Obtener turnos por semana del mes actual
$sql = "SELECT 
            WEEK(fecha_apertura, 1) - WEEK(DATE_SUB(fecha_apertura, INTERVAL DAYOFMONTH(fecha_apertura) - 1 DAY), 1) + 1 as semana,
            COUNT(*) as total
        FROM turnos
        WHERE MONTH(fecha_apertura) = MONTH(CURDATE())
        AND YEAR(fecha_apertura) = YEAR(CURDATE())
        AND estado = 'CERRADO'
        GROUP BY semana
        ORDER BY semana ASC";

$resultado = $conexion->query($sql);

$labels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5'];
$values = [0, 0, 0, 0, 0];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $semana = intval($fila['semana']) - 1;
        if ($semana >= 0 && $semana < 5) {
            $values[$semana] = intval($fila['total']);
        }
    }
}

// Eliminar semanas vacías al final
while (count($values) > 1 && end($values) === 0) {
    array_pop($values);
    array_pop($labels);
}

$response = [
    'labels' => $labels,
    'values' => $values
];

echo json_encode($response);

$conexion->close();
?>
