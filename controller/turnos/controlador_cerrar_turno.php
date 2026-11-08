<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('reporte', $_POST['id_reporte'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = trim((string) ($_POST['id_reporte']));
$descuentos = trim((string) ($_POST['descuentos']));
$otros_gastos = trim((string) ($_POST['otros_gastos']));
$monto_efectivo = trim((string) ($_POST['monto_efectivo']));

$consulta = $MTurnos->Cerrar_Turno($id_reporte, $descuentos, $otros_gastos, $monto_efectivo);
echo $consulta;
?>
