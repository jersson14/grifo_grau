<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_turnos.php';

// Retornar respuesta en formato JSON para mejor manejo de errores
header('Content-Type: application/json');

$MTurnos = new Modelo_Turnos();

// Verificar que los datos lleguen correctamente
if (!isset($_POST['numero_documento']) || !isset($_POST['id_usuario']) || !isset($_POST['turno']) || 
    !isset($_POST['fecha']) || !isset($_POST['hora_inicio']) || !isset($_POST['hora_fin'])) {
    error_log("ERROR: Faltan parámetros en la solicitud");
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros obligatorios']);
    exit;
}

$numero_documento = trim((string) ($_POST['numero_documento']));
$id_usuario = trim((string) ($_POST['id_usuario']));
$turno = trim((string) ($_POST['turno']));
$fecha = trim((string) ($_POST['fecha']));
$hora_inicio = trim((string) ($_POST['hora_inicio']));
$hora_fin = trim((string) ($_POST['hora_fin']));

// Log para depuración
error_log("=== ABRIR TURNO ===");
error_log("Grifero ID: $id_usuario");
error_log("Fecha: $fecha");
error_log("Turno: $turno");

// REGLA DE NEGOCIO: Solo puede haber UN turno abierto a la vez en todo el sistema
$hay_turno_abierto = $MTurnos->Verificar_Turno_Abierto_Sistema();
if ($hay_turno_abierto > 0) {
    $info_turno = $MTurnos->Obtener_Info_Turno_Abierto_Sistema();
    $mensaje = 'Ya hay un turno abierto en el sistema';
    if ($info_turno) {
        $mensaje .= ' (' . $info_turno['turno'] . ' - ' . $info_turno['grifero_nombre'] . ')';
    }
    $mensaje .= '. Debe cerrarlo antes de abrir uno nuevo.';
    
    error_log("ERROR: " . $mensaje);
    echo json_encode(['success' => false, 'message' => $mensaje]);
    exit;
}

// Abrir turno
$id_reporte = $MTurnos->Abrir_Turno($numero_documento, $id_usuario, $turno, $fecha, $hora_inicio, $hora_fin);

if ($id_reporte > 0) {
    error_log("SUCCESS: Turno abierto con ID: $id_reporte");
    echo json_encode(['success' => true, 'id_reporte' => $id_reporte, 'message' => 'Turno abierto correctamente']);
} else {
    error_log("ERROR: No se pudo abrir el turno");
    echo json_encode(['success' => false, 'message' => 'Error al insertar el turno en la base de datos']);
}
?>
