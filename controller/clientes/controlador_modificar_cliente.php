<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = trim((string) ($_POST['id']));
$nombre = trim((string) ($_POST['nombre']));
$dni = trim((string) ($_POST['dni']));
$telefono = trim((string) ($_POST['telefono']));
$direccion = trim((string) ($_POST['direccion']));

$consulta = $MClientes->Modificar_Cliente($id, $nombre, $dni, $telefono, $direccion);
echo $consulta;
?>
