<?php
// Archivo de prueba para verificar la API de DNI

$dni = '12345678'; // DNI de prueba
$token = 'sk_11678.HdeHGplwfvrLVqBOrFwH2fspxdwFoTOT';

echo "<h2>Probando API de RENIEC</h2>";
echo "<p>DNI: $dni</p>";

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
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if(curl_errno($curl)){
    echo "<p style='color:red'>Error CURL: " . curl_error($curl) . "</p>";
} else {
    echo "<p>HTTP Code: $httpCode</p>";
    echo "<h3>Respuesta:</h3>";
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
    
    echo "<h3>Respuesta RAW:</h3>";
    echo "<pre>$response</pre>";
}

curl_close($curl);
?>
