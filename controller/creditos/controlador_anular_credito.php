<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = trim((string) ($_POST['id_credito']));
$motivo = trim((string) ($_POST['motivo']));

$consulta = $MCreditos->Anular_Credito($id_credito, $motivo);
echo $consulta;
?>
