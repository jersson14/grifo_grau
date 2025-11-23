<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_pago = $_POST['id_pago'];

$resultado = $MTurnos->Eliminar_Pago_Reporte($id_pago);

echo $resultado;
?>
