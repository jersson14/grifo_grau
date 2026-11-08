<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_usuario = trim((string) ($_POST['id_usuario']));

$consulta = $MTurnos->Verificar_Turno_Abierto($id_usuario);
echo $consulta;
?>
