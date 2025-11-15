<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Obtener_Lecturas_Turno($id_reporte);
echo json_encode($consulta);
?>
