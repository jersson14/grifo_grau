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
            rt.monto_faltante as faltante
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        WHERE rt.estado = 'CERRADO' 
        AND (rt.id_administrador IS NULL OR rt.fecha_aprobacion IS NULL)
        ORDER BY rt.fecha_reporte DESC";

$stmt = $c->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = ['data' => $data];
echo json_encode($response);
?>
