<?php
/**
 * Prueba de la página principal por rol contra el sistema en ejecución.
 * Crea dos turnos temporales (uno abierto de ayer y uno cerrado del grifero) y los borra al terminar.
 *
 *   php tests/inicio.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'ini'); }
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
$maxReporte = (int) $pdo->query('SELECT COALESCE(MAX(id_reporte), 0) FROM reportes_turno')->fetchColumn();
$bloquesAntes = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_bloques'")->fetchColumn();
$idGrifero = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$tiene = fn(string $html, string $id) => strpos($html, 'id="' . $id . '"') !== false;

try {
    $pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado) VALUES ('INI-ABIERTO', 2, 'NOCHE', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'ABIERTO')");
    $pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado, total_ventas, total_galones, monto_faltante, monto_credito)
                VALUES ('INI-CERRADO', $idGrifero, 'DIA', CURDATE(), 'CERRADO', 1200, 80, -15, 100)");

    echo "1. Cada rol ve sus bloques\n";
    $pa = $admin->pedir('view/reportes/view_dashboard.php')['cuerpo'];
    comprobar('el administrador ve las cifras del grifo', $tiene($pa, 'indicadores_dia') && $tiene($pa, 'grafico_ventas_semana') && $tiene($pa, 'tabla_desempeno_griferos'));
    comprobar('y no el resumen propio de grifero', !$tiene($pa, 'mi_resumen'));
    $pg = $grifero->pedir('view/reportes/view_dashboard.php');
    comprobar('el grifero abre el Dashboard', $pg['codigo'] === 200);
    comprobar('el grifero ve su resumen propio', $tiene($pg['cuerpo'], 'mi_resumen'));
    comprobar('y no las cifras de todo el grifo', !$tiene($pg['cuerpo'], 'indicadores_dia') && !$tiene($pg['cuerpo'], 'tabla_desempeno_griferos') && !$tiene($pg['cuerpo'], 'grafico_ventas_semana'));
    comprobar('sus accesos rápidos son solo pantallas que puede abrir', strpos($pg['cuerpo'], 'turnos/view_registrar_turno.php') !== false && strpos($pg['cuerpo'], 'caja/view_caja.php') === false);

    echo "\n2. Las cifras del grifo no se le entregan al grifero\n";
    foreach (['controlador_ventas_por_fecha', 'controlador_desempeno_griferos', 'controlador_indicadores_dia', 'controlador_ventas_mes'] as $c) {
        comprobar("$c: 403 para el grifero", $grifero->pedir("controller/reportes/$c.php", [])['codigo'] === 403);
    }

    echo "\n3. Resumen propio\n";
    $r = $grifero->json('controller/inicio/controlador_mi_resumen.php');
    comprobar('cuenta sus turnos, ventas y faltantes del mes', ($r['mes']['turnos'] ?? 0) >= 1 && ($r['mes']['ventas'] ?? 0) >= 1200 && ($r['mes']['faltantes'] ?? 0) >= 15, json_encode($r));
    comprobar('muestra su último turno', ($r['ultimo']['numero_documento'] ?? '') === 'INI-CERRADO', json_encode($r['ultimo'] ?? null));
    $r2 = $admin->json('controller/inicio/controlador_mi_resumen.php', ['id_usuario' => $idGrifero]);
    comprobar('siempre es del usuario en sesión, aunque se envíe otro id', ($r2['ultimo']['numero_documento'] ?? '') !== 'INI-CERRADO');

    echo "\n4. Indicadores corregidos\n";
    $i = json_decode($admin->pedir('controller/reportes/controlador_indicadores_dia.php', [])['cuerpo'], true);
    comprobar('cuenta el turno noche abierto ayer', ($i['turnos_activos'] ?? 0) >= 1, json_encode($i));
    comprobar('las ventas de hoy incluyen el turno cerrado hoy', ($i['ventas_dia'] ?? 0) >= 1200);
    comprobar('los créditos traen saldo y vencidos', array_key_exists('creditos_saldo', $i) && array_key_exists('creditos_vencidos', $i));
    $s = json_decode($admin->pedir('controller/reportes/controlador_ventas_semana.php', [])['cuerpo'], true);
    comprobar('el gráfico de la semana trae 7 días seguidos, hasta hoy', count($s) === 7 && end($s)['fecha_reporte'] === date('Y-m-d') && $s[0]['fecha_reporte'] === date('Y-m-d', strtotime('-6 day')), json_encode(array_column($s, 'fecha_reporte')));

    echo "\n5. Bloques configurables\n";
    $pdo->exec("UPDATE configuracion SET valor = 'indicadores' WHERE clave = 'inicio_bloques'");
    $pa = $admin->pedir('view/reportes/view_dashboard.php')['cuerpo'];
    comprobar('con solo «indicadores» elegido, el resto no aparece', $tiene($pa, 'indicadores_dia') && !$tiene($pa, 'grafico_ventas_semana') && !$tiene($pa, 'tabla_desempeno_griferos'));
    $r = $admin->json('controller/configuracion/controlador_guardar.php', ['inicio_bloques' => 'ventas_semana,inventado']);
    comprobar('Configuración guarda solo bloques válidos', $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_bloques'")->fetchColumn() === 'ventas_semana', json_encode($r));
} finally {
    $pdo->exec("DELETE FROM reportes_turno WHERE id_reporte > $maxReporte AND numero_documento LIKE 'INI-%'");
    $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'inicio_bloques'")->execute([$bloquesAntes]);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
