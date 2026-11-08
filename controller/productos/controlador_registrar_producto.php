<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$nombre = trim((string) ($_POST['nombre']));
$tipo = trim((string) ($_POST['tipo']));
$precio = trim((string) ($_POST['precio']));
// Lo registra siempre quien tiene la sesión abierta.
$id_usuario = (int) $_SESSION['S_ID'];

$consulta = $MProductos->Registrar_Producto($nombre, $tipo, $precio, $id_usuario);
echo $consulta;
?>
