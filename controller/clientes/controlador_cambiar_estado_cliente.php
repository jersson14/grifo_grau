<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = trim((string) ($_POST['id']));
$estado = trim((string) ($_POST['estado']));

$consulta = $MClientes->Cambiar_Estado_Cliente($id, $estado);
echo $consulta;
?>
