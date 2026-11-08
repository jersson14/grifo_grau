<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id = trim((string) ($_POST['id']));

$consulta = $MClientes->Ver_Detalle_Cliente($id);
echo json_encode($consulta);
?>
