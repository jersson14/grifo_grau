<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$numero_maquina = htmlspecialchars($_POST['numero_maquina'], ENT_QUOTES, 'UTF-8');

$consulta = $MSurtidores->Obtener_Surtidores_Por_Maquina($numero_maquina);
echo json_encode($consulta);
?>
