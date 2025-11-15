<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');
$motivo = htmlspecialchars($_POST['motivo'], ENT_QUOTES, 'UTF-8');

$consulta = $MCreditos->Anular_Credito($id_credito, $motivo);
echo $consulta;
?>
