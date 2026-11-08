<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_chatbot.php';

set_time_limit(120);

$chatbot = new Modelo_Chatbot((int) $_SESSION['S_ID'], (string) $_SESSION['S_ROL']);
if (!$chatbot->Puede_Usar()) {
    denegar_acceso(403, 'Su rol no tiene acceso al chatbot.');
}

// Con flujo=1 la respuesta llega por líneas JSON: primero el avance ({"avance": "..."}) y al final el resultado.
// Si el servidor junta la salida, el navegador igual recibe todas las líneas al terminar.
$flujo = ($_POST['flujo'] ?? '') === '1';
if ($flujo) {
    header('Content-Type: application/x-ndjson; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
}

$enviar = function (array $dato) use ($flujo): void {
    echo json_encode($dato, JSON_UNESCAPED_UNICODE), $flujo ? "\n" : '';
    if ($flujo) {
        flush();
    }
};

$pregunta = trim((string) ($_POST['pregunta'] ?? ''));
if ($pregunta === '' || mb_strlen($pregunta) > 1000) {
    $enviar(['ok' => false, 'respuesta' => 'Escriba una pregunta de hasta 1000 caracteres.']);
    exit;
}

// La sesión se libera durante la llamada para no bloquear otras pantallas del usuario.
session_write_close();

$avance = $flujo ? fn(string $texto) => $enviar(['avance' => $texto]) : null;
$enviar($chatbot->Preguntar($pregunta, $chatbot->Contexto(), $avance));
