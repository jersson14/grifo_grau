<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$consulta = $MSurtidores->Obtener_Surtidores_Activos();
echo json_encode($consulta);
?>
