<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$id = trim((string) ($_POST['id']));
$nombre = trim((string) ($_POST['nombre']));
$tipo = trim((string) ($_POST['tipo']));
$precio = trim((string) ($_POST['precio']));

$consulta = $MProductos->Modificar_Producto($id, $nombre, $tipo, $precio, (int) $_SESSION['S_ID']);
echo $consulta;
?>
