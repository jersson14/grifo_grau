<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('reporte', $_POST['id_reporte'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Obtener_Pagos_Reporte($id_reporte);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
