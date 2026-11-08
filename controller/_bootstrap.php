<?php
/**
 * Control de acceso central. Todo controlador y vista lo carga en su primera línea.
 *
 * Al incluirse: inicia la sesión con cookies seguras, comprueba sesión, rol (ver
 * config/permisos.php, que niega por defecto) y token CSRF en cada POST.
 */
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/model/model_conexion.php';

date_default_timezone_set('America/Lima');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
configurar_registro_errores();

/**
 * Los errores se guardan en registros/errores.log (carpeta sin acceso web) y nunca se muestran.
 * El archivo se renombra con la fecha al pasar de 5 MB; se conservan los 5 más recientes.
 */
function configurar_registro_errores(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    $carpeta = rtrim(env('LOG_DIR', dirname(__DIR__) . '/registros'), '/\\');
    if (!is_dir($carpeta) && !@mkdir($carpeta, 0755, true)) {
        return; // Sin carpeta escribible, PHP sigue usando el registro del servidor.
    }
    if (!is_file($carpeta . '/.htaccess')) {
        @file_put_contents($carpeta . '/.htaccess', "Require all denied\n");
    }
    $archivo = $carpeta . '/errores.log';
    if (is_file($archivo) && filesize($archivo) > 5 * 1024 * 1024) {
        @rename($archivo, $carpeta . '/errores-' . date('Ymd-His') . '.log');
        $viejos = glob($carpeta . '/errores-*.log') ?: [];
        rsort($viejos);
        foreach (array_slice($viejos, 5) as $viejo) {
            @unlink($viejo);
        }
    }
    ini_set('error_log', $archivo);

    // Una excepción que nadie atrapó: se registra y el usuario ve un mensaje genérico, no una página en blanco.
    set_exception_handler(function (Throwable $e) {
        error_log('Excepción no atrapada en ' . ruta_script_actual() . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')');
        if (!headers_sent()) {
            http_response_code(500);
        }
        $mensaje = 'Ocurrió un error inesperado. Inténtelo de nuevo; si sigue fallando, avise al administrador.';
        if (strpos(ruta_script_actual(), 'controller/') === 0) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['success' => false, 'message' => $mensaje]);
        } else {
            echo '<div class="alert alert-danger m-3">' . $mensaje . '</div>';
        }
    });
}

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

/**
 * Texto de la base listo para HTML (vistas, PDF, Excel). Primero decodifica, porque los datos antiguos
 * se guardaron ya escapados (&amp;), y luego escapa una sola vez. Acepta null.
 */
