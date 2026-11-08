<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_chatbot.php';

header('Content-Type: application/json; charset=utf-8');

$chatbot = new Modelo_Chatbot((int) $_SESSION['S_ID'], (string) $_SESSION['S_ROL']);
if (!$chatbot->Puede_Usar()) {
    denegar_acceso(403, 'Su rol no tiene acceso al chatbot.');
}
// Solo la conversación del usuario de la sesión.
echo json_encode(['data' => $chatbot->Historial()]);
