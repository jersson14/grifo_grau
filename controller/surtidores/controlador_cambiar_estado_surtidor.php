<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$estado = htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8');

$consulta = $MSurtidores->Cambiar_Estado_Surtidor($id, $estado);
echo $consulta;
?>
