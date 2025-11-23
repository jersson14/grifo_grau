<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : 'PENDIENTE';

$consulta = $MCreditos->Listar_Creditos_Por_Cliente($filtro_estado);

echo json_encode($consulta);
?>
