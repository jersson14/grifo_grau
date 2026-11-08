<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$nombre = trim((string) ($_POST['nombre']));
$dni = trim((string) ($_POST['dni']));
$telefono = trim((string) ($_POST['telefono']));
$direccion = trim((string) ($_POST['direccion']));
// Lo registra siempre quien tiene la sesión abierta.
$id_usuario = (int) $_SESSION['S_ID'];

$consulta = $MClientes->Registrar_Cliente($nombre, $dni, $telefono, $direccion, $id_usuario);
echo $consulta;
?>
