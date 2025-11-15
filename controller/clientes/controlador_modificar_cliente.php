<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$dni = htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8');
$telefono = htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8');
$direccion = htmlspecialchars($_POST['direccion'], ENT_QUOTES, 'UTF-8');

$consulta = $MClientes->Modificar_Cliente($id, $nombre, $dni, $telefono, $direccion);
echo $consulta;
?>
