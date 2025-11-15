<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$filtro_cliente = isset($_POST['filtro_cliente']) ? $_POST['filtro_cliente'] : null;
$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : null;

$consulta = $MCreditos->Listar_Todos_Creditos($filtro_cliente, $filtro_estado);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
