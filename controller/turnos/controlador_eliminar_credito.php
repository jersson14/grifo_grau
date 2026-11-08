<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('credito', $_POST['id_credito'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_credito = trim((string) ($_POST['id_credito']));

$consulta = $MTurnos->Eliminar_Credito($id_credito);
echo $consulta;
?>
