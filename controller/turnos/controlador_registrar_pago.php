<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');
$id_tipo_pago = htmlspecialchars($_POST['id_tipo_pago'], ENT_QUOTES, 'UTF-8');
$codigo_operacion = htmlspecialchars($_POST['codigo_operacion'], ENT_QUOTES, 'UTF-8');
$monto = htmlspecialchars($_POST['monto'], ENT_QUOTES, 'UTF-8');
$observaciones = htmlspecialchars($_POST['observaciones'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Registrar_Pago_Reporte($id_reporte, $id_tipo_pago, $codigo_operacion, $monto, $observaciones);
echo $consulta;
?>
