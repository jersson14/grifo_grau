<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$numero = $MTurnos->Generar_Numero_Documento();
echo $numero;
?>
