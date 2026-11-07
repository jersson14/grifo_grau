<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();

$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$tipo = htmlspecialchars($_POST['tipo'], ENT_QUOTES, 'UTF-8');
$precio = htmlspecialchars($_POST['precio'], ENT_QUOTES, 'UTF-8');
// Lo registra siempre quien tiene la sesión abierta.
$id_usuario = (int) $_SESSION['S_ID'];

$consulta = $MProductos->Registrar_Producto($nombre, $tipo, $precio, $id_usuario);
echo $consulta;
?>
