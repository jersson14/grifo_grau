<?php
session_start();
require_once '../../model/model_turnos.php';

// Validar que el usuario esté logueado
if (!isset($_SESSION['S_ID'])) {
    echo json_encode(array('success' => false, 'message' => 'Sesión no válida'));
    exit;
}

// Validar que sea ADMINISTRADOR
if ($_SESSION['S_ROL'] != 'ADMINISTRADOR') {
    echo json_encode(array('success' => false, 'message' => 'No tiene permisos para eliminar turnos. Solo los administradores pueden realizar esta acción.'));
    exit;
}

// Validar parámetros
if (!isset($_POST['id_reporte']) || !isset($_POST['motivo'])) {
    echo json_encode(array('success' => false, 'message' => 'Faltan parámetros requeridos'));
    exit;
}

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');
$motivo = htmlspecialchars($_POST['motivo'], ENT_QUOTES, 'UTF-8');
$id_usuario = $_SESSION['S_ID'];

// Validar que el motivo no esté vacío
if (empty(trim($motivo))) {
    echo json_encode(array('success' => false, 'message' => 'Debe proporcionar un motivo para eliminar el turno'));
    exit;
}

// Instanciar modelo
$MTurnos = new Modelo_Turnos();

// Eliminar turno
$resultado = $MTurnos->Eliminar_Turno($id_reporte, $id_usuario, $motivo);

// Retornar resultado
header('Content-Type: application/json');
echo json_encode($resultado);
?>
