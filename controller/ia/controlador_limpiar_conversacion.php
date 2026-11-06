<?php
require_once __DIR__ . '/../_bootstrap.php';

unset($_SESSION['IA_HISTORIAL']);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
