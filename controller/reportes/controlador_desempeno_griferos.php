<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$consulta = $MReportes->Desempeno_Por_Grifero_Mes();
echo json_encode($consulta);
?>
