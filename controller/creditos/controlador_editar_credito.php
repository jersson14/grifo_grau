<?php
require_once '../../model/model_creditos.php';

header('Content-Type: application/json');

$MCreditos = new Modelo_Creditos();

// Validar que lleguen los datos
if (!isset($_POST['id_credito'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del crédito']);
    exit;
}

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');
$numero_vale = isset($_POST['numero_vale']) ? htmlspecialchars($_POST['numero_vale'], ENT_QUOTES, 'UTF-8') : null;
$monto = isset($_POST['monto']) ? htmlspecialchars($_POST['monto'], ENT_QUOTES, 'UTF-8') : null;
$fecha_vencimiento = isset($_POST['fecha_vencimiento']) ? htmlspecialchars($_POST['fecha_vencimiento'], ENT_QUOTES, 'UTF-8') : null;
$observaciones = isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones'], ENT_QUOTES, 'UTF-8') : null;
$estado = isset($_POST['estado']) ? htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8') : null;

// Editar crédito
$consulta = $MCreditos->Editar_Credito($id_credito, $numero_vale, $monto, $fecha_vencimiento, $observaciones, $estado);

if ($consulta > 0) {
    echo json_encode(['success' => true, 'message' => 'Crédito actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el crédito']);
}
?>
