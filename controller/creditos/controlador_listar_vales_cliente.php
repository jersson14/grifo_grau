<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_cliente = isset($_POST['id_cliente']) ? $_POST['id_cliente'] : 0;
$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : 'PENDIENTE';

$consulta = $MCreditos->Listar_Vales_Cliente($id_cliente, $filtro_estado);

echo json_encode($consulta);
?>
