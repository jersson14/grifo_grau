<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('reporte', $_POST['id_reporte'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = trim((string) ($_POST['id_reporte']));
$id_tipo_pago = trim((string) ($_POST['id_tipo_pago']));
$codigo_operacion = trim((string) ($_POST['codigo_operacion']));
$monto = trim((string) ($_POST['monto']));
$observaciones = trim((string) ($_POST['observaciones']));

$consulta = $MTurnos->Registrar_Pago_Reporte($id_reporte, $id_tipo_pago, $codigo_operacion, $monto, $observaciones);
echo $consulta;
?>
