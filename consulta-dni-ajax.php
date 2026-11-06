<?php
require_once __DIR__ . '/controller/_bootstrap.php';
require_once __DIR__ . '/config/decolecta.php';

$dni = $_POST['dni'] ?? '';

if (!preg_match('/^\d{8}$/', $dni)) {
    echo json_encode(1); // El frontend interpreta 1 como "debe tener 8 dígitos"
    exit;
}

echo consultar_decolecta('reniec/dni?numero=' . $dni);
