<?php

$ruc = $_POST['ruc'] ?? null;
$token = 'sk_11678.HdeHGplwfvrLVqBOrFwH2fspxdwFoTOT'; // Tu token real

// Validar longitud del RUC
if (!$ruc || strlen($ruc) != 11) {
    echo json_encode(['error' => 'El RUC debe tener 11 dígitos.']);
    exit;
}

// Inicializar CURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.decolecta.com/v1/sunat/ruc/full?numero=' . $ruc,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ],
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo json_encode(['error' => curl_error($curl)]);
    curl_close($curl);
    exit;
}

curl_close($curl);

// Mostrar la respuesta en JSON
echo $response;
