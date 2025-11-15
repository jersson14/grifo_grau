<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$consulta = $MCreditos->Top_Clientes_Deuda(10);
echo json_encode($consulta);
?>