function esc($valor): string
{
    return htmlspecialchars(html_entity_decode((string) $valor, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

/** Responde con un error y termina. Las vistas reciben HTML; el resto, JSON. */
function denegar_acceso(int $codigo, string $mensaje): void
{
    if ($codigo === 401 && ruta_script_actual() === 'view/index.php') {
        header('Location: ../login.php');
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

function modulo_activo(string $codigo): bool
{
    static $cache = [];
    if (!isset($cache[$codigo])) {
        require_once dirname(__DIR__) . '/model/model_configuracion.php';
        $cache[$codigo] = Modelo_Configuracion::Modulo_Activo($codigo);
    }
    return $cache[$codigo];
}

/**
 * Corta la petición si el usuario no puede ver este turno: sin la capacidad «ver todos», solo los propios.
 * $tipo indica qué identificador se recibió: el del turno, una lectura, un pago o un crédito.
 */
function exigir_acceso_turno(string $tipo, $id): void
{
    $consultas = [
        'reporte' => 'SELECT id_usuario FROM reportes_turno WHERE id_reporte = ?',
        'lectura' => 'SELECT rt.id_usuario FROM lecturas_turno x INNER JOIN reportes_turno rt ON x.id_reporte = rt.id_reporte WHERE x.id_lectura = ?',
        'pago'    => 'SELECT rt.id_usuario FROM pagos_reporte x INNER JOIN reportes_turno rt ON x.id_reporte = rt.id_reporte WHERE x.id_pago_reporte = ?',
        'credito' => 'SELECT rt.id_usuario FROM ventas_credito x INNER JOIN reportes_turno rt ON x.id_reporte = rt.id_reporte WHERE x.id_credito = ?',
    ];
    $c = conexionBD::conexionPDO();
    $query = $c->prepare($consultas[$tipo]);
    $query->execute([(int) $id]);
    $dueno = $query->fetchColumn();
    if ($dueno === false) {
        denegar_acceso(404, 'El turno no existe.');
    }
    if (!puede('ver_todos') && (int) $dueno !== (int) $_SESSION['S_ID']) {
        denegar_acceso(403, 'Este turno pertenece a otro usuario.');
    }
}

function es_administrador(): bool
{
    return ($_SESSION['S_ROL'] ?? '') === 'ADMINISTRADOR';
}

/** Capacidades del usuario en sesión, o null si es administrador (las tiene todas). */
function capacidades_sesion(): ?array
{
    static $cache = null;
    static $rol = null;
    if (es_administrador()) {
        return null;
    }
    if ($cache === null || $rol !== $_SESSION['S_ROL']) {
        require_once dirname(__DIR__) . '/model/model_roles.php';
        $rol = $_SESSION['S_ROL'];
        $cache = Modelo_Roles::Capacidades_De($rol);
    }
    return $cache;
}

function puede(string $capacidad): bool
{
    $capacidades = capacidades_sesion();
    return $capacidades === null || in_array($capacidad, $capacidades, true);
}

/** Si el rol del usuario en sesión da acceso a la ruta. Lo que no está en el catálogo es solo para administradores. */
function ruta_permitida(string $ruta): bool
{
    $capacidades = capacidades_sesion();
    if ($capacidades === null) {
        return true;
    }
    $catalogo = require dirname(__DIR__) . '/config/capacidades.php';
    foreach ($capacidades as $capacidad) {
        if (isset($catalogo[$capacidad]) && ruta_coincide($ruta, $catalogo[$capacidad][2])) {
            return true;
        }
    }
    return false;
}

/** Si la ruta es usable ahora: incluye tener permiso y que su módulo esté activo. */
function ruta_accesible(string $ruta): bool
{
    $permisos = require dirname(__DIR__) . '/config/permisos.php';
    foreach ($permisos['modulos'] as $patron => $modulo) {
        if (ruta_coincide($ruta, [$patron]) && !modulo_activo($modulo)) {
            return false;
        }
    }
    return ruta_coincide($ruta, $permisos['propios']) || ruta_permitida($ruta);
}

/**
 * Registra toda acción que modifica datos, sin contraseñas ni claves. Corre al terminar la
 * petición, de modo que un fallo aquí nunca afecta a la operación.
 */
function auditar_peticion(string $ruta): void
{
    $accion = str_replace(['controlador_', '.php'], '', basename($ruta));
    if (!preg_match('/^(regist|guardar|modific|cambiar|anular|eliminar|actualizar|agregar|abrir|cerrar|pagar|revertir|validar|asignar|aplicar|resolver|programar|cancelar|mover|editar)/', $accion)) {
        return;
    }
    try {
        $datos = [];
        foreach ($_POST as $clave => $valor) {
            if (preg_match('/contra|clave|pass|token|key|secret|^con$|^c$/i', (string) $clave)) {
                $datos[$clave] = '***';
            } else {
                $datos[$clave] = $valor;
            }
        }
        $detalle = 'HTTP ' . http_response_code() . ' ' . json_encode($datos, JSON_UNESCAPED_UNICODE);
        $c = conexionBD::conexionPDO();
        $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
          ->execute([$_SESSION['S_ID'] ?? null, $_SESSION['S_USU'] ?? null, $accion, mb_substr($detalle, 0, 2000), $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) {
        error_log('No se pudo registrar la auditoría: ' . $e->getMessage());
    }
}

/**
 * Ejecuta la acción de un controlador y responde en JSON. Los errores de validación
 * (DomainException) llegan al usuario; cualquier otro se registra y se oculta.
 */
function responder_json(callable $accion): void
{
    header('Content-Type: application/json; charset=utf-8');
    try {
        $datos = $accion();
        echo json_encode(['success' => true] + (is_array($datos) ? $datos : []));
    } catch (DomainException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log(ruta_script_actual() . ': ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'No se pudo completar la operación.']);
    }
}

/** Rango de fechas de un filtro. Por defecto, el mes en curso. */
function rango_fechas(): array
{
    $valida = fn($f) => is_string($f) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
    $desde = $_POST['desde'] ?? '';
    $hasta = $_POST['hasta'] ?? '';
    return [$valida($desde) ? $desde : date('Y-m-01'), $valida($hasta) ? $hasta : date('Y-m-d')];
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
    // Las consultas automáticas (la campana de avisos) no cuentan como actividad: si no, la sesión nunca vencería.
    if (empty($_SERVER['HTTP_X_SEGUNDO_PLANO'])) {
        $_SESSION['S_ACTIVIDAD'] = time();
    }

    // El rol y el estado se leen en cada petición: un cambio o una baja surten efecto de inmediato.
    $c = conexionBD::conexionPDO();
    if ($c === null) {
        denegar_acceso(503, 'El sistema no está disponible en este momento.');
    }
    $query = $c->prepare('SELECT usu_rol, usu_estatus, debe_cambiar_clave FROM usuario WHERE id_usuario = ?');
    $query->execute([$_SESSION['S_ID']]);
    $cuenta = $query->fetch(PDO::FETCH_ASSOC);
    if (!$cuenta || $cuenta['usu_estatus'] !== 'ACTIVO') {
        session_unset();
        session_destroy();
        denegar_acceso(401, 'Su usuario ya no está activo.');
    }
    $_SESSION['S_ROL'] = $cuenta['usu_rol'];

    // Con una clave de ejemplo, débil o asignada por un administrador, solo se puede cambiarla.
    $_SESSION['S_CAMBIAR_CLAVE'] = false;
    if ((int) $cuenta['debe_cambiar_clave'] === 1) {
        // Se consulta aquí y no con Modelo_Usuario: hay controladores que cargan ese modelo con require.
        $ajuste = $c->query("SELECT valor FROM configuracion WHERE clave = 'clave_forzar_cambio'")->fetchColumn();
        if ($ajuste === false || $ajuste === null || $ajuste === '1') {
            $_SESSION['S_CAMBIAR_CLAVE'] = true;
            if (!ruta_coincide($ruta, $permisos['cambio_clave'])) {
                denegar_acceso(403, 'Cambie su contraseña para continuar.');
            }
        }
    }

    $propio = ruta_coincide($ruta, $permisos['propios']);
    if (!$propio && !ruta_coincide($ruta, $permisos['base']) && !ruta_permitida($ruta)) {
        denegar_acceso(403, 'No tiene permisos para realizar esta acción.');
    }

    foreach ($permisos['modulos'] as $patron => $modulo) {
        if (ruta_coincide($ruta, [$patron]) && !modulo_activo($modulo)) {
            denegar_acceso(403, 'Este módulo está desactivado.');
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!is_string($enviado) || !hash_equals(token_csrf(), $enviado)) {
            denegar_acceso(403, 'La página caducó. Recárguela e inténtelo de nuevo.');
        }
    }

    // Sin la capacidad de ver a todos, solo se actúa en nombre propio, envíe lo que envíe el navegador.
    if ($propio || !puede('ver_todos')) {
        foreach (['id_usuario', 'idusuario', 'filtro_usuario'] as $campo) {
            if (isset($_POST[$campo]) || $campo === 'filtro_usuario') {
                $_POST[$campo] = $_SESSION['S_ID'];
            }
            if (isset($_GET[$campo])) {
                $_GET[$campo] = $_SESSION['S_ID'];
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        register_shutdown_function('auditar_peticion', $ruta);
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
