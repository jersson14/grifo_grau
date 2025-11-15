<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$consulta = $MReportes->Ventas_Mes_Actual();
echo json_encode($consulta);
?>
