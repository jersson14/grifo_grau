<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

$id_reporte = $_POST['id_reporte'];

// Obtener totales por tipo de combustible desde lecturas_turno
$sql = "SELECT 
            p.nombre_producto,
            SUM(lt.total_soles) as total
        FROM lecturas_turno lt
        INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
        INNER JOIN productos p ON s.id_producto = p.id_producto
        WHERE lt.id_reporte = ?
        GROUP BY p.id_producto, p.nombre_producto";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_reporte);
$stmt->execute();
$resultado = $stmt->get_result();

$total_diesel = 0;
$total_regular = 0;
$total_premium = 0;
$total_ventas = 0;

while ($fila = $resultado->fetch_assoc()) {
    $producto = strtoupper($fila['nombre_producto']);
    $total = floatval($fila['total']);
    
    if (strpos($producto, 'DIESEL') !== false || strpos($producto, 'DB5') !== false) {
        $total_diesel += $total;
    } elseif (strpos($producto, 'REGULAR') !== false || strpos($producto, '90') !== false || strpos($producto, '84') !== false) {
        $total_regular += $total;
    } elseif (strpos($producto, 'PREMIUM') !== false || strpos($producto, '95') !== false || strpos($producto, '97') !== false) {
        $total_premium += $total;
    }
    
    $total_ventas += $total;
}

$response = [
    'total_diesel' => $total_diesel,
    'total_regular' => $total_regular,
    'total_premium' => $total_premium,
    'total_ventas' => $total_ventas
];

echo json_encode($response);

$conexion->close();
?>
