<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$sql = "SELECT 
            rt.id_reporte as id_turno,
            rt.numero_documento,
            rt.fecha_reporte as fecha_apertura,
            rt.turno,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero,
            rt.total_ventas as monto_total,
            rt.monto_faltante as faltante,
            CASE 
                WHEN rt.id_administrador IS NOT NULL AND rt.fecha_aprobacion IS NOT NULL THEN 'VALIDADO'
                ELSE 'PENDIENTE'
            END as estado_validacion,
            rt.fecha_aprobacion,
            CONCAT(adm.usu_nombre, ' ', adm.usu_apellido) as validado_por
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        LEFT JOIN usuario adm ON rt.id_administrador = adm.id_usuario
        WHERE rt.estado = 'CERRADO' 
        AND (rt.id_administrador IS NULL OR rt.fecha_aprobacion IS NULL)
        ORDER BY rt.fecha_reporte DESC";

$stmt = $c->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = ['data' => $data];
echo json_encode($response);
?>
