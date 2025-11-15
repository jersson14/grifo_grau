<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$turnos_activos = $MReportes->Turnos_Activos_Hoy();
$totales_dia = $MReportes->Totales_Dia();
$creditos_pendientes = $MReportes->Total_Creditos_Pendientes();
$reportes_pendientes = $MReportes->Total_Reportes_Pendientes();

$resultado = array(
    'turnos_activos' => $turnos_activos,
    'ventas_dia' => $totales_dia['total_ventas'] ? $totales_dia['total_ventas'] : 0,
    'creditos_pendientes' => $creditos_pendientes,
    'reportes_pendientes' => $reportes_pendientes
);

echo json_encode($resultado);
?>
