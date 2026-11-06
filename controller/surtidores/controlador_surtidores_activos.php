<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$consulta = $MSurtidores->Obtener_Surtidores_Activos();
echo json_encode($consulta);
?>
