<?php
/**
 * Imitación local de la API de Nubefact, para probar sin cuenta. No sustituye la prueba en su modo demo:
 * reproduce el formato de sus ejemplos, no sus validaciones reales.
 *
 *   MOCK_DIR=<carpeta> php -S 127.0.0.1:8766 tests/mock_nubefact.php
 *
 * En MOCK_DIR: "modo" elige la conducta (ok, sunat_pendiente, caida, caida_tras_registrar, rechazo);
 * "documentos.json" guarda lo emitido y "peticiones.log" registra cada petición.
 */
$dir = rtrim((string) getenv('MOCK_DIR'), '/\\') . '/';
$modo = is_file($dir . 'modo') ? trim(file_get_contents($dir . 'modo')) : 'ok';
$datos = json_decode((string) file_get_contents('php://input'), true) ?: [];
$docs = is_file($dir . 'documentos.json') ? json_decode(file_get_contents($dir . 'documentos.json'), true) : [];

file_put_contents($dir . 'peticiones.log', json_encode(['auth' => $_SERVER['HTTP_AUTHORIZATION'] ?? '', 'datos' => $datos]) . "\n", FILE_APPEND);
header('Content-Type: application/json');

if (($_SERVER['HTTP_AUTHORIZATION'] ?? '') !== 'Token token="token-de-prueba"') {
    http_response_code(401);
    echo json_encode(['errors' => 'Token inválido', 'codigo' => 10]);
    exit;
}

$clave = ($datos['serie'] ?? '') . '-' . ($datos['numero'] ?? '');
$respuesta = fn(bool $aceptada) => [
    'tipo_de_comprobante' => $datos['tipo_de_comprobante'] ?? '', 'serie' => $datos['serie'] ?? '', 'numero' => $datos['numero'] ?? '',
    'enlace' => "https://www.nubefact.test/cpe/$clave", 'enlace_del_pdf' => "https://www.nubefact.test/cpe/$clave.pdf",
    'enlace_del_xml' => "https://www.nubefact.test/cpe/$clave.xml", 'enlace_del_cdr' => "https://www.nubefact.test/cpe/$clave.cdr",
    'aceptada_por_sunat' => $aceptada, 'sunat_description' => $aceptada ? 'La Factura ha sido aceptada' : '', 'sunat_note' => '', 'codigo_hash' => 'hash', 'cadena_para_codigo_qr' => 'qr',
];
$guardar = function () use (&$docs, $dir) { file_put_contents($dir . 'documentos.json', json_encode($docs)); };

switch ($datos['operacion'] ?? '') {
    case 'generar_comprobante':
        if ($modo === 'caida') { http_response_code(503); echo 'Servicio no disponible'; exit; }
        if ($modo === 'rechazo') { echo json_encode(['errors' => 'El total no coincide', 'codigo' => 20]); exit; }
        if (isset($docs[$clave])) { echo json_encode(['errors' => 'Este documento ya existe', 'codigo' => 23]); exit; }
        $docs[$clave] = $modo === 'sunat_pendiente' ? 'enviado' : 'aceptado';
        $guardar();
        // El documento se registró, pero la respuesta nunca llega: es el caso que podría duplicar.
        if ($modo === 'caida_tras_registrar') { http_response_code(502); echo 'Bad gateway'; exit; }
        echo json_encode($respuesta($modo !== 'sunat_pendiente'));
        break;
    case 'consultar_comprobante':
        if ($modo === 'caida') { http_response_code(503); exit; }
        if (!isset($docs[$clave])) { echo json_encode(['errors' => 'Documento no encontrado', 'codigo' => 24]); exit; }
        if ($modo === 'ok') { $docs[$clave] = 'aceptado'; $guardar(); }
        echo json_encode($respuesta($docs[$clave] === 'aceptado'));
        break;
    case 'generar_anulacion':
        if (!isset($docs[$clave])) { echo json_encode(['errors' => 'Documento no encontrado', 'codigo' => 24]); exit; }
        $docs[$clave . ':baja'] = 'pendiente';
        $guardar();
        echo json_encode(['aceptada_por_sunat' => false, 'sunat_ticket_numero' => '123']);
        break;
    case 'consultar_anulacion':
        echo json_encode(['aceptada_por_sunat' => $modo === 'ok']);
        break;
    default:
        echo json_encode(['errors' => 'Operación desconocida', 'codigo' => 21]);
}
