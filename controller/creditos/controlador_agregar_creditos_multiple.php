<?php
require_once '../../model/model_creditos.php';

header('Content-Type: application/json');

$MCreditos = new Modelo_Creditos();

// Validar que lleguen los créditos
if (!isset($_POST['creditos'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron créditos para guardar']);
    exit;
}

// Decodificar el JSON de créditos
$creditos = json_decode($_POST['creditos'], true);

if (!is_array($creditos) || count($creditos) === 0) {
    echo json_encode(['success' => false, 'message' => 'Formato de datos inválido']);
    exit;
}

// Contador de créditos guardados
$guardados = 0;
$errores = [];

// Procesar cada crédito
foreach ($creditos as $index => $credito) {
    // Validar datos obligatorios
    if (!isset($credito['id_cliente']) || !isset($credito['numero_vale']) || !isset($credito['monto'])) {
        $errores[] = "Crédito " . ($index + 1) . ": Faltan datos obligatorios";
        continue;
    }
    
    $id_cliente = htmlspecialchars($credito['id_cliente'], ENT_QUOTES, 'UTF-8');
    $numero_vale = htmlspecialchars($credito['numero_vale'], ENT_QUOTES, 'UTF-8');
    $monto = htmlspecialchars($credito['monto'], ENT_QUOTES, 'UTF-8');
    $fecha_registro = isset($credito['fecha_registro']) && !empty($credito['fecha_registro']) 
        ? htmlspecialchars($credito['fecha_registro'], ENT_QUOTES, 'UTF-8') 
        : date('Y-m-d'); // Si no viene fecha, usar hoy
    $observaciones = ''; // No tenemos campo de observaciones en la tabla
    $placa = isset($credito['placa']) ? strtoupper(trim(htmlspecialchars($credito['placa'], ENT_QUOTES, 'UTF-8'))) : '';
    
    // Agregar crédito
    $resultado = $MCreditos->Agregar_Credito_Manual($id_cliente, $numero_vale, $monto, $fecha_registro, $observaciones, $placa);
    
    if ($resultado > 0) {
        $guardados++;
    } else {
        $errores[] = "Crédito " . ($index + 1) . " (Vale: $numero_vale): Error al guardar";
    }
}

// Preparar respuesta
if ($guardados > 0) {
    $mensaje = "Se guardaron $guardados crédito(s) correctamente";
    if (count($errores) > 0) {
        $mensaje .= ". Errores: " . implode(", ", $errores);
    }
    echo json_encode(['success' => true, 'message' => $mensaje, 'guardados' => $guardados, 'errores' => $errores]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo guardar ningún crédito. ' . implode(", ", $errores)]);
}
?>
