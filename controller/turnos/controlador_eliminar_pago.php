<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$id_pago = htmlspecialchars($_POST['id_pago'], ENT_QUOTES, 'UTF-8');

$consulta = $MTurnos->Eliminar_Pago_Reporte($id_pago);
echo $consulta;
?>
