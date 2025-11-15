<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$consulta = $MClientes->Listar_Clientes_Activos();
echo json_encode($consulta);
?>
