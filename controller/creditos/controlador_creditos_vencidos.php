<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$sql = "SELECT 
            vc.id_credito,
            vc.numero_vale,
            c.nombre_completo as cliente,
            vc.saldo_pendiente,
            vc.fecha_vencimiento,
            DATEDIFF(CURDATE(), vc.fecha_vencimiento) as dias_vencido
        FROM ventas_credito vc
        INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
        WHERE vc.estado = 'PENDIENTE' 
        AND vc.fecha_vencimiento < CURDATE()
        ORDER BY vc.fecha_vencimiento ASC
        LIMIT 10";

$stmt = $c->prepare($sql);
$stmt->execute();
$creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'total' => count($creditos),
    'creditos' => $creditos
];

echo json_encode($response);
?>
