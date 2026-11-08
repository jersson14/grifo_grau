<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

header('Content-Type: application/json');

$MCreditos = new Modelo_Creditos();

// Validar que lleguen los datos
if (!isset($_POST['id_credito'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del crédito']);
    exit;
}

$id_credito = trim((string) ($_POST['id_credito']));
$numero_vale = isset($_POST['numero_vale']) ? trim((string) ($_POST['numero_vale'])) : null;
$monto = isset($_POST['monto']) ? trim((string) ($_POST['monto'])) : null;
$fecha_vencimiento = isset($_POST['fecha_vencimiento']) ? trim((string) ($_POST['fecha_vencimiento'])) : null;
$observaciones = isset($_POST['observaciones']) ? trim((string) ($_POST['observaciones'])) : null;
$estado = isset($_POST['estado']) ? trim((string) ($_POST['estado'])) : null;

// Editar crédito
$consulta = $MCreditos->Editar_Credito($id_credito, $numero_vale, $monto, $fecha_vencimiento, $observaciones, $estado);

if ($consulta > 0) {
    echo json_encode(['success' => true, 'message' => 'Crédito actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el crédito']);
}
?>
