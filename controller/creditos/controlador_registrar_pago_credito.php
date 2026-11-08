<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito       = trim((string) ($_POST['id_credito']));
$id_tipo_pago     = trim((string) ($_POST['id_tipo_pago']));
$codigo_operacion = trim((string) ($_POST['codigo_operacion']));
$monto_pagado     = trim((string) ($_POST['monto_pagado']));
$descuento        = isset($_POST['descuento']) ? floatval($_POST['descuento']) : 0;
$fecha_pago       = trim((string) ($_POST['fecha_pago']));
$id_usuario       = trim((string) ($_POST['id_usuario']));
$observaciones    = trim((string) ($_POST['observaciones']));

$consulta = $MCreditos->Registrar_Pago_Credito($id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado, $descuento, $fecha_pago, $id_usuario, $observaciones);
echo $consulta;
?>
