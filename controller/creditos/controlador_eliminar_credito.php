<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = trim((string) ($_POST['id_credito']));

$consulta = $MCreditos->Eliminar_Credito($id_credito);
echo $consulta;
?>
