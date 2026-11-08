<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = trim((string) ($_POST['id']));
$lectura = trim((string) ($_POST['lectura']));

$consulta = $MSurtidores->Actualizar_Lectura($id, $lectura);
echo $consulta;
?>
