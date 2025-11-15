<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$consulta = $MCreditos->Obtener_Resumen_Creditos();
echo json_encode($consulta);
?>
