<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$id = trim((string) ($_POST['id']));
$estado = trim((string) ($_POST['estado']));

$consulta = $MProductos->Cambiar_Estado_Producto($id, $estado);
echo $consulta;
?>
