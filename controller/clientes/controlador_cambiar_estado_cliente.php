<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$estado = htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8');

$consulta = $MClientes->Cambiar_Estado_Cliente($id, $estado);
echo $consulta;
?>
