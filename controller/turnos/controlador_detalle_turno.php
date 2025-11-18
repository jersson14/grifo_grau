<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_reporte = $_POST['id_reporte'];

// Obtener información del turno
$sql = "SELECT 
            rt.numero_documento,
            DATE_FORMAT(rt.fecha_reporte, '%d/%m/%Y') as fecha,
            rt.turno,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        WHERE rt.id_reporte = ?";

$stmt = $c->prepare($sql);
$stmt->execute(array($id_reporte));
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular totales por combustible (soles y galones)
$sql_totales = "SELECT 
                    p.nombre as nombre_producto,
                    SUM(lt.total_soles) as total_soles,
                    SUM(lt.galones_vendidos) as total_galones
                FROM lecturas_turno lt
                INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE lt.id_reporte = ?
                GROUP BY p.id_producto, p.nombre";

$stmt = $c->prepare($sql_totales);
$stmt->execute(array($id_reporte));
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_diesel = 0;
$total_regular = 0;
$total_premium = 0;
$total_ventas = 0;
$galones_diesel = 0;
$galones_regular = 0;
$galones_premium = 0;
$total_galones = 0;

foreach ($resultado as $fila) {
    $producto = strtoupper($fila['nombre_producto']);
    $total = floatval($fila['total_soles']);
    $galones = floatval($fila['total_galones']);
    
    if (strpos($producto, 'DIESEL') !== false || strpos($producto, 'DB5') !== false) {
        $total_diesel += $total;
        $galones_diesel += $galones;
    } elseif (strpos($producto, 'REGULAR') !== false || strpos($producto, '90') !== false || strpos($producto, '84') !== false) {
        $total_regular += $total;
        $galones_regular += $galones;
    } elseif (strpos($producto, 'PREMIUM') !== false || strpos($producto, '95') !== false || strpos($producto, '97') !== false) {
        $total_premium += $total;
        $galones_premium += $galones;
    }
    
    $total_ventas += $total;
    $total_galones += $galones;
}

$response = [
    'numero_documento' => $turno['numero_documento'],
    'fecha' => $turno['fecha'],
    'turno' => $turno['turno'],
    'grifero' => $turno['grifero'],
    'total_diesel' => $total_diesel,
    'total_regular' => $total_regular,
    'total_premium' => $total_premium,
    'total_ventas' => $total_ventas,
    'galones_diesel' => $galones_diesel,
    'galones_regular' => $galones_regular,
    'galones_premium' => $galones_premium,
    'total_galones' => $total_galones
];

echo json_encode($response);
?>
