<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();
$resultado = $MReportes->Ventas_Mes_Actual() ?: [];

// Desglose del mes por producto, desde las lecturas: cualquier combustible, no solo diésel, regular y premium.
$c = conexionBD::conexionPDO();
$q = $c->query("SELECT p.nombre, p.tipo, ROUND(SUM(lt.total_soles), 2) AS total, ROUND(SUM(lt.galones_vendidos), 3) AS galones
                FROM lecturas_turno lt
                INNER JOIN reportes_turno rt ON rt.id_reporte = lt.id_reporte AND rt.estado = 'CERRADO'
                     AND rt.fecha_reporte >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND rt.fecha_reporte <= CURDATE()
                INNER JOIN surtidores s ON s.id_surtidor = lt.id_surtidor
                INNER JOIN productos p ON p.id_producto = s.id_producto
                GROUP BY p.id_producto, p.nombre, p.tipo
                ORDER BY FIELD(p.tipo, 'DIESEL', 'REGULAR', 'PREMIUM') = 0, FIELD(p.tipo, 'DIESEL', 'REGULAR', 'PREMIUM'), p.nombre");
$resultado['por_producto'] = $q->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultado);
