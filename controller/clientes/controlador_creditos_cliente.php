<?php
require_once '../../model/model_clientes_grifo.php';

$MClientes = new Modelo_Clientes_Grifo();

$id_cliente = htmlspecialchars($_POST['id_cliente'], ENT_QUOTES, 'UTF-8');

$consulta = $MClientes->Listar_Creditos_Cliente($id_cliente);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
