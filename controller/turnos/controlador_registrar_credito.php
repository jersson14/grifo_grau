<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');
$id_cliente = htmlspecialchars($_POST['id_cliente'], ENT_QUOTES, 'UTF-8');
$numero_vale = htmlspecialchars($_POST['numero_vale'], ENT_QUOTES, 'UTF-8');
$monto = htmlspecialchars($_POST['monto'], ENT_QUOTES, 'UTF-8');
$fecha_vencimiento = htmlspecialchars($_POST['fecha_vencimiento'], ENT_QUOTES, 'UTF-8');
$observaciones = htmlspecialchars($_POST['observaciones'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Registrar_Credito($id_reporte, $id_cliente, $numero_vale, $monto, $fecha_vencimiento, $observaciones);
echo $consulta;
?>
