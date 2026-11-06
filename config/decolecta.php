<?php
require_once __DIR__ . '/env.php';

/** Consulta la API de DNI/RUC. El token se lee de .env y no llega al navegador. */
function consultar_decolecta(string $ruta): string
{
    $token = env('DECOLECTA_TOKEN', '');
    if ($token === '') {
        return json_encode(['error' => 'La consulta DNI/RUC no está configurada.']);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.decolecta.com/v1/' . $ruta,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $token],
    ]);
    $respuesta = curl_exec($curl);

    if ($respuesta === false) {
        error_log('Error consultando decolecta: ' . curl_error($curl));
        curl_close($curl);
        return json_encode(['error' => 'No se pudo consultar el servicio.']);
    }
    curl_close($curl);
    return $respuesta;
}
