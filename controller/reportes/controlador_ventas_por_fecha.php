<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$id_grifero = $_POST['id_grifero'] ?? null;

// Construir consulta base
$sql = "SELECT 
            rt.*,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        WHERE rt.fecha_reporte BETWEEN ? AND ?
        AND rt.estado = 'CERRADO'";

$params = [$fecha_inicio, $fecha_fin];

// Filtrar por grifero si se especifica
if ($id_grifero) {
    $sql .= " AND rt.id_usuario = ?";
    $params[] = $id_grifero;
}

$sql .= " ORDER BY rt.fecha_reporte DESC, rt.turno";

$stmt = $c->prepare($sql);
$stmt->execute($params);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular resumen
$resumen = [
    'total_turnos' => count($turnos),
    'total_galones' => 0,
    'total_soles' => 0,
    'galones_diesel' => 0,
    'galones_regular' => 0,
    'galones_premium' => 0,
    'soles_diesel' => 0,
    'soles_regular' => 0,
    'soles_premium' => 0,
    'promedio_turno' => 0
];

foreach ($turnos as $turno) {
    $resumen['total_galones'] += $turno['total_galones'];
    $resumen['total_soles'] += $turno['total_ventas'];
    $resumen['galones_diesel'] += $turno['galones_diesel'];
    $resumen['galones_regular'] += $turno['galones_regular'];
    $resumen['galones_premium'] += $turno['galones_premium'];
    $resumen['soles_diesel'] += $turno['total_diesel'];
    $resumen['soles_regular'] += $turno['total_regular'];
    $resumen['soles_premium'] += $turno['total_premium'];
}

if ($resumen['total_turnos'] > 0) {
    $resumen['promedio_turno'] = $resumen['total_soles'] / $resumen['total_turnos'];
}

// Resumen por grifero
$sql_griferos = "SELECT 
                    u.id_usuario,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    COUNT(*) as total_turnos,
                    SUM(rt.total_galones) as total_galones,
                    SUM(rt.total_ventas) as total_ventas,
                    AVG(rt.total_ventas) as promedio_turno
                FROM reportes_turno rt
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE rt.fecha_reporte BETWEEN ? AND ?
                AND rt.estado = 'CERRADO'";

$params_griferos = [$fecha_inicio, $fecha_fin];

if ($id_grifero) {
    $sql_griferos .= " AND rt.id_usuario = ?";
    $params_griferos[] = $id_grifero;
}

$sql_griferos .= " GROUP BY u.id_usuario, u.usu_nombre, u.usu_apellido
                   ORDER BY total_ventas DESC";

$stmt_griferos = $c->prepare($sql_griferos);
$stmt_griferos->execute($params_griferos);
$por_grifero = $stmt_griferos->fetchAll(PDO::FETCH_ASSOC);

// Respuesta
$response = [
    'turnos' => $turnos,
    'resumen' => $resumen,
    'por_grifero' => $por_grifero
];

echo json_encode($response);
?>
