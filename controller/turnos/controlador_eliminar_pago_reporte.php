<?php
require_once __DIR__ . '/../_bootstrap.php';
exigir_acceso_turno('pago', $_POST['id_pago'] ?? 0);
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_pago = $_POST['id_pago'];

$resultado = $MTurnos->Eliminar_Pago_Reporte($id_pago);

echo $resultado;
?>
