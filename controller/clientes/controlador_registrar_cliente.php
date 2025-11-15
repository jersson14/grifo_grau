<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$dni = htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8');
$telefono = htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8');
$direccion = htmlspecialchars($_POST['direccion'], ENT_QUOTES, 'UTF-8');
$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');

$consulta = $MClientes->Registrar_Cliente($nombre, $dni, $telefono, $direccion, $id_usuario);
echo $consulta;
?>
