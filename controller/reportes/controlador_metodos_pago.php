<?php
require_once '../../model/model_reportes.php';

$MReportes = new Modelo_Reportes();

$resultado = $MReportes->Metodos_Pago_Mes();

echo json_encode($resultado);
?>
