<?php
/**
 * Entrada del sistema. Con el módulo «Página pública» activo muestra la página del grifo;
 * si no, el acceso. Quien gestiona la página la puede ver antes de publicarla con ?vista_previa=1.
 */
require_once __DIR__ . '/controller/_bootstrap.php';

$vistaPrevia = isset($_GET['vista_previa']) && !empty($_SESSION['S_ID']) && puede('pagina_publica_gestionar');

if (!empty($_SESSION['S_ID']) && !$vistaPrevia) {
    header('Location: view/index.php');
    exit;
}

if ($vistaPrevia || modulo_activo('pagina_publica')) {
    require __DIR__ . '/publico/landing.php';
    exit;
}

require __DIR__ . '/login.php';
