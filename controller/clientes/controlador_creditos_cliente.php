<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id_cliente = trim((string) ($_POST['id_cliente']));

$consulta = $MClientes->Listar_Creditos_Cliente($id_cliente);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
