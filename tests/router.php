<?php
/**
 * Enrutador para probar el sistema con el servidor integrado de PHP, sin Apache:
 *   php -S 127.0.0.1:8784 -t <carpeta que contiene grifo_grau> grifo_grau/tests/router.php
 * El servidor integrado no lee .htaccess: aquí se repiten sus bloqueos para que las pruebas
 * de acceso den el mismo resultado que en Apache.
 */
$ruta = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$raiz = basename(dirname(__DIR__));
$prefijo = '/' . $raiz . '/';

if (strpos($ruta . '/', $prefijo) !== 0) {
    http_response_code(404);
    return true;
}
$interna = substr($ruta, strlen($prefijo));
$nombre = basename($interna);

$denegado =
    preg_match('#(^|/)\.#', $interna)
    || preg_match('/\.(sql|md|bat|lock|yml|example)$/i', $nombre)
    || in_array($nombre, ['composer.json', 'Dockerfile'], true)
    || preg_match('#^(config|database|respaldos|scripts|tests)(/|$)#', $interna)
    || (preg_match('#^(controller/empresa/FOTOS|controller/usuario/fotos|img/usuarios)/#', $interna)
        && preg_match('/\.(php\d?|phtml|phar|pl|py|cgi|sh)$/i', $nombre));

if ($denegado) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$archivo = dirname(__DIR__) . '/' . $interna;
if (is_dir($archivo)) {
    if (!is_file(rtrim($archivo, '/') . '/index.php')) {
        http_response_code(403);
        echo 'Forbidden';
        return true;
    }
    if (substr($ruta, -1) !== '/') {
        header('Location: ' . $ruta . '/');
        return true;
    }
}
return false;
