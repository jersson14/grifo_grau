<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Obtener_Creditos_Reporte($id_reporte);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
