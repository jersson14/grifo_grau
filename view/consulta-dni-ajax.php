<?php


$token = 'apis-token-9261.xB1hHERpBHmMXkoEqotp7E8MrfEWZuYEs';
$dni = $_POST['dni'];

// Iniciar llamada a API
$curl = curl_init();

// Buscar dni
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $dni,
  CURLOPT_SSL_VERIFYPEER=>0,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYPEER => 0,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 2,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Referer: https://apis.net.pe/consulta-dni-api',
    'Authorization: Bearer ' . $token
  ),
));


$response = curl_exec($curl);
if(curl_errno($curl)){
    echo 'Error del scraper:'.curl_error($curl);
    exit;
}

curl_close($curl);

// Datos listos para usar
echo $response;

// // Datos
// echo "El archivo es accesible";

// $token = 'apis-token-9261.xB1hHERpBHmMXkoEqotp7E8MrfEWZuYEs';
// $dni = $_POST["dni"];

// // Iniciar llamada a API
// $curl = curl_init();

// // Buscar dni
// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $dni,
//   CURLOPT_SSL_VERIFYPEER=>0,
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_SSL_VERIFYPEER => 0,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 2,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_CUSTOMREQUEST => 'GET',
//   CURLOPT_HTTPHEADER => array(
//     'Referer: https://apis.net.pe/consulta-dni-api',
//     'Authorization: Bearer ' . $token
//   ),
// ));

// $response = curl_exec($curl);

// curl_close($curl);
// // Datos listos para usar
// echo $response;