<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_lectura = htmlspecialchars($_POST['id_lectura'], ENT_QUOTES, 'UTF-8');
$lectura_actual = htmlspecialchars($_POST['lectura_actual'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Actualizar_Lectura_Turno($id_lectura, $lectura_actual);
echo $consulta;
?>
