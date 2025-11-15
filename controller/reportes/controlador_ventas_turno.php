<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$consulta = $MReportes->Ventas_Por_Turno_Mes();
echo json_encode($consulta);
?>
