<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_reporte = $_POST['id_reporte'];
$descuentos = floatval($_POST['descuentos'] ?? 0);
$otros_gastos = floatval($_POST['otros_gastos'] ?? 0);

// Total ventas (de las lecturas de surtidores)
$sql_ventas = "SELECT COALESCE(SUM(total_soles), 0) as total
               FROM lecturas_turno
               WHERE id_reporte = ?";
$stmt = $c->prepare($sql_ventas);
$stmt->execute(array($id_reporte));
$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$total_ventas = floatval($fila['total']);

// Total pagos
$sql_pagos = "SELECT COALESCE(SUM(monto), 0) as total
              FROM pagos_reporte
              WHERE id_reporte = ?";
$stmt = $c->prepare($sql_pagos);
$stmt->execute(array($id_reporte));
$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$total_pagos = floatval($fila['total']);

// Total créditos
$sql_creditos = "SELECT COALESCE(SUM(monto), 0) as total
                 FROM ventas_credito
                 WHERE id_reporte = ?";
$stmt = $c->prepare($sql_creditos);
$stmt->execute(array($id_reporte));
$fila = $stmt->fetch(PDO::FETCH_ASSOC);
$total_creditos = floatval($fila['total']);

// Calcular faltante/sobrante
// Faltante = Ventas - (Pagos + Créditos + Descuentos + Otros Gastos)
$faltante = $total_ventas - ($total_pagos + $total_creditos + $descuentos + $otros_gastos);

$response = [
    'total_ventas' => $total_ventas,
    'total_pagos' => $total_pagos,
    'total_creditos' => $total_creditos,
    'descuentos' => $descuentos,
    'otros_gastos' => $otros_gastos,
    'faltante' => $faltante
];

echo json_encode($response);
?>
