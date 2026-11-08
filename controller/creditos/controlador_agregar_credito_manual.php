<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

header('Content-Type: application/json');

$MCreditos = new Modelo_Creditos();

// Validar que lleguen los datos obligatorios
if (!isset($_POST['id_cliente']) || !isset($_POST['numero_vale']) || !isset($_POST['monto'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros obligatorios (Cliente, N° Vale y Monto)']);
    exit;
}

$id_cliente = trim((string) ($_POST['id_cliente']));
$numero_vale = trim((string) ($_POST['numero_vale']));
$monto = trim((string) ($_POST['monto']));
$fecha_registro = isset($_POST['fecha_registro']) && !empty($_POST['fecha_registro']) ? trim((string) ($_POST['fecha_registro'])) : date('Y-m-d');
$observaciones = isset($_POST['observaciones']) ? trim((string) ($_POST['observaciones'])) : '';
$placa = isset($_POST['placa']) ? strtoupper(trim(trim((string) ($_POST['placa'])))) : '';

// Agregar crédito (sin id_reporte, es manual)
$consulta = $MCreditos->Agregar_Credito_Manual($id_cliente, $numero_vale, $monto, $fecha_registro, $observaciones, $placa);

if ($consulta > 0) {
    echo json_encode(['success' => true, 'message' => 'Crédito agregado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al agregar el crédito']);
}
?>
