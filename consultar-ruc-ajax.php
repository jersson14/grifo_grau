<?php
require_once __DIR__ . '/controller/_bootstrap.php';
require_once __DIR__ . '/config/decolecta.php';

$ruc = $_POST['ruc'] ?? '';

if (!preg_match('/^\d{11}$/', $ruc)) {
    echo json_encode(['error' => 'El RUC debe tener 11 dígitos.']);
    exit;
}

echo consultar_decolecta('sunat/ruc/full?numero=' . $ruc);
