<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$tipo = htmlspecialchars($_POST['tipo'], ENT_QUOTES, 'UTF-8');
$precio = htmlspecialchars($_POST['precio'], ENT_QUOTES, 'UTF-8');

$consulta = $MProductos->Modificar_Producto($id, $nombre, $tipo, $precio, (int) $_SESSION['S_ID']);
echo $consulta;
?>
