<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$resultado = $MReportes->Desempeno_Por_Grifero_Mes();

echo json_encode($resultado);
?>
