<?php
/**
 * Prueba del cambio obligatorio de contraseña contra el sistema en ejecución.
 * Enciende el ajuste, entra con clave de ejemplo y la cambia. Al final deja las claves y el ajuste como estaban.
 *
 *   php tests/claves.php
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$base = rtrim(getenv('GRIFO_BASE') ?: 'http://localhost/grifo_grau', '/');
$fallos = 0; $total = 0;

function comprobar(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $total;
    $total++;
    if (!$ok) { $fallos++; }
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : ' -> ' . substr($detalle, 0, 300)) . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'cla'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $c = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies, CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $this->csrf !== '' ? ['X-CSRF-Token: ' . $this->csrf] : []]);
        if ($post !== null) { curl_setopt($c, CURLOPT_POST, true); curl_setopt($c, CURLOPT_POSTFIELDS, $post); }
        $cuerpo = (string) curl_exec($c);
        return ['codigo' => curl_getinfo($c, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'tipo' => (string) curl_getinfo($c, CURLINFO_CONTENT_TYPE)];
    }
    public function json(string $ruta, array $post = []): array { return json_decode($this->pedir($ruta, $post)['cuerpo'], true) ?: []; }
    public function entrar(string $u, string $c): void
    {
        $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
    }
}

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$fila = $pdo->query("SELECT id_usuario, usu_contrasenia, debe_cambiar_clave FROM usuario WHERE usu_usuario = 'grifero2'")->fetch(PDO::FETCH_ASSOC);
$id = (int) $fila['id_usuario'];
$ajusteAntes = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'clave_forzar_cambio'")->fetchColumn();
$ajuste = fn(string $v) => $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'clave_forzar_cambio'")->execute([$v]);
$marca = fn() => (int) $pdo->query("SELECT debe_cambiar_clave FROM usuario WHERE id_usuario = $id")->fetchColumn();
$llaveIntentos = hash('sha256', 'cambio_clave|' . $id);
$nueva = 'Surtidor-Norte-2026';

try {
    $marcasAntes = $pdo->query('SELECT id_usuario, debe_cambiar_clave FROM usuario')->fetchAll(PDO::FETCH_KEY_PAIR);
    $ajuste('1');

    echo "1. Entrar con una clave de ejemplo\n";
    $g = new Cliente($base);
    $r = json_decode($g->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'grifero2', 'c' => 'grifero123'])['cuerpo'], true);
    comprobar('entra y avisa que debe cambiar la clave', ($r['ok'] ?? false) === true && ($r['cambiar_clave'] ?? false) === true, json_encode($r));
    comprobar('queda marcado en la base', $marca() === 1);
    $panel = $g->pedir('view/index.php');
    if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $panel['cuerpo'], $m)) { $g->csrf = $m[1]; }
    comprobar('el panel abre la pantalla de cambio', $panel['codigo'] === 200 && strpos($panel['cuerpo'], 'view_cambiar_clave.php') !== false);
    comprobar('la pantalla de cambio se puede abrir', $g->pedir('view/usuario/view_cambiar_clave.php')['codigo'] === 200);
    $otra = $g->pedir('controller/usuario/controlador_obtener_perfil.php', []);
    comprobar('cualquier otra acción queda bloqueada', $otra['codigo'] === 403 && strpos((string) (json_decode($otra['cuerpo'], true)['message'] ?? ''), 'Cambie su contraseña') !== false, $otra['codigo'] . ' ' . $otra['cuerpo']);
    comprobar('cualquier otra pantalla también', $g->pedir('view/turnos/view_registrar_turno.php')['codigo'] === 403);

    echo "\n2. Reglas de la clave nueva\n";
    $cambiar = fn($actual, $nueva) => $g->json('controller/usuario/controlador_cambiar_contrasena.php', ['contrasena_actual' => $actual, 'contrasena_nueva' => $nueva, 'id_usuario' => 1]);
    $r = $cambiar('incorrecta', $nueva);
    comprobar('rechaza una clave actual incorrecta', ($r['success'] ?? true) === false && strpos($r['message'], 'actual') !== false, json_encode($r));
    comprobar('rechaza una clave corta', ($cambiar('grifero123', 'Ab1')['success'] ?? true) === false);
    comprobar('rechaza una clave común', ($cambiar('grifero123', '12345678')['success'] ?? true) === false);
    comprobar('rechaza el propio usuario como clave', ($cambiar('grifero123', 'GRIFERO2')['success'] ?? true) === false);
    comprobar('rechaza repetir la clave actual', ($cambiar('grifero123', 'grifero123')['success'] ?? true) === false);
    $antesAdmin = $pdo->query('SELECT usu_contrasenia FROM usuario WHERE id_usuario = 1')->fetchColumn();
    $r = $cambiar('grifero123', $nueva);
    comprobar('acepta una clave aceptable', ($r['success'] ?? false) === true, json_encode($r));
    comprobar('usa la cuenta de la sesión aunque el formulario envíe otro id', $pdo->query('SELECT usu_contrasenia FROM usuario WHERE id_usuario = 1')->fetchColumn() === $antesAdmin
        && password_verify($nueva, (string) $pdo->query("SELECT usu_contrasenia FROM usuario WHERE id_usuario = $id")->fetchColumn()));
    comprobar('quita la marca y libera el sistema', $marca() === 0 && $g->pedir('view/usuario/view_mi_perfil.php')['codigo'] === 200);
    comprobar('queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'CAMBIO_CLAVE' AND id_usuario = $id")->fetchColumn() >= 1);

    echo "\n3. Entrar de nuevo\n";
    $h = new Cliente($base);
    $r = json_decode($h->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'grifero2', 'c' => 'grifero123'])['cuerpo'], true);
    comprobar('la clave anterior ya no sirve', ($r['ok'] ?? true) === false);
    $r = json_decode($h->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'grifero2', 'c' => $nueva])['cuerpo'], true);
    comprobar('con la nueva entra sin que se le pida otro cambio', ($r['ok'] ?? false) === true && ($r['cambiar_clave'] ?? true) === false, json_encode($r));

    echo "\n4. Límite de intentos con la clave actual\n";
    if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $h->pedir('view/index.php')['cuerpo'], $m)) { $h->csrf = $m[1]; }
    for ($i = 0; $i < 5; $i++) {
        $h->json('controller/usuario/controlador_cambiar_contrasena.php', ['contrasena_actual' => 'mal' . $i, 'contrasena_nueva' => 'Otra-Clave-Larga-1']);
    }
    $r = $h->json('controller/usuario/controlador_cambiar_contrasena.php', ['contrasena_actual' => $nueva, 'contrasena_nueva' => 'Otra-Clave-Larga-1']);
    comprobar('tras 5 fallos bloquea aunque la clave sea correcta', ($r['success'] ?? true) === false && strpos($r['message'], 'Demasiados') !== false, json_encode($r));
    $pdo->prepare('DELETE FROM login_intentos WHERE clave = ?')->execute([$llaveIntentos]);

    echo "\n5. Clave asignada por un administrador y ajuste apagado\n";
    // El administrador entra con el ajuste apagado (su clave de ejemplo lo marcaría) y lo enciende después.
    $ajuste('0');
    $admin = new Cliente($base);
    $admin->entrar('admin', 'admin123');
    $ajuste('1');
    $admin->pedir('controller/usuario/controlador_modificar_usuario_contra.php', ['id' => $id, 'con' => 'Asignada-Por-Admin-1']);
    comprobar('una clave asignada por el administrador obliga a cambiarla', $marca() === 1);
    $ajuste('0');
    $k = new Cliente($base);
    $k->entrar('grifero2', 'Asignada-Por-Admin-1');
    comprobar('con el ajuste apagado no se bloquea', $k->pedir('view/usuario/view_mi_perfil.php')['codigo'] === 200);
    $r = json_decode($k->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'grifero1', 'c' => 'grifero123'])['cuerpo'], true);
    comprobar('ni se pide cambiar una clave de ejemplo', ($r['ok'] ?? false) === true && ($r['cambiar_clave'] ?? true) === false);
} finally {
    $pdo->prepare('UPDATE usuario SET usu_contrasenia = ?, debe_cambiar_clave = ? WHERE id_usuario = ?')->execute([$fila['usu_contrasenia'], $fila['debe_cambiar_clave'], $id]);
    foreach ($marcasAntes ?? [] as $u => $v) {
        if ((int) $u !== $id) { $pdo->prepare('UPDATE usuario SET debe_cambiar_clave = ? WHERE id_usuario = ?')->execute([$v, $u]); }
    }
    $pdo->prepare('DELETE FROM login_intentos WHERE clave = ?')->execute([$llaveIntentos]);
    $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'clave_forzar_cambio'")->execute([$ajusteAntes === false ? '1' : $ajusteAntes]);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
