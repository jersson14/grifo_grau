<?php
require_once '../../model/model_conexion.php';

$conexion = conexion();

// Contar créditos por estado
$sql_pendientes = "SELECT COUNT(*) as total FROM creditos WHERE estado = 'PENDIENTE' AND saldo_pendiente > 0";
$sql_pagados = "SELECT COUNT(*) as total FROM creditos WHERE estado = 'PAGADO'";
$sql_vencidos = "SELECT COUNT(*) as total FROM creditos WHERE estado = 'PENDIENTE' AND fecha_vencimiento < CURDATE()";

$pendientes = 0;
$pagados = 0;
$vencidos = 0;

$resultado = $conexion->query($sql_pendientes);
if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $pendientes = intval($fila['total']);
}

$resultado = $conexion->query($sql_pagados);
if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $pagados = intval($fila['total']);
}

$resultado = $conexion->query($sql_vencidos);
if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $vencidos = intval($fila['total']);
}

$response = [
    'labels' => ['Pendientes', 'Pagados', 'Vencidos'],
    'values' => [$pendientes, $pagados, $vencidos]
];

echo json_encode($response);

$conexion->close();
?>
