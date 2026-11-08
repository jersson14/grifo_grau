<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_reportes.php';

// Los 7 días hasta hoy, también los que no tuvieron turnos cerrados (en cero), para que el gráfico no salte fechas.
$porFecha = [];
foreach ((new Modelo_Reportes())->Ventas_Ultimos_7_Dias() ?: [] as $fila) {
    $porFecha[$fila['fecha_reporte']] = $fila;
}
$dias = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i day"));
    $dias[] = $porFecha[$fecha] ?? ['fecha_reporte' => $fecha, 'total_diesel' => 0, 'total_regular' => 0, 'total_premium' => 0, 'total_ventas' => 0];
}
echo json_encode($dias);
