<?php
/**
 * Prueba de la campana de avisos contra el sistema en ejecución.
 * Crea un supervisor y un crédito vencido temporales; al final borra ambos y deja los módulos como estaban.
 *
 *   php tests/avisos.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'avi'); }
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
$maxCredito = (int) $pdo->query('SELECT COALESCE(MAX(id_credito), 0) FROM ventas_credito')->fetchColumn();
$modulos = $pdo->query("SELECT codigo, activo FROM modulos")->fetchAll(PDO::FETCH_KEY_PAIR);
$alertasAntes = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_alertas'")->fetchColumn();
$cliente = (int) $pdo->query('SELECT MIN(id_cliente) FROM clientes')->fetchColumn();
$R = 'controller/avisos/controlador_listar.php';
$claves = fn(array $r) => array_column($r['data'] ?? [], 'clave');

try {
    $pdo->exec("DELETE FROM usuario WHERE usu_usuario = 'supervisor_avisos'");
    $pdo->prepare("INSERT INTO usuario (usu_nombre, usu_apellido, usu_usuario, usu_contrasenia, usu_rol, usu_foto) VALUES ('SUPER', 'AVISOS', 'supervisor_avisos', ?, 'SUPERVISOR', 'img/avatar.png')")
        ->execute([password_hash('Clave-Prueba-2026', PASSWORD_DEFAULT)]);
    $pdo->exec("INSERT INTO ventas_credito (id_cliente, numero_vale, monto, saldo_pendiente, estado, fecha_vencimiento) VALUES ($cliente, 'AVISO-1', 50, 50, 'PENDIENTE', DATE_SUB(CURDATE(), INTERVAL 3 DAY))");
    $pdo->exec("UPDATE configuracion SET valor = 'creditos_vencidos,diferencias_caja,turnos_sin_validar' WHERE clave = 'inicio_alertas'");

    $admin = new Cliente($base);
    $admin->entrar('admin', 'admin123');
    $grifero = new Cliente($base);
    $grifero->entrar('grifero1', 'grifero123');
    $sup = new Cliente($base);
    $sup->entrar('supervisor_avisos', 'Clave-Prueba-2026');

    echo "1. Quién recibe avisos\n";
    $r = $admin->json($R);
    comprobar('el administrador recibe el aviso de créditos vencidos', ($r['activo'] ?? false) === true && in_array('creditos_vencidos', $claves($r), true), json_encode($r));
    $a = array_values(array_filter($r['data'], fn($x) => $x['clave'] === 'creditos_vencidos'))[0] ?? null;
    comprobar('el aviso trae cantidad, texto, nivel y pantalla', $a !== null && $a['cantidad'] >= 1 && $a['nivel'] === 'danger' && $a['vista'] === 'creditos/view_creditos_pendientes.php');
    $r = $grifero->json($R);
    comprobar('el grifero no recibe avisos', ($r['activo'] ?? true) === false && ($r['data'] ?? [1]) === [], json_encode($r));
    $r = $sup->json($R);
    comprobar('el supervisor también los recibe', ($r['activo'] ?? false) === true && in_array('creditos_vencidos', $claves($r), true), json_encode($r));

    echo "\n2. Solo avisos de pantallas que se pueden abrir\n";
    $pdo->exec("UPDATE modulos SET activo = 0 WHERE codigo = 'caja'");
    $pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado) VALUES ('AVISO-T1', 2, 'DIA', CURDATE(), 'CERRADO')");
    $idTurno = (int) $pdo->lastInsertId();
    $pdo->exec("INSERT INTO arqueos (id_reporte, denominaciones, efectivo_contado, efectivo_esperado, diferencia, id_usuario) VALUES ($idTurno, '{}', 90, 100, -10, 1)");
    comprobar('con Caja apagada no se avisa de diferencias de caja', !in_array('diferencias_caja', $claves($admin->json($R)), true));
    $pdo->exec("UPDATE modulos SET activo = 1 WHERE codigo = 'caja'");
    comprobar('con Caja encendida sí', in_array('diferencias_caja', $claves($admin->json($R)), true));
    $vistas = array_column($sup->json($R)['data'] ?? [], 'vista');
    $abren = array_filter($vistas, fn($v) => $sup->pedir('view/' . $v)['codigo'] === 200);
    comprobar('cada aviso del supervisor lleva a una pantalla que puede abrir', count($vistas) > 0 && count($abren) === count($vistas), json_encode($vistas));
    $d = $admin->json('controller/dashboard/controlador_alertas.php');
    comprobar('el tablero usa el mismo filtro', in_array('creditos_vencidos', $claves($d), true));
} finally {
    $pdo->exec("DELETE FROM arqueos WHERE id_reporte IN (SELECT id_reporte FROM reportes_turno WHERE numero_documento = 'AVISO-T1')");
    $pdo->exec("DELETE FROM reportes_turno WHERE numero_documento = 'AVISO-T1'");
    $pdo->exec("DELETE FROM ventas_credito WHERE id_credito > $maxCredito");
    $pdo->exec("DELETE FROM usuario WHERE usu_usuario = 'supervisor_avisos'");
    foreach ($modulos as $codigo => $activo) {
        $pdo->prepare('UPDATE modulos SET activo = ? WHERE codigo = ?')->execute([$activo, $codigo]);
    }
    $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'inicio_alertas'")->execute([$alertasAntes]);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
