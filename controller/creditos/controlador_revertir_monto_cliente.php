<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
$monto      = isset($_POST['monto'])      ? floatval($_POST['monto'])    : 0;

if (!$id_cliente || $monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$result = $MCreditos->Revertir_Monto_Cliente($id_cliente, $monto);
echo json_encode($result);
?>
