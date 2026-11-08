<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$numero_maquina = trim((string) ($_POST['numero_maquina']));

$consulta = $MSurtidores->Obtener_Surtidores_Por_Maquina($numero_maquina);
echo json_encode($consulta);
?>
