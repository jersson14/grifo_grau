<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$consulta = $MReportes->Ventas_Ultimos_7_Dias();
echo json_encode($consulta);
?>
