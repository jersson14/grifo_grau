<?php
/**
 * Prueba de un cuarto combustible contra el sistema en ejecución.
 * Crea un producto GLP con su surtidor, registra un turno que lo vende y al final lo borra todo y repone las lecturas.
 *
 *   php tests/combustibles.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'com'); }
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
$lecturasAntes = $pdo->query('SELECT id_surtidor, lectura_actual FROM surtidores')->fetchAll(PDO::FETCH_KEY_PAIR);
$maxProducto = (int) $pdo->query('SELECT COALESCE(MAX(id_producto), 0) FROM productos')->fetchColumn();
$maxSurtidor = (int) $pdo->query('SELECT COALESCE(MAX(id_surtidor), 0) FROM surtidores')->fetchColumn();
$maxReporte = (int) $pdo->query('SELECT COALESCE(MAX(id_reporte), 0) FROM reportes_turno')->fetchColumn();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');

try {
    echo "1. Un producto de otro tipo\n";
    $r = $admin->pedir('controller/productos/controlador_registrar_producto.php', ['nombre' => 'GLP PRUEBA', 'tipo' => 'GLP', 'precio' => '8.50']);
    $glp = $pdo->query("SELECT id_producto, tipo FROM productos WHERE nombre = 'GLP PRUEBA'")->fetch(PDO::FETCH_ASSOC);
    comprobar('se registra un producto de tipo GLP', $glp && $glp['tipo'] === 'GLP', $r['cuerpo']);
    $admin->pedir('controller/productos/controlador_registrar_producto.php', ['nombre' => 'RARO PRUEBA', 'tipo' => 'XYZ', 'precio' => '1']);
    comprobar('un tipo desconocido se rechaza', (int) $pdo->query("SELECT COUNT(*) FROM productos WHERE nombre = 'RARO PRUEBA'")->fetchColumn() === 0);
    $pdo->exec("INSERT INTO surtidores (numero_maquina, codigo, id_producto, lectura_actual) VALUES (3, 'G1', {$glp['id_producto']}, 500)");

    echo "\n2. El turno suma el combustible nuevo\n";
    $lista = json_decode($grifero->pedir('controller/surtidores/controlador_surtidores_activos.php', [])['cuerpo'], true);
    $lecturas = [];
    $esperado = 0.0;
    foreach ($lista as $s) {
        $galones = $s['codigo'] === 'G1' ? 20 : 0;
        $lecturas[] = ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => (float) $s['lectura_actual'] + $galones, 'precio' => $s['precio_actual']];
        $esperado += $galones * (float) $s['precio_actual'];
    }
    // Diésel: 10 galones en el primer surtidor de ese tipo.
    foreach ($lecturas as $i => $l) {
        $s = $lista[$i];
        if (strpos(strtoupper($s['producto_nombre'] ?? ''), 'DIESEL') !== false) { $lecturas[$i]['lectura_final'] = (float) $s['lectura_actual'] + 10; $esperado += 10 * (float) $s['precio_actual']; $diesel = 10 * (float) $s['precio_actual']; break; }
    }
    comprobar('el surtidor de GLP aparece en el formulario', in_array('G1', array_column($lista, 'codigo'), true));
    $r = $grifero->pedir('controller/turnos/controlador_registrar_turno_completo.php', [
        'numero_documento' => 'COMB-' . random_int(1000, 9999), 'id_usuario' => 1, 'turno' => 'DIA', 'fecha' => date('Y-m-d'), 'hora_inicio' => '06:00', 'hora_fin' => '14:00',
        'lecturas' => json_encode($lecturas), 'pagos' => json_encode([]), 'creditos' => json_encode([]),
        'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => $esperado,
    ]);
    $turno = json_decode($r['cuerpo'], true);
    comprobar('el turno se registra', !empty($turno['success']), $r['cuerpo']);
    $id = (int) ($turno['id_reporte'] ?? 0);
    $fila = $pdo->query("SELECT * FROM reportes_turno WHERE id_reporte = $id")->fetch(PDO::FETCH_ASSOC) ?: [];
    comprobar('el total incluye el GLP (170) además del diésel', abs((float) ($fila['total_ventas'] ?? 0) - $esperado) < 0.01, ($fila['total_ventas'] ?? '?') . " vs $esperado");
    comprobar('los galones también (20 + 10)', abs((float) ($fila['total_galones'] ?? 0) - 30) < 0.001, (string) ($fila['total_galones'] ?? '?'));
    comprobar('con el efectivo exacto, el turno cuadra', abs((float) ($fila['monto_faltante'] ?? 1)) < 0.01, (string) ($fila['monto_faltante'] ?? '?'));
    comprobar('la columna de diésel sigue llevando solo el diésel', abs((float) ($fila['total_diesel'] ?? 0) - $diesel) < 0.01);

    echo "\n3. Detalle y reportes\n";
    $d = $admin->json('controller/turnos/controlador_detalle_turno.php', ['id_reporte' => $id]);
    $nombres = array_column($d['por_producto'] ?? [], 'nombre');
    comprobar('el detalle del turno lista el GLP con sus galones', in_array('GLP PRUEBA', $nombres, true)
        && abs((float) (array_values(array_filter($d['por_producto'], fn($p) => $p['nombre'] === 'GLP PRUEBA'))[0]['galones'] ?? 0) - 20) < 0.001, json_encode($d['por_producto'] ?? null));
    comprobar('y su total es el del turno', abs((float) ($d['total_ventas'] ?? 0) - $esperado) < 0.01);
    $m = json_decode($admin->pedir('controller/reportes/controlador_ventas_mes.php', [])['cuerpo'], true);
    comprobar('el gráfico del mes incluye el GLP', in_array('GLP PRUEBA', array_column($m['por_producto'] ?? [], 'nombre'), true), json_encode($m['por_producto'] ?? null));
} finally {
    $ids = "SELECT id_reporte FROM reportes_turno WHERE id_reporte > $maxReporte";
    foreach (['lecturas_turno', 'pagos_reporte', 'ventas_credito'] as $t) {
        $pdo->exec("DELETE FROM $t WHERE id_reporte IN ($ids)");
    }
    $pdo->exec("DELETE FROM reportes_turno WHERE id_reporte > $maxReporte");
    $pdo->exec("DELETE FROM surtidores WHERE id_surtidor > $maxSurtidor");
    $pdo->exec("DELETE FROM precios_historial WHERE id_producto > $maxProducto");
    $pdo->exec("DELETE FROM productos WHERE id_producto > $maxProducto");
    $restaurar = $pdo->prepare('UPDATE surtidores SET lectura_actual = ? WHERE id_surtidor = ?');
    foreach ($lecturasAntes as $s => $l) { $restaurar->execute([$l, $s]); }
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
