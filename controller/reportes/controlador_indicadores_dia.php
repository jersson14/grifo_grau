<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();
$totales_dia = $MReportes->Totales_Dia();
$creditos = $MReportes->Resumen_Creditos_Pendientes();

echo json_encode(array(
    'turnos_activos' => (int) $MReportes->Turnos_Activos_Hoy(),
    'turnos_cerrados_hoy' => (int) ($totales_dia['total_turnos'] ?? 0),
    'ventas_dia' => (float) ($totales_dia['total_ventas'] ?? 0),
    'creditos_pendientes' => (int) $creditos['vales'],
    'creditos_saldo' => round((float) $creditos['saldo'], 2),
    'creditos_vencidos' => (int) $creditos['vencidos'],
    'reportes_pendientes' => (int) $MReportes->Total_Reportes_Pendientes(),
));
