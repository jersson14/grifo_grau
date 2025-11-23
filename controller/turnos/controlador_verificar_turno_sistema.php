<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

// Verificar si hay algún turno abierto en el sistema
$total = $MTurnos->Verificar_Turno_Abierto_Sistema();

echo $total;
?>
