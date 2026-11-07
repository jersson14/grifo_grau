<?php
/**
 * Recibe la solicitud de crédito para flotas de la página pública. Es una ruta pública:
 * comprueba aquí el módulo, el token del formulario y el campo trampa contra robots.
 * Responde JSON al envío con JavaScript y redirige al formulario sin él.
 */
require_once dirname(__DIR__) . '/controller/_bootstrap.php';
require_once dirname(__DIR__) . '/model/model_pagina_publica.php';

$quiereJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

$responder = function (bool $ok, string $mensaje = '', array $errores = []) use ($quiereJson): void {
    if ($quiereJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok, 'message' => $mensaje, 'errores' => (object) $errores]);
        exit;
    }
    $_SESSION['FLOTA_AVISO'] = $ok ? 'ok' : [
        'mensaje' => $mensaje,
        'errores' => $errores,
        'datos'   => array_intersect_key($_POST, array_flip(['empresa', 'ruc', 'contacto', 'telefono', 'correo', 'unidades', 'galones_mes', 'mensaje'])),
    ];
    header('Location: ../index.php#flotas');
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !modulo_activo('pagina_publica')) {
    http_response_code(404);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !hash_equals(token_csrf(), $token)) {
    $responder(false, 'La página caducó. Recárgala e inténtalo de nuevo.');
}

// Un robot suele llenar todos los campos, incluido el oculto: se le responde como si nada.
if (trim((string) ($_POST['sitio_web'] ?? '')) !== '') {
    $responder(true);
}

try {
    (new Modelo_Pagina_Publica())->Registrar_Solicitud($_POST, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $responder(true);
} catch (Solicitud_Invalida $e) {
    $responder(false, $e->getMessage(), $e->errores);
} catch (DomainException $e) {
    $responder(false, $e->getMessage());
} catch (Throwable $e) {
    error_log('Solicitud de flota: ' . $e->getMessage());
    $responder(false, 'No se pudo enviar la solicitud. Llámanos o escríbenos por WhatsApp.');
}
