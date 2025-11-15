<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');
$descuentos = htmlspecialchars($_POST['descuentos'], ENT_QUOTES, 'UTF-8');
$otros_gastos = htmlspecialchars($_POST['otros_gastos'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Cerrar_Turno($id_reporte, $descuentos, $otros_gastos);
echo $consulta;
?>
