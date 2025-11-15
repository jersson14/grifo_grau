<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');

$consulta = $MCreditos->Obtener_Detalle_Credito($id_credito);
echo json_encode($consulta);
?>
