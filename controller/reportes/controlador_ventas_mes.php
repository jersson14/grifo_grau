<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$resultado = $MReportes->Ventas_Mes_Actual();

echo json_encode($resultado);
?>
