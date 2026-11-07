<?php
/**
 * Prueba del módulo de gastos contra el sistema en ejecución.
 * Requiere los datos de demostración. Deja el módulo y los datos como estaban.
 *
 *   php tests/gastos.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'gas'); }
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
$moduloAntes = (int) $pdo->query("SELECT activo FROM modulos WHERE codigo = 'gastos'")->fetchColumn();
$maxGasto = (int) $pdo->query('SELECT COALESCE(MAX(id_gasto), 0) FROM gastos_grifo')->fetchColumn();
$maxCategoria = (int) $pdo->query('SELECT COALESCE(MAX(id_categoria), 0) FROM categorias_gasto')->fetchColumn();
$modulo = fn(int $a) => $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'gastos'")->execute([$a]);

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$R = 'controller/gastos_grifo/controlador_';
$hoy = date('Y-m-d');

try {
    echo "1. Acceso\n";
    $modulo(0);
    $r = $admin->pedir($R . 'listar.php', []);
    comprobar('con el módulo apagado no responde', $r['codigo'] === 403, (string) $r['codigo']);
    comprobar('con el módulo apagado no aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'gastos_grifo/view_gastos.php') === false);
    $modulo(1);
    comprobar('con el módulo activo aparece en el menú del administrador', strpos($admin->pedir('view/index.php')['cuerpo'], 'gastos_grifo/view_gastos.php') !== false);
    $r = $grifero->pedir($R . 'registrar.php', ['fecha' => $hoy, 'id_categoria' => 1, 'descripcion' => 'X', 'monto' => 5, 'medio_pago' => 'EFECTIVO']);
    comprobar('el grifero no puede registrar gastos', $r['codigo'] === 403, (string) $r['codigo']);
    comprobar('el contador y el supervisor tienen el permiso por defecto', (int) $pdo->query("SELECT COUNT(*) FROM roles_capacidades WHERE capacidad = 'gastos_gestionar' AND rol IN ('CONTADOR', 'SUPERVISOR')")->fetchColumn() === 2);

    echo "\n2. Registro y validaciones\n";
    $datos = $admin->json($R . 'datos.php');
    $categoria = (int) ($datos['categorias'][0]['id_categoria'] ?? 0);
    comprobar('trae categorías y medios de pago', $categoria > 0 && count($datos['medios'] ?? []) === 5);
    $mal = fn(array $cambios) => $admin->json($R . 'registrar.php', $cambios + ['fecha' => $hoy, 'id_categoria' => $categoria, 'descripcion' => 'Prueba', 'monto' => '10', 'medio_pago' => 'EFECTIVO']);
    comprobar('rechaza una fecha futura', ($mal(['fecha' => date('Y-m-d', strtotime('+1 day'))])['success'] ?? true) === false);
    comprobar('rechaza un monto en cero', ($mal(['monto' => '0'])['success'] ?? true) === false);
    comprobar('rechaza un gasto sin descripción', ($mal(['descripcion' => '  '])['success'] ?? true) === false);
    comprobar('rechaza un medio de pago desconocido', ($mal(['medio_pago' => 'BITCOIN'])['success'] ?? true) === false);
    $r = $admin->json($R . 'registrar.php', ['fecha' => $hoy, 'id_categoria' => $categoria, 'descripcion' => 'Recibo de luz de prueba', 'monto' => '150.50', 'medio_pago' => 'TRANSFERENCIA', 'comprobante' => 'R-001']);
    $id1 = (int) ($r['id_gasto'] ?? 0);
    comprobar('registra un gasto válido', $id1 > 0, json_encode($r));
    $r = $admin->json($R . 'registrar.php', ['fecha' => $hoy, 'id_categoria' => $categoria, 'descripcion' => '=HYPERLINK("http://x")', 'monto' => '49.50', 'medio_pago' => 'EFECTIVO']);
    $id2 = (int) ($r['id_gasto'] ?? 0);
    $fila = $pdo->query("SELECT id_usuario, monto FROM gastos_grifo WHERE id_gasto = $id1")->fetch(PDO::FETCH_ASSOC);
    comprobar('guarda quién lo registró y el monto exacto', (int) $fila['id_usuario'] === 1 && $fila['monto'] === '150.50', json_encode($fila));

    echo "\n3. Listado y resumen\n";
    $l = $admin->json($R . 'listar.php', ['desde' => $hoy, 'hasta' => $hoy]);
    $ids = array_map('intval', array_column($l['gastos'] ?? [], 'id_gasto'));
    comprobar('lista los gastos del periodo', in_array($id1, $ids, true) && in_array($id2, $ids, true));
    $esperado = (float) $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM gastos_grifo WHERE fecha = '$hoy' AND estado = 'ACTIVO'")->fetchColumn();
    comprobar('el resumen suma los gastos activos', abs(($l['resumen']['gastos'] ?? -1) - $esperado) < 0.001, json_encode($l['resumen'] ?? null));
    [$ventas, $otros] = array_map('floatval', $pdo->query("SELECT COALESCE(SUM(total_ventas), 0), COALESCE(SUM(monto_otros_gastos), 0) FROM reportes_turno WHERE fecha_reporte = '$hoy'")->fetch(PDO::FETCH_NUM));
    comprobar('las ventas y los otros gastos salen de los turnos', abs($l['resumen']['ventas'] - $ventas) < 0.001 && abs($l['resumen']['otros_gastos_turno'] - $otros) < 0.001);
    $r = $l['resumen'];
    comprobar('la diferencia es ventas − compras − gastos', abs($r['diferencia'] - ($r['ventas'] - $r['compras'] - $r['gastos'] - $r['otros_gastos_turno'])) < 0.001);
    $cat = array_values(array_filter($r['por_categoria'], fn($c) => $c['total'] >= 200))[0] ?? null;
    comprobar('agrupa por categoría', $cat !== null && $cat['cantidad'] >= 2);
    $filtrado = $admin->json($R . 'listar.php', ['desde' => $hoy, 'hasta' => $hoy, 'id_categoria' => $categoria + 1]);
    comprobar('filtra por categoría', !in_array($id1, array_map('intval', array_column($filtrado['gastos'] ?? [], 'id_gasto')), true));

    echo "\n4. Anulación\n";
    comprobar('anular exige motivo', ($admin->json($R . 'anular.php', ['id_gasto' => $id2, 'motivo' => ''])['success'] ?? true) === false);
    comprobar('anula con motivo', ($admin->json($R . 'anular.php', ['id_gasto' => $id2, 'motivo' => 'Registrado por error'])['success'] ?? false) === true);
    comprobar('no se anula dos veces', ($admin->json($R . 'anular.php', ['id_gasto' => $id2, 'motivo' => 'otra vez'])['success'] ?? true) === false);
    $l = $admin->json($R . 'listar.php', ['desde' => $hoy, 'hasta' => $hoy]);
    comprobar('el anulado sale del listado y del resumen', !in_array($id2, array_map('intval', array_column($l['gastos'], 'id_gasto')), true) && abs($l['resumen']['gastos'] - ($esperado - 49.50)) < 0.001);
    $l = $admin->json($R . 'listar.php', ['desde' => $hoy, 'hasta' => $hoy, 'anulados' => '1']);
    $an = array_values(array_filter($l['gastos'], fn($g) => (int) $g['id_gasto'] === $id2))[0] ?? null;
    comprobar('con «incluir anulados» se ve con su motivo', $an !== null && $an['estado'] === 'ANULADO' && $an['motivo_anulacion'] === 'Registrado por error');
    $fila = $pdo->query("SELECT anulado_por, anulado_en FROM gastos_grifo WHERE id_gasto = $id2")->fetch(PDO::FETCH_ASSOC);
    comprobar('guarda quién y cuándo anuló', (int) $fila['anulado_por'] === 1 && $fila['anulado_en'] !== null);
    comprobar('la anulación queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'anular' AND detalle LIKE '%\"id_gasto\":\"$id2\"%'")->fetchColumn() >= 1);

    echo "\n5. Categorías\n";
    $r = $admin->json($R . 'guardar_categoria.php', ['nombre' => 'Categoría de prueba', 'activo' => '1']);
    $idCat = (int) ($r['id_categoria'] ?? 0);
    comprobar('crea una categoría', $idCat > 0, json_encode($r));
    comprobar('no repite nombres', ($admin->json($R . 'guardar_categoria.php', ['nombre' => 'Categoría de prueba', 'activo' => '1'])['success'] ?? true) === false);
    $admin->json($R . 'guardar_categoria.php', ['id_categoria' => $idCat, 'nombre' => 'Categoría de prueba', 'activo' => '0']);
    comprobar('una categoría inactiva no admite gastos nuevos', ($admin->json($R . 'registrar.php', ['fecha' => $hoy, 'id_categoria' => $idCat, 'descripcion' => 'X', 'monto' => '5', 'medio_pago' => 'EFECTIVO'])['success'] ?? true) === false);

    echo "\n6. Exportación a Excel\n";
    $r = $admin->pedir($R . 'exportar.php?desde=' . $hoy . '&hasta=' . $hoy . '&anulados=1');
    comprobar('descarga un CSV en UTF-8 para Excel', $r['codigo'] === 200 && strpos($r['tipo'], 'text/csv') === 0 && str_starts_with($r['cuerpo'], "\xEF\xBB\xBF"), $r['tipo']);
    comprobar('incluye los gastos con punto y coma', strpos($r['cuerpo'], '"Recibo de luz de prueba";') !== false && strpos($r['cuerpo'], '150.50') !== false);
    comprobar('una descripción con fórmula no se ejecuta en Excel', strpos($r['cuerpo'], "'=HYPERLINK") !== false);
    $r = $grifero->pedir($R . 'exportar.php');
    comprobar('el grifero no puede exportar', $r['codigo'] === 403, (string) $r['codigo']);
} finally {
    $pdo->exec("DELETE FROM gastos_grifo WHERE id_gasto > $maxGasto");
    $pdo->exec("DELETE FROM categorias_gasto WHERE id_categoria > $maxCategoria");
    $modulo($moduloAntes);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
