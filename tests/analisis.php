<?php
/**
 * Prueba del análisis gerencial contra el sistema en ejecución.
 * Crea sus propios datos en marzo de 2020 y créditos vencidos a la fecha, y los borra al terminar.
 *
 *   php tests/analisis.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'ana'); }
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
$max = [];
foreach (['proveedores' => 'id_proveedor', 'tanques' => 'id_tanque', 'compras' => 'id_compra', 'reportes_turno' => 'id_reporte', 'ventas' => 'id_venta', 'clientes' => 'id_cliente', 'ventas_credito' => 'id_credito'] as $t => $k) {
    $max[$t] = (int) $pdo->query("SELECT COALESCE(MAX($k), 0) FROM $t")->fetchColumn();
}
$usuario = fn($u) => (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = '$u'")->fetchColumn();
$g1 = $usuario('grifero1');
$g2 = $usuario('grifero2') ?: $g1;
$surtidor = fn($nombre) => $pdo->query("SELECT s.id_surtidor, s.id_producto FROM surtidores s JOIN productos p USING (id_producto) WHERE p.nombre LIKE '$nombre%' ORDER BY s.id_surtidor LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$diesel = $surtidor('DIESEL');
$regular = $surtidor('REGULAR');
$pago = (int) $pdo->query("SELECT id_tipo_pago FROM tipos_pago WHERE nombre = 'EFECTIVO'")->fetchColumn();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$R = 'controller/analisis/controlador_';
$falla = fn(array $r) => ($r['success'] ?? true) === false;
$periodo = ['desde' => '2020-03-01', 'hasta' => '2020-03-31'];
$casi = fn($a, $b) => $a !== null && abs((float) $a - (float) $b) < 0.01;

try {
    // Compras de diésel: dos dentro de la ventana de costo (11 y 12, promedio 11.50), una anulada y una antigua que no cuentan.
    $pdo->exec("INSERT INTO proveedores (ruc, razon_social) VALUES ('20999999991', 'Proveedor de prueba del análisis')");
    $prov = (int) $pdo->lastInsertId();
    $pdo->exec("INSERT INTO tanques (nombre, id_producto, capacidad_galones) VALUES ('Tanque prueba análisis', {$diesel['id_producto']}, 5000)");
    $tanque = (int) $pdo->lastInsertId();
    $compra = function ($fecha, $galones, $costo, $estado = 'REGISTRADA') use ($pdo, $prov, $tanque) {
        $total = round($galones * $costo, 2);
        $pdo->exec("INSERT INTO compras (id_proveedor, numero_documento, fecha_compra, subtotal, total, id_usuario, estado) VALUES ($prov, 'ANA-" . uniqid() . "', '$fecha', $total, $total, 1, '$estado')");
        $id = (int) $pdo->lastInsertId();
        $pdo->exec("INSERT INTO compras_detalle (id_compra, id_tanque, galones, costo_galon, importe) VALUES ($id, $tanque, $galones, $costo, $total)");
    };
    $compra('2020-03-05', 1000, 11);
    $compra('2020-02-20', 1000, 12);
    $compra('2020-03-10', 1000, 50, 'ANULADA');
    $compra('2019-06-01', 1000, 1);

    // Turnos: dos cerrados y uno abierto, que no cuenta.
    $turno = function ($usuario, $fecha, $tipo, $estado, $ventas, $galones, $faltante, array $lecturas) use ($pdo) {
        $pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado, total_ventas, total_galones, monto_faltante, monto_credito)
                    VALUES ('ANA-" . substr(uniqid(), -8) . "', $usuario, '$tipo', '$fecha', '$estado', $ventas, $galones, $faltante, 0)");
        $id = (int) $pdo->lastInsertId();
        foreach ($lecturas as [$s, $gal, $soles]) {
            $pdo->exec("INSERT INTO lecturas_turno (id_reporte, id_surtidor, galones_vendidos, precio_galon, total_soles) VALUES ($id, $s, $gal, " . round($soles / $gal, 3) . ", $soles)");
        }
    };
    $turno($g1, '2020-03-02', 'DIA', 'CERRADO', 1500, 100, -20, [[$diesel['id_surtidor'], 100, 1500]]);
    $turno($g2, '2020-03-03', 'NOCHE', 'CERRADO', 2600, 200, 0, [[$diesel['id_surtidor'], 100, 1500], [$regular['id_surtidor'], 100, 1100]]);
    $turno($g1, '2020-03-04', 'DIA', 'ABIERTO', 9999, 999, 0, [[$diesel['id_surtidor'], 999, 9999]]);

    // Ventas individuales: lunes a las 8 (80 en total), martes a las 20 (100) y una anulada.
    $venta = fn($cuando, $total, $estado = 'REGISTRADA') => $pdo->exec("INSERT INTO ventas (id_usuario, id_surtidor, id_producto, galones, precio_galon, total, id_tipo_pago, estado, creado_en)
        VALUES ($g1, {$diesel['id_surtidor']}, {$diesel['id_producto']}, " . round($total / 15, 3) . ", 15, $total, $pago, '$estado', '$cuando')");
    $venta('2020-03-02 08:15:00', 50);
    $venta('2020-03-02 08:40:00', 30);
    $venta('2020-03-03 20:10:00', 100);
    $venta('2020-03-03 21:00:00', 500, 'ANULADA');

    // Créditos de un cliente: vencidos hace 45 y 100 días, uno por vencer y uno pagado.
    $pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc) VALUES ('Cliente moroso de prueba', '20999999992')");
    $cliente = (int) $pdo->lastInsertId();
    $credito = fn($dias, $saldo, $estado = 'PENDIENTE') => $pdo->exec("INSERT INTO ventas_credito (id_cliente, numero_vale, monto, saldo_pendiente, estado, fecha_vencimiento)
        VALUES ($cliente, 'ANA-" . uniqid() . "', " . max($saldo, 100) . ", $saldo, '$estado', DATE_ADD(CURDATE(), INTERVAL $dias DAY))");
    $credito(-45, 300);
    $credito(-100, 200);
    $credito(10, 150);
    $credito(-200, 0, 'PAGADO');

    echo "1. Acceso\n";
    comprobar('el grifero no entra al análisis', $grifero->pedir($R . 'datos.php', ['seccion' => 'morosidad'])['codigo'] === 403);
    comprobar('el grifero no ve la pantalla ni el PDF', $grifero->pedir('view/reportes/view_analisis.php')['codigo'] === 403 && $grifero->pedir('view/MPDF/REPORTE/analisis.php')['codigo'] === 403);
    comprobar('supervisor y contador tienen el permiso', (int) $pdo->query("SELECT COUNT(*) FROM roles_capacidades WHERE capacidad = 'reportes_analisis' AND rol IN ('SUPERVISOR', 'CONTADOR')")->fetchColumn() === 2);
    comprobar('aparece en el menú de Reportes', strpos($admin->pedir('view/index.php')['cuerpo'], 'reportes/view_analisis.php') !== false);
    comprobar('rechaza un periodo invertido', $falla($admin->json($R . 'datos.php', ['seccion' => 'griferos', 'desde' => '2020-03-31', 'hasta' => '2020-03-01'])));
    comprobar('rechaza un periodo de más de un año', $falla($admin->json($R . 'datos.php', ['seccion' => 'griferos', 'desde' => '2018-01-01', 'hasta' => '2020-03-01'])));
    comprobar('rechaza una sección desconocida', $falla($admin->json($R . 'datos.php', ['seccion' => 'otra'] + $periodo)));

    echo "\n2. Rentabilidad\n";
    $r = $admin->json($R . 'datos.php', ['seccion' => 'rentabilidad'] + $periodo);
    $prod = [];
    foreach ($r['productos'] ?? [] as $p) { $prod[(int) $p['id_producto']] = $p; }
    $d = $prod[(int) $diesel['id_producto']] ?? null;
    $rg = $prod[(int) $regular['id_producto']] ?? null;
    comprobar('suma galones e ingresos de los turnos cerrados, sin el abierto', $d !== null && $casi($d['galones'], 200) && $casi($d['ingresos'], 3000), json_encode($d));
    comprobar('costo promedio ponderado de compras recientes, sin anuladas ni antiguas', $d !== null && $casi($d['costo_galon'], 11.5), json_encode($d));
    comprobar('margen bruto y porcentaje', $d !== null && $casi($d['margen'], 700) && $casi($d['margen_pct'], 23.3));
    comprobar('sin compras no inventa un costo', $rg !== null && $rg['costo_galon'] === null && $rg['margen'] === null);
    comprobar('el margen total solo usa productos con costo', $casi($r['totales']['ingresos'] ?? null, 4100) && $casi($r['totales']['margen'], 700) && $casi($r['totales']['margen_pct'], 23.3) && $r['totales']['sin_costo'] === 1, json_encode($r['totales'] ?? null));

    echo "\n3. Ranking de griferos\n";
    $g = $admin->json($R . 'datos.php', ['seccion' => 'griferos'] + $periodo)['griferos'] ?? [];
    $por = [];
    foreach ($g as $f) { $por[(int) $f['id_usuario']] = $f; }
    comprobar('ordena por ventas', count($g) === 2 && (int) $g[0]['id_usuario'] === $g2 && $g[0]['puesto'] === 1, json_encode($g));
    comprobar('cuenta faltantes y turnos cuadrados', $casi($por[$g1]['faltantes'] ?? null, 20) && ($por[$g1]['cuadre_pct'] ?? -1) === 0 && ($por[$g2]['cuadre_pct'] ?? -1) === 100);
    comprobar('promedio por turno', $casi($por[$g2]['promedio_turno'] ?? null, 2600) && ($por[$g1]['turnos'] ?? 0) === 1);

    echo "\n4. Morosidad\n";
    $m = $admin->json($R . 'datos.php', ['seccion' => 'morosidad']);
    $c = array_values(array_filter($m['clientes'] ?? [], fn($x) => (int) $x['id_cliente'] === $cliente))[0] ?? null;
    comprobar('suma lo pendiente sin los créditos pagados', $c !== null && $casi($c['pendiente'], 650) && (int) $c['vales'] === 3, json_encode($c));
    comprobar('reparte el vencido por antigüedad', $c !== null && $casi($c['por_vencer'], 150) && $casi($c['d31_60'], 300) && $casi($c['d90'], 200) && $casi($c['d1_30'], 0) && $casi($c['vencido'], 500));
    comprobar('días del vale más atrasado', $c !== null && $c['dias_max'] === 100);
    comprobar('primero quien debe más vencido', ($m['clientes'][0]['vencido'] ?? 0) >= ($c['vencido'] ?? 0));

    echo "\n5. Ventas por horario\n";
    $h = $admin->json($R . 'datos.php', ['seccion' => 'horario'] + $periodo);
    $celda = fn($dia, $hora) => array_values(array_filter($h['celdas'] ?? [], fn($x) => $x['dia'] === $dia && $x['hora'] === $hora))[0] ?? null;
    comprobar('agrupa por día y hora sin anuladas', count($h['celdas'] ?? []) === 2 && $casi($celda(2, 8)['total'] ?? null, 80) && ($celda(2, 8)['ventas'] ?? 0) === 2 && $casi($celda(3, 20)['total'] ?? null, 100), json_encode($h['celdas'] ?? null));
    comprobar('hora pico', ($h['hora_pico'] ?? null) === 20);
    $t = array_values(array_filter($h['turnos'] ?? [], fn($x) => $x['dia'] === 3 && $x['turno'] === 'NOCHE'))[0] ?? null;
    comprobar('promedio por día de la semana y turno, sin turnos abiertos', $t !== null && $casi($t['promedio'], 2600) && count($h['turnos']) === 2, json_encode($h['turnos'] ?? null));

    echo "\n6. Exportación\n";
    $e = $admin->pedir($R . 'exportar.php?seccion=rentabilidad&' . http_build_query($periodo));
    comprobar('CSV en UTF-8 para Excel', $e['codigo'] === 200 && str_starts_with($e['tipo'], 'text/csv') && str_starts_with($e['cuerpo'], "\xEF\xBB\xBF"), $e['tipo']);
    comprobar('con los valores calculados', strpos($e['cuerpo'], ';200.000;3000.00;15.000;11.5000;2300.00;700.00;23.3') !== false, substr($e['cuerpo'], 0, 300));
    $e = $admin->pedir($R . 'exportar.php?seccion=morosidad');
    comprobar('la morosidad también se exporta', $e['codigo'] === 200 && strpos($e['cuerpo'], 'Cliente moroso de prueba') !== false);
    comprobar('una sección desconocida no descarga nada', $admin->pedir($R . 'exportar.php?seccion=otra')['codigo'] === 400);
    $p = $admin->pedir('view/MPDF/REPORTE/analisis.php?' . http_build_query($periodo));
    comprobar('genera el informe en PDF', $p['codigo'] === 200 && str_starts_with($p['cuerpo'], '%PDF'), $p['tipo'] . ' ' . substr($p['cuerpo'], 0, 200));
} finally {
    $pdo->exec("DELETE FROM ventas_credito WHERE id_credito > {$max['ventas_credito']}");
    $pdo->exec("DELETE FROM clientes WHERE id_cliente > {$max['clientes']}");
    $pdo->exec("DELETE FROM ventas WHERE id_venta > {$max['ventas']}");
    $pdo->exec("DELETE FROM lecturas_turno WHERE id_reporte > {$max['reportes_turno']}");
    $pdo->exec("DELETE FROM reportes_turno WHERE id_reporte > {$max['reportes_turno']}");
    $pdo->exec("DELETE FROM compras_detalle WHERE id_compra > {$max['compras']}");
    $pdo->exec("DELETE FROM compras WHERE id_compra > {$max['compras']}");
    $pdo->exec("DELETE FROM tanques WHERE id_tanque > {$max['tanques']}");
    $pdo->exec("DELETE FROM proveedores WHERE id_proveedor > {$max['proveedores']}");
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
