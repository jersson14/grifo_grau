<?php

$dni = $_POST['dni'];
$token = 'sk_11678.HdeHGplwfvrLVqBOrFwH2fspxdwFoTOT'; // Tu token real

if(strlen($dni) != 8){
    echo json_encode(1); // Validación de 8 dígitos
    exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.decolecta.com/v1/reniec/dni?numero=' . $dni,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
  ),
));

$response = curl_exec($curl);

if(curl_errno($curl)){
    echo json_encode(['error' => curl_error($curl)]);
    exit;
}

curl_close($curl);

// Mostrar la respuesta como JSON
echo $response;
