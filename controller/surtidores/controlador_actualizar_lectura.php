<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$lectura = htmlspecialchars($_POST['lectura'], ENT_QUOTES, 'UTF-8');

$consulta = $MSurtidores->Actualizar_Lectura($id, $lectura);
echo $consulta;
?>
