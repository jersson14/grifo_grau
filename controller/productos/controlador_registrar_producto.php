<?php
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$tipo = htmlspecialchars($_POST['tipo'], ENT_QUOTES, 'UTF-8');
$precio = htmlspecialchars($_POST['precio'], ENT_QUOTES, 'UTF-8');
$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');

$consulta = $MProductos->Registrar_Producto($nombre, $tipo, $precio, $id_usuario);
echo $consulta;
?>
