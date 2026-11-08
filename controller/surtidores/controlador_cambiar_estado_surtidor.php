<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = trim((string) ($_POST['id']));
$estado = trim((string) ($_POST['estado']));

$consulta = $MSurtidores->Cambiar_Estado_Surtidor($id, $estado);
echo $consulta;
?>
