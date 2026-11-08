<?php
/**
 * Prueba del asistente de instalación contra el sistema en ejecución.
 * Necesita una base recién migrada sin usuarios (sin --demo). Al terminar la deja vacía otra vez.
 *
 *   GRIFO_BASE=... DB_NAME=<base vacía> php tests/instalacion.php
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
    public string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'ins'); }
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
if ((int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn() > 0) {
    echo "Omitida: la base " . getenv('DB_NAME') . " ya tiene usuarios. Use una base recién migrada sin --demo.\n";
    exit(0);
}

function instalar(Cliente $c, string $token, array $datos): array
{
    $h = curl_init($GLOBALS['base'] . '/instalar.php');
    curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['token' => $token, 'datos' => $datos]), CURLOPT_COOKIEFILE => $c->cookies, CURLOPT_COOKIEJAR => $c->cookies]);
    return json_decode((string) curl_exec($h), true) ?: [];
}

$validos = [
    'empresa' => ['nombre' => 'Grifo Prueba & Hijos', 'ruc' => '20123456789', 'telefono' => '987654321', 'direccion' => 'Av. Grau 123'],
    'administrador' => ['nombres' => 'Rosa', 'apellidos' => 'Quispe', 'usuario' => 'rosa.admin', 'clave' => 'Surtidor-Sur-2026', 'repetir' => 'Surtidor-Sur-2026'],
    'combustibles' => [['nombre' => 'Diesel B5', 'tipo' => 'DIESEL', 'precio' => '15.20'], ['nombre' => 'Regular 90', 'tipo' => 'REGULAR', 'precio' => '13.50'], ['nombre' => 'GLP', 'tipo' => 'GLP', 'precio' => '8.40']],
    'surtidores' => [
        ['maquina' => 1, 'codigo' => 'd1', 'combustible' => 0, 'lectura' => '125430.5'],
        ['maquina' => 1, 'codigo' => 'R1', 'combustible' => 1, 'lectura' => '98002.125'],
        ['maquina' => 2, 'codigo' => 'G1', 'combustible' => 2, 'lectura' => '0'],
    ],
];
$con = function (array $cambios) use ($validos) { return array_replace_recursive($validos, $cambios); };

try {
    echo "1. Un sistema sin usuarios abre el asistente\n";
    $c = new Cliente($base);
    $r = $c->pedir('');
    comprobar('la entrada lleva al asistente', $r['codigo'] === 302 || strpos($r['cuerpo'], 'Instalar el sistema') !== false, (string) $r['codigo']);
    $h = curl_init($base . '/login.php'); curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true]); $cab = (string) curl_exec($h);
    comprobar('el acceso también redirige al asistente', stripos($cab, 'Location: instalar.php') !== false);
    $pagina = $c->pedir('instalar.php');
    preg_match('/var TOKEN = "([a-f0-9]{64})"/', $pagina['cuerpo'], $m);
    $token = $m[1] ?? '';
    comprobar('el asistente se muestra con su token', $pagina['codigo'] === 200 && $token !== '');

    echo "\n2. Valida antes de guardar\n";
    comprobar('rechaza un token inválido', strpos(instalar($c, str_repeat('0', 64), $validos)['message'] ?? '', 'caducó') !== false);
    comprobar('exige una contraseña segura', strpos(instalar($c, $token, $con(['administrador' => ['clave' => '12345678', 'repetir' => '12345678']]))['message'] ?? '', 'fácil') !== false);
    comprobar('las contraseñas deben coincidir', strpos(instalar($c, $token, $con(['administrador' => ['repetir' => 'otra-cosa-123']]))['message'] ?? '', 'coinciden') !== false);
    comprobar('valida el RUC', strpos(instalar($c, $token, $con(['empresa' => ['ruc' => '123']]))['message'] ?? '', 'RUC') !== false);
    comprobar('exige el precio de cada combustible', strpos(instalar($c, $token, $con(['combustibles' => [2 => ['precio' => '']]]))['message'] ?? '', 'precio') !== false);
    $repetido = $validos; $repetido['surtidores'][] = ['maquina' => 1, 'codigo' => 'D1', 'combustible' => 0, 'lectura' => '1'];
    comprobar('no admite un surtidor repetido en la misma máquina', strpos(instalar($c, $token, $repetido)['message'] ?? '', 'repetido') !== false);
    $malo = $validos; $malo['surtidores'][0]['combustible'] = 7;
    comprobar('el surtidor debe apuntar a un combustible de la lista', strpos(instalar($c, $token, $malo)['message'] ?? '', 'combustible') !== false);
    comprobar('nada se guardó con datos inválidos', (int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn() === 0 && (int) $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn() === 0);

    echo "\n3. Instala\n";
    $r = instalar($c, $token, $validos);
    comprobar('la instalación termina', ($r['success'] ?? false) === true, json_encode($r));
    $u = $pdo->query("SELECT usu_usuario, usu_rol, usu_estatus, debe_cambiar_clave, usu_nombre FROM usuario")->fetchAll(PDO::FETCH_ASSOC);
    comprobar('crea un único administrador activo, sin cambio de clave pendiente', count($u) === 1 && $u[0]['usu_rol'] === 'ADMINISTRADOR' && $u[0]['usu_estatus'] === 'ACTIVO' && (int) $u[0]['debe_cambiar_clave'] === 0 && $u[0]['usu_nombre'] === 'ROSA', json_encode($u));
    $e = $pdo->query('SELECT nombre, codigo FROM empresa')->fetch(PDO::FETCH_ASSOC);
    comprobar('guarda la empresa tal cual se escribió', $e && $e['nombre'] === 'Grifo Prueba & Hijos' && $e['codigo'] === '20123456789');
    $p = $pdo->query('SELECT nombre, tipo, precio_actual FROM productos ORDER BY id_producto')->fetchAll(PDO::FETCH_ASSOC);
    comprobar('crea los combustibles con su tipo y precio', count($p) === 3 && $p[2]['nombre'] === 'GLP' && $p[2]['tipo'] === 'GLP' && abs((float) $p[0]['precio_actual'] - 15.2) < 0.001, json_encode($p));
    $s = $pdo->query('SELECT s.numero_maquina, s.codigo, s.lectura_actual, p.nombre FROM surtidores s JOIN productos p USING (id_producto) ORDER BY s.id_surtidor')->fetchAll(PDO::FETCH_ASSOC);
    comprobar('crea los surtidores con su lectura inicial y combustible', count($s) === 3 && $s[0]['codigo'] === 'D1' && abs((float) $s[0]['lectura_actual'] - 125430.5) < 0.001 && $s[2]['nombre'] === 'GLP' && (int) $s[2]['numero_maquina'] === 2, json_encode($s));
    comprobar('queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'INSTALACION'")->fetchColumn() === 1);

    echo "\n4. Después queda cerrado\n";
    $r = instalar($c, $token, $con(['administrador' => ['usuario' => 'intruso']]));
    comprobar('un segundo envío no crea otro administrador', ($r['success'] ?? true) === false && (int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn() === 1, json_encode($r));
    $h = curl_init($base . '/instalar.php'); curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true]); $cab = (string) curl_exec($h);
    comprobar('el asistente redirige al acceso', stripos($cab, 'Location: login.php') !== false);
    $a = new Cliente($base);
    $r = json_decode($a->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'rosa.admin', 'c' => 'Surtidor-Sur-2026'])['cuerpo'], true);
    comprobar('el administrador entra sin que se le pida cambiar la clave', ($r['ok'] ?? false) === true && ($r['cambiar_clave'] ?? true) === false, json_encode($r));
    comprobar('y ve el panel', strpos($a->pedir('view/index.php')['cuerpo'], 'ROSA') !== false);
} finally {
    // La base vuelve a quedar sin usuarios para poder repetir la prueba.
    $pdo->exec('DELETE FROM surtidores');
    $pdo->exec('DELETE FROM precios_historial');
    $pdo->exec('DELETE FROM productos');
    $pdo->exec('DELETE FROM empresa');
    $pdo->exec('DELETE FROM auditoria');
    $pdo->exec('DELETE FROM login_intentos');
    $pdo->exec('DELETE FROM usuario');
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
