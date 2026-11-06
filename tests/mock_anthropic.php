<?php
/**
 * Imitación local de la API de mensajes, para probar el chatbot sin clave ni costo.
 * Registra cada petición recibida en MOCK_LOG. Se levanta con: php -S 127.0.0.1:8765 tests/mock_anthropic.php
 */
$cuerpo = file_get_contents('php://input');
$peticion = json_decode($cuerpo, true) ?: [];

file_put_contents(getenv('MOCK_LOG') ?: sys_get_temp_dir() . '/mock_anthropic.log', json_encode([
    'ruta'     => $_SERVER['REQUEST_URI'],
    // El SDK de PHP envía la clave como Authorization: Bearer.
    'api_key'  => $_SERVER['HTTP_X_API_KEY'] ?? preg_replace('/^Bearer /', '', $_SERVER['HTTP_AUTHORIZATION'] ?? ''),
    'beta'     => $_SERVER['HTTP_ANTHROPIC_BETA'] ?? '',
    'peticion' => $peticion,
]) . "\n", FILE_APPEND);

header('Content-Type: application/json');

$ultimo    = end($peticion['messages']);
$contenido = $ultimo['content'] ?? '';
$resultado = null;
if (is_array($contenido)) {
    foreach ($contenido as $bloque) {
        if (($bloque['type'] ?? '') === 'tool_result') {
            $resultado = $bloque;
        }
    }
}

$base = ['id' => 'msg_prueba', 'type' => 'message', 'role' => 'assistant', 'model' => $peticion['model'] ?? '',
         'stop_sequence' => null, 'usage' => ['input_tokens' => 1000, 'output_tokens' => 100, 'cache_read_input_tokens' => 500, 'cache_creation_input_tokens' => 0]];

$texto = is_string($contenido) ? $contenido : '';

if (strpos($texto, 'FORZAR_ERROR') !== false) {
    http_response_code(500);
    echo json_encode(['type' => 'error', 'error' => ['type' => 'api_error', 'message' => 'fallo simulado']]);
    exit;
}
if (strpos($texto, 'FORZAR_RECHAZO') !== false) {
    echo json_encode($base + ['stop_reason' => 'refusal', 'content' => []]);
    exit;
}

if ($resultado === null) {
    $herramienta = strpos($texto, 'RANKING') !== false ? 'ranking_griferos' : 'ventas_por_periodo';
    $entrada = ['desde' => date('Y-m-d'), 'hasta' => date('Y-m-d')];
    if ($herramienta === 'ventas_por_periodo') {
        $entrada['agrupar_por_dia'] = false;
    }
    echo json_encode($base + ['stop_reason' => 'tool_use', 'content' => [
        ['type' => 'text', 'text' => 'Voy a consultar.'],
        ['type' => 'tool_use', 'id' => 'toolu_prueba', 'name' => $herramienta, 'input' => $entrada],
    ]]);
    exit;
}

$detalle = is_string($resultado['content']) ? $resultado['content'] : json_encode($resultado['content']);
echo json_encode($base + ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'RESULTADO: ' . $detalle]]]);
