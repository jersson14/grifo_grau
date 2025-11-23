<?php
require_once 'model/model_conexion.php';

echo "<h2>Prueba de Créditos - Verificar Cálculo de Pagado</h2>";

$conexion = conexionBD::conexionPDO();

if ($conexion === null) {
    echo "<p style='color: red;'>❌ Error: No se pudo conectar a la base de datos</p>";
    exit;
}

echo "<p style='color: green;'>✅ Conexión exitosa</p>";

// Consulta para ver los créditos con detalle
$sql = "SELECT 
            vc.id_credito,
            vc.numero_vale,
            c.nombre_completo,
            vc.monto,
            vc.saldo_pendiente,
            (vc.monto - vc.saldo_pendiente) as monto_pagado,
            vc.estado
        FROM ventas_credito vc
        INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
        WHERE vc.estado = 'PENDIENTE'
        ORDER BY c.nombre_completo, vc.numero_vale";

$query = $conexion->prepare($sql);
$query->execute();
$creditos = $query->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Créditos Pendientes (Detalle):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr style='background-color:#dc3545; color:white'>";
echo "<th>Vale</th><th>Cliente</th><th>Monto</th><th>Saldo Pendiente</th><th>Pagado (Calculado)</th><th>Estado</th>";
echo "</tr>";

foreach ($creditos as $credito) {
    echo "<tr>";
    echo "<td>" . $credito['numero_vale'] . "</td>";
    echo "<td>" . $credito['nombre_completo'] . "</td>";
    echo "<td>S/. " . number_format($credito['monto'], 2) . "</td>";
    echo "<td>S/. " . number_format($credito['saldo_pendiente'], 2) . "</td>";
    echo "<td>S/. " . number_format($credito['monto_pagado'], 2) . "</td>";
    echo "<td>" . $credito['estado'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Consulta agrupada por cliente
$sql_agrupado = "SELECT 
                    c.id_cliente,
                    c.nombre_completo,
                    COUNT(vc.id_credito) as total_vales,
                    COALESCE(SUM(vc.monto), 0) as monto_total,
                    COALESCE(SUM(vc.saldo_pendiente), 0) as saldo_pendiente,
                    COALESCE(SUM(vc.monto - vc.saldo_pendiente), 0) as monto_pagado
                FROM clientes c
                INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                WHERE vc.estado = 'PENDIENTE'
                GROUP BY c.id_cliente, c.nombre_completo
                HAVING saldo_pendiente > 0
                ORDER BY c.nombre_completo";

$query_agrupado = $conexion->prepare($sql_agrupado);
$query_agrupado->execute();
$clientes = $query_agrupado->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Créditos Agrupados por Cliente:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr style='background-color:#dc3545; color:white'>";
echo "<th>Cliente</th><th>Total Vales</th><th>Monto Total</th><th>Saldo Pendiente</th><th>Pagado (Calculado)</th>";
echo "</tr>";

foreach ($clientes as $cliente) {
    echo "<tr>";
    echo "<td>" . $cliente['nombre_completo'] . "</td>";
    echo "<td>" . $cliente['total_vales'] . "</td>";
    echo "<td>S/. " . number_format($cliente['monto_total'], 2) . "</td>";
    echo "<td>S/. " . number_format($cliente['saldo_pendiente'], 2) . "</td>";
    echo "<td>S/. " . number_format($cliente['monto_pagado'], 2) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Ver historial de pagos
$sql_historial = "SELECT 
                    hpc.*,
                    vc.numero_vale,
                    c.nombre_completo
                FROM historial_pagos_credito hpc
                INNER JOIN ventas_credito vc ON hpc.id_credito = vc.id_credito
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                ORDER BY hpc.fecha_pago DESC
                LIMIT 10";

$query_historial = $conexion->prepare($sql_historial);
$query_historial->execute();
$pagos = $query_historial->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Últimos 10 Pagos Registrados:</h3>";
if (count($pagos) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr style='background-color:#28a745; color:white'>";
    echo "<th>Fecha</th><th>Cliente</th><th>Vale</th><th>Monto Pagado</th><th>Saldo Anterior</th><th>Saldo Nuevo</th>";
    echo "</tr>";
    
    foreach ($pagos as $pago) {
        echo "<tr>";
        echo "<td>" . $pago['fecha_pago'] . "</td>";
        echo "<td>" . $pago['nombre_completo'] . "</td>";
        echo "<td>" . $pago['numero_vale'] . "</td>";
        echo "<td>S/. " . number_format($pago['monto_pagado'], 2) . "</td>";
        echo "<td>S/. " . number_format($pago['saldo_anterior'], 2) . "</td>";
        echo "<td>S/. " . number_format($pago['saldo_nuevo'], 2) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No hay pagos registrados aún</p>";
}
?>
