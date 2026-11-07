<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
$dni = htmlspecialchars($_POST['dni'], ENT_QUOTES, 'UTF-8');
$telefono = htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8');
$direccion = htmlspecialchars($_POST['direccion'], ENT_QUOTES, 'UTF-8');
// Lo registra siempre quien tiene la sesión abierta.
$id_usuario = (int) $_SESSION['S_ID'];

$consulta = $MClientes->Registrar_Cliente($nombre, $dni, $telefono, $direccion, $id_usuario);
echo $consulta;
?>
