<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_usuario = trim((string) ($_POST['id_usuario']));

$consulta = $MTurnos->Obtener_Turno_Abierto($id_usuario);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo '0';
}
?>
