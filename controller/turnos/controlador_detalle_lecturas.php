<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_reporte = $_POST['id_reporte'];

$sql = "SELECT 
            s.numero_maquina,
            s.codigo,
            p.nombre as producto,
            lt.lectura_anterior,
            lt.lectura_actual,
            lt.galones_vendidos,
            lt.precio_galon as precio,
            lt.total_soles as total
        FROM lecturas_turno lt
        INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
        INNER JOIN productos p ON s.id_producto = p.id_producto
        WHERE lt.id_reporte = ?
        ORDER BY 
            s.numero_maquina,
            CASE 
                WHEN s.codigo LIKE '%DS1%' OR s.codigo LIKE '%BS1%' THEN 1
                WHEN s.codigo LIKE '%DS2%' OR s.codigo LIKE '%BS2%' THEN 2
                WHEN s.codigo LIKE '%R1%' THEN 3
                WHEN s.codigo LIKE '%R2%' THEN 4
                WHEN s.codigo LIKE '%P1%' THEN 5
                WHEN s.codigo LIKE '%P2%' THEN 6
                ELSE 7
            END";

$stmt = $c->prepare($sql);
$stmt->execute(array($id_reporte));
$lecturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($lecturas);
?>
