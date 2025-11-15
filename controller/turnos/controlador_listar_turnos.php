<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$filtro_fecha_inicio = isset($_POST['filtro_fecha_inicio']) ? $_POST['filtro_fecha_inicio'] : null;
$filtro_fecha_fin = isset($_POST['filtro_fecha_fin']) ? $_POST['filtro_fecha_fin'] : null;
$filtro_usuario = isset($_POST['filtro_usuario']) ? $_POST['filtro_usuario'] : null;
$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : null;

$consulta = $MTurnos->Listar_Turnos($filtro_fecha_inicio, $filtro_fecha_fin, $filtro_usuario, $filtro_estado);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
