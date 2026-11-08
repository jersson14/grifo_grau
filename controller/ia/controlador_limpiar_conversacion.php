<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_chatbot.php';

// Oculta la conversación del usuario; las consultas siguen en la bitácora de consumo.
(new Modelo_Chatbot((int) $_SESSION['S_ID'], (string) $_SESSION['S_ROL']))->Nueva_Conversacion();
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
