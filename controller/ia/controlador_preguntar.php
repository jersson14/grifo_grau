<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_chatbot.php';

header('Content-Type: application/json; charset=utf-8');
set_time_limit(120);

$chatbot = new Modelo_Chatbot((int) $_SESSION['S_ID'], (string) $_SESSION['S_ROL']);
if (!$chatbot->Puede_Usar()) {
    denegar_acceso(403, 'Su rol no tiene acceso al chatbot.');
}

$pregunta = trim((string) ($_POST['pregunta'] ?? ''));
if ($pregunta === '' || mb_strlen($pregunta) > 1000) {
    echo json_encode(['ok' => false, 'respuesta' => 'Escriba una pregunta de hasta 1000 caracteres.']);
    exit;
}

// La sesión se libera durante la llamada para no bloquear otras pantallas del usuario.
$historial = $_SESSION['IA_HISTORIAL'] ?? [];
session_write_close();

$resultado = $chatbot->Preguntar($pregunta, $historial);

if ($resultado['ok']) {
    session_start();
    $historial[] = ['role' => 'user', 'content' => $pregunta];
    $historial[] = ['role' => 'assistant', 'content' => $resultado['respuesta']];
    $_SESSION['IA_HISTORIAL'] = array_slice($historial, -10);
}

echo json_encode($resultado);
