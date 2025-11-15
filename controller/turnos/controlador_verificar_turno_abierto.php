<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Verificar_Turno_Abierto($id_usuario);
echo $consulta;
?>
