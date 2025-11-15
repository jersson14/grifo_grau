<?php
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$estado = htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8');

$consulta = $MProductos->Cambiar_Estado_Producto($id, $estado);
echo $consulta;
?>
