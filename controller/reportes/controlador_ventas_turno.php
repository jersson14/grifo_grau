<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$resultado = $MReportes->Ventas_Por_Turno_Mes();

echo json_encode($resultado);
?>
