<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('lectura', $_POST['id_lectura'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_lectura = trim((string) ($_POST['id_lectura']));
$lectura_actual = trim((string) ($_POST['lectura_actual']));

$consulta = $MTurnos->Actualizar_Lectura_Turno($id_lectura, $lectura_actual);
echo $consulta;
?>
