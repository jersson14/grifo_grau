<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');

$consulta = $MClientes->Ver_Detalle_Cliente($id);
echo json_encode($consulta);
?>
