<?php
require_once '../../model/model_creditos.php';

header('Content-Type: application/json');

$MCreditos = new Modelo_Creditos();

// Validar que lleguen los datos obligatorios
if (!isset($_POST['id_cliente']) || !isset($_POST['numero_vale']) || !isset($_POST['monto'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros obligatorios (Cliente, N° Vale y Monto)']);
    exit;
}

$id_cliente = htmlspecialchars($_POST['id_cliente'], ENT_QUOTES, 'UTF-8');
$numero_vale = htmlspecialchars($_POST['numero_vale'], ENT_QUOTES, 'UTF-8');
$monto = htmlspecialchars($_POST['monto'], ENT_QUOTES, 'UTF-8');
$fecha_vencimiento = isset($_POST['fecha_vencimiento']) && !empty($_POST['fecha_vencimiento']) ? htmlspecialchars($_POST['fecha_vencimiento'], ENT_QUOTES, 'UTF-8') : '';
$observaciones = isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones'], ENT_QUOTES, 'UTF-8') : '';

// Agregar crédito (sin id_reporte, es manual)
$consulta = $MCreditos->Agregar_Credito_Manual($id_cliente, $numero_vale, $monto, $fecha_vencimiento, $observaciones);

if ($consulta > 0) {
    echo json_encode(['success' => true, 'message' => 'Crédito agregado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al agregar el crédito']);
}
?>
