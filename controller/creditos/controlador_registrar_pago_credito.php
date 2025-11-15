<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');
$id_tipo_pago = htmlspecialchars($_POST['id_tipo_pago'], ENT_QUOTES, 'UTF-8');
$codigo_operacion = htmlspecialchars($_POST['codigo_operacion'], ENT_QUOTES, 'UTF-8');
$monto_pagado = htmlspecialchars($_POST['monto_pagado'], ENT_QUOTES, 'UTF-8');
$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');
$observaciones = htmlspecialchars($_POST['observaciones'], ENT_QUOTES, 'UTF-8');

$consulta = $MCreditos->Registrar_Pago_Credito($id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado, $id_usuario, $observaciones);
echo $consulta;
?>
