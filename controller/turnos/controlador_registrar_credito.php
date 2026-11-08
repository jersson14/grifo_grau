<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('reporte', $_POST['id_reporte'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = trim((string) ($_POST['id_reporte']));
$id_cliente = trim((string) ($_POST['id_cliente']));
$numero_vale = trim((string) ($_POST['numero_vale']));
$monto = trim((string) ($_POST['monto']));
$fecha_vencimiento = trim((string) ($_POST['fecha_vencimiento']));
$observaciones = trim((string) ($_POST['observaciones']));

$consulta = $MTurnos->Registrar_Credito($id_reporte, $id_cliente, $numero_vale, $monto, $fecha_vencimiento, $observaciones);
echo $consulta;
?>
