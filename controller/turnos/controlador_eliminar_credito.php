<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Eliminar_Credito($id_credito);
echo $consulta;
?>
