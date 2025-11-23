<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

// Obtener el turno abierto del sistema (sin importar el usuario)
$turno = $MTurnos->Obtener_Info_Turno_Abierto_Sistema();

if ($turno) {
    echo json_encode($turno);
} else {
    echo '0';
}
?>
