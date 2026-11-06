<?php
/**
 * Control de acceso central. Todo controlador y vista lo carga en su primera línea.
 *
 * Al incluirse: inicia la sesión con cookies seguras, comprueba sesión, rol (ver
 * config/permisos.php, que niega por defecto) y token CSRF en cada POST.
 */
require_once dirname(__DIR__) . '/config/env.php';

date_default_timezone_set('America/Lima');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

function iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

/** Responde con un error y termina. Las vistas reciben HTML; el resto, JSON. */
function denegar_acceso(int $codigo, string $mensaje): void
{
    if ($codigo === 401 && ruta_script_actual() === 'view/index.php') {
        header('Location: ../index.php');
        exit;
    }

    http_response_code($codigo);
    $esVista = strpos(ruta_script_actual(), 'view/') === 0;
    if ($esVista) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<div class="alert alert-danger m-3">' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</div>';
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $mensaje]);
    }
    exit;
}

/** Ruta del script en ejecución relativa a la raíz del proyecto, con barras "/". */
function ruta_script_actual(): string
{
    $raiz   = str_replace('\\', '/', dirname(__DIR__)) . '/';
    $script = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']);
    return strpos($script, $raiz) === 0 ? substr($script, strlen($raiz)) : $script;
}

function ruta_coincide(string $ruta, array $patrones): bool
{
    foreach ($patrones as $patron) {
        if ($patron === $ruta) {
            return true;
        }
        if (substr($patron, -1) === '*' && strpos($ruta, substr($patron, 0, -1)) === 0) {
            return true;
        }
    }
    return false;
}

function token_csrf(): string
{
    if (empty($_SESSION['CSRF'])) {
        $_SESSION['CSRF'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['CSRF'];
}

function verificar_acceso(): void
{
    $ruta     = ruta_script_actual();
    $permisos = require dirname(__DIR__) . '/config/permisos.php';

    iniciar_sesion_segura();

    if (ruta_coincide($ruta, $permisos['publico'])) {
        return;
    }

    if (empty($_SESSION['S_ID'])) {
        denegar_acceso(401, 'Su sesión ha expirado. Vuelva a iniciar sesión.');
    }

    $limite = (int) env('SESSION_MINUTOS', '60') * 60;
    if (isset($_SESSION['S_ACTIVIDAD']) && time() - $_SESSION['S_ACTIVIDAD'] > $limite) {
        session_unset();
        session_destroy();
        denegar_acceso(401, 'Su sesión ha expirado por inactividad.');
    }
    $_SESSION['S_ACTIVIDAD'] = time();

    $esAdmin = ($_SESSION['S_ROL'] ?? '') === 'ADMINISTRADOR';
    $propio  = ruta_coincide($ruta, $permisos['propios']);

    if (!$esAdmin && !$propio && !ruta_coincide($ruta, $permisos['griferos'])) {
        denegar_acceso(403, 'No tiene permisos para realizar esta acción.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!is_string($enviado) || !hash_equals(token_csrf(), $enviado)) {
            denegar_acceso(403, 'La página caducó. Recárguela e inténtelo de nuevo.');
        }
    }

    // Quien no es administrador solo actúa en nombre propio, envíe lo que envíe el navegador.
    if ($propio || !$esAdmin) {
        foreach (['id_usuario', 'idusuario', 'filtro_usuario'] as $campo) {
            if (isset($_POST[$campo]) || $campo === 'filtro_usuario') {
                $_POST[$campo] = $_SESSION['S_ID'];
            }
            if (isset($_GET[$campo])) {
                $_GET[$campo] = $_SESSION['S_ID'];
            }
        }
    }
}

/**
 * Guarda una imagen subida con nombre generado por el servidor.
 * Devuelve el nombre del archivo, o null si no es una imagen válida.
 */
function guardar_imagen_subida(array $archivo, string $carpeta): ?string
{
    $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'];

    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
        || $archivo['size'] > 2 * 1024 * 1024
        || !is_uploaded_file($archivo['tmp_name'])) {
        return null;
    }

    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false || !isset($tipos[$info[2]])) {
        return null;
    }

    if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
        return null;
    }

    $nombre = 'IMG_' . bin2hex(random_bytes(12)) . '.' . $tipos[$info[2]];
    return move_uploaded_file($archivo['tmp_name'], rtrim($carpeta, '/') . '/' . $nombre) ? $nombre : null;
}

verificar_acceso();
