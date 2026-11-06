<?php
/**
 * Prueba de inventario, compras y ventas contra el sistema en ejecución.
 * Requiere los datos de demostración. Limpia lo que crea y deja los módulos apagados.
 *
 *   php tests/inventario.php
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$base = 'http://localhost/grifo_grau';
$fallos = 0; $total = 0;

function comprobar(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $total;
    $total++;
    if (!$ok) { $fallos++; }
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : " -> $detalle") . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'inv'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $curl = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_HTTPHEADER => $this->csrf ? ['X-CSRF-Token: ' . $this->csrf] : [], CURLOPT_TIMEOUT => 60]);
        if ($post !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
        $cuerpo = (string) curl_exec($curl);
        return ['codigo' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'json' => json_decode($cuerpo, true)];
    }
    public function entrar(string $u, string $c): void
    {
        $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
    }
}

const INV = 'controller/inventario/controlador_';
const COM = 'controller/compras/controlador_';
const VEN = 'controller/ventas/controlador_';
const MODULO = 'controller/configuracion/controlador_cambiar_modulo.php';

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$stock = fn(int $id) => (float) $pdo->query("SELECT stock_actual FROM tanques WHERE id_tanque = $id")->fetchColumn();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$idGrifero = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();

$productos = $pdo->query('SELECT tipo, id_producto FROM productos')->fetchAll(PDO::FETCH_KEY_PAIR);
$hoy = date('Y-m-d');

/** Registra un turno con 10 galones por surtidor, como lo hace la pantalla del grifero. */
function turno(Cliente $quien, PDO $pdo): array
{
    $surtidores = $pdo->query("SELECT s.id_surtidor, s.lectura_actual, p.precio_actual FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE s.estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
    $lecturas = array_map(fn($s) => ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => (float) $s['lectura_actual'] + 10, 'precio' => $s['precio_actual']], $surtidores);
    return $quien->pedir('controller/turnos/controlador_registrar_turno_completo.php', [
        'numero_documento' => 'INV-' . random_int(10000, 99999), 'id_usuario' => 0, 'turno' => 'DIA', 'fecha' => date('Y-m-d'),
        'hora_inicio' => '06:00', 'hora_fin' => '14:00', 'lecturas' => json_encode($lecturas), 'pagos' => '[]', 'creditos' => '[]',
        'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => 0,
    ]);
}

echo "\n1. Módulos apagados\n";
$admin->pedir(MODULO, ['codigo' => 'inventario', 'activo' => '0']);
$admin->pedir(MODULO, ['codigo' => 'ventas', 'activo' => '0']);
comprobar('inventario apagado: no responde', $admin->pedir(INV . 'listar_tanques.php', [])['codigo'] === 403);
comprobar('ventas apagado: no responde', $admin->pedir(VEN . 'listar_ventas.php', [])['codigo'] === 403);
comprobar('la vista tampoco abre', $admin->pedir('view/inventario/view_compras.php')['codigo'] === 403);
$panel = $admin->pedir('view/index.php')['cuerpo'];
comprobar('no aparecen en el menú', strpos($panel, 'inventario/view_tanques') === false && strpos($panel, 'ventas/view_ventas') === false);
$r = turno($grifero, $pdo);
comprobar('el turno funciona con los módulos apagados', !empty($r['json']['success']), $r['cuerpo']);
$turnoApagado = (int) ($r['json']['id_reporte'] ?? 0);

$admin->pedir(MODULO, ['codigo' => 'inventario', 'activo' => '1']);
$admin->pedir(MODULO, ['codigo' => 'ventas', 'activo' => '1']);
$panel = $admin->pedir('view/index.php')['cuerpo'];
comprobar('encendidos aparecen en el menú', strpos($panel, 'inventario/view_tanques') !== false && strpos($panel, 'ventas/view_ventas') !== false);
foreach (['view/inventario/view_tanques.php', 'view/inventario/view_varillaje.php', 'view/inventario/view_compras.php', 'view/ventas/view_ventas.php'] as $vista) {
    comprobar('abre ' . basename($vista), $admin->pedir($vista)['codigo'] === 200);
}

echo "\n2. Tanques\n";
$r = $admin->pedir(INV . 'guardar_tanque.php', ['nombre' => 'T-DIESEL', 'id_producto' => $productos['DIESEL'], 'capacidad' => 5000, 'stock_minimo' => 500, 'stock_inicial' => 2000]);
comprobar('crea tanque con stock inicial', !empty($r['json']['success']), $r['cuerpo']);
$admin->pedir(INV . 'guardar_tanque.php', ['nombre' => 'T-REGULAR', 'id_producto' => $productos['REGULAR'], 'capacidad' => 5000, 'stock_minimo' => 500, 'stock_inicial' => 2000]);
$admin->pedir(INV . 'guardar_tanque.php', ['nombre' => 'T-PREMIUM', 'id_producto' => $productos['PREMIUM'], 'capacidad' => 3000, 'stock_minimo' => 2000, 'stock_inicial' => 1000]);
$tanques = $pdo->query('SELECT nombre, id_tanque FROM tanques')->fetchAll(PDO::FETCH_KEY_PAIR);
$diesel = (int) $tanques['T-DIESEL']; $regular = (int) $tanques['T-REGULAR']; $premium = (int) $tanques['T-PREMIUM'];
comprobar('stock inicial aplicado', $stock($diesel) === 2000.0);
$r = $admin->pedir(INV . 'guardar_tanque.php', ['nombre' => 'T-DIESEL', 'id_producto' => $productos['DIESEL'], 'capacidad' => 5000]);
comprobar('nombre duplicado rechazado', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(INV . 'guardar_tanque.php', ['nombre' => 'MAL', 'id_producto' => $productos['DIESEL'], 'capacidad' => 100, 'stock_minimo' => 200]);
comprobar('mínimo mayor que capacidad rechazado', ($r['json']['success'] ?? true) === false);
$lista = $admin->pedir(INV . 'listar_tanques.php', [])['json']['data'];
$bajo = array_column($lista, 'stock_bajo', 'nombre');
comprobar('alerta de stock bajo', (int) $bajo['T-PREMIUM'] === 1 && (int) $bajo['T-DIESEL'] === 0, json_encode($bajo));
comprobar('grifero no crea tanques', $grifero->pedir(INV . 'guardar_tanque.php', ['nombre' => 'X'])['codigo'] === 403);
comprobar('grifero no abre tanques', $grifero->pedir('view/inventario/view_tanques.php')['codigo'] === 403);

$sRegular = (int) $pdo->query("SELECT s.id_surtidor FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE p.tipo = 'REGULAR' LIMIT 1")->fetchColumn();
$r = $admin->pedir(INV . 'asignar_surtidor.php', ['id_surtidor' => $sRegular, 'id_tanque' => $diesel]);
comprobar('no asigna un surtidor a un tanque de otro combustible', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(INV . 'asignar_surtidor.php', ['id_surtidor' => $sRegular, 'id_tanque' => $regular]);
comprobar('asigna al tanque correcto', !empty($r['json']['success']), $r['cuerpo']);

echo "\n3. Compras\n";
$r = $admin->pedir(COM . 'guardar_proveedor.php', ['ruc' => '123', 'razon_social' => 'X']);
comprobar('RUC inválido rechazado', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(COM . 'guardar_proveedor.php', ['ruc' => '20100000001', 'razon_social' => 'PROVEEDOR <b>DEMO</b>', 'telefono' => '', 'direccion' => '']);
comprobar('crea proveedor', !empty($r['json']['success']), $r['cuerpo']);
$r = $admin->pedir(COM . 'guardar_proveedor.php', ['ruc' => '20100000001', 'razon_social' => 'OTRO']);
comprobar('RUC duplicado rechazado', ($r['json']['success'] ?? true) === false);
$idProveedor = (int) $pdo->query("SELECT id_proveedor FROM proveedores WHERE ruc = '20100000001'")->fetchColumn();

$lineas = fn($g, $costo = 12.5) => json_encode([['id_tanque' => $diesel, 'galones' => $g, 'costo_galon' => $costo]]);
$datos = ['id_proveedor' => $idProveedor, 'numero_documento' => 'F001-1', 'fecha_compra' => $hoy, 'fecha_vencimiento' => $hoy, 'percepcion' => 25, 'tipo_documento' => 'FACTURA'];
$antes = $stock($diesel);
$r = $admin->pedir(COM . 'registrar_compra.php', $datos + ['lineas' => $lineas(1000)]);
comprobar('registra la compra', !empty($r['json']['success']), $r['cuerpo']);
$idCompra = (int) ($r['json']['id_compra'] ?? 0);
comprobar('suma al stock', $stock($diesel) === $antes + 1000);
$compra = $pdo->query("SELECT * FROM compras WHERE id_compra = $idCompra")->fetch(PDO::FETCH_ASSOC);
comprobar('total con percepción', (float) $compra['total'] === 12525.0, $compra['total']);
$r = $admin->pedir(COM . 'registrar_compra.php', $datos + ['lineas' => $lineas(10)]);
comprobar('documento duplicado rechazado', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
comprobar('el duplicado no tocó el stock', $stock($diesel) === $antes + 1000);
$r = $admin->pedir(COM . 'registrar_compra.php', ['numero_documento' => 'F001-2', 'lineas' => $lineas(-5)] + $datos);
comprobar('galones negativos rechazados', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(COM . 'registrar_compra.php', ['numero_documento' => 'F001-3', 'fecha_compra' => date('Y-m-d', strtotime('+1 day')), 'lineas' => $lineas(5)] + $datos);
comprobar('fecha futura rechazada', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(COM . 'registrar_compra.php', ['numero_documento' => 'F001-4', 'lineas' => $lineas(9000)] + $datos);
comprobar('exceder la capacidad avisa sin bloquear', !empty($r['json']['avisos']), $r['cuerpo']);
$compraGrande = (int) ($r['json']['id_compra'] ?? 0);
$r = $admin->pedir(COM . 'anular_compra.php', ['id_compra' => $compraGrande, 'motivo' => '']);
comprobar('anular sin motivo rechazado', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(COM . 'anular_compra.php', ['id_compra' => $compraGrande, 'motivo' => 'prueba']);
comprobar('anular retira del stock', !empty($r['json']['success']) && $stock($diesel) === $antes + 1000, $r['cuerpo']);
$r = $admin->pedir(COM . 'anular_compra.php', ['id_compra' => $compraGrande, 'motivo' => 'prueba']);
comprobar('no se anula dos veces', ($r['json']['success'] ?? true) === false && $stock($diesel) === $antes + 1000);

$r = $admin->pedir(COM . 'registrar_pago.php', ['id_compra' => $idCompra, 'monto' => 99999, 'fecha_pago' => $hoy]);
comprobar('pago mayor al saldo rechazado', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(COM . 'registrar_pago.php', ['id_compra' => $idCompra, 'monto' => 5000, 'fecha_pago' => $hoy, 'medio' => 'TRANSFERENCIA']);
comprobar('pago parcial', !empty($r['json']['success']), $r['cuerpo']);
$fila = array_values(array_filter($admin->pedir(COM . 'listar_compras.php', ['filtro' => 'POR_PAGAR'])['json']['data'], fn($c) => (int) $c['id_compra'] === $idCompra))[0] ?? null;
comprobar('saldo correcto en por pagar', $fila && (float) $fila['saldo'] === 7525.0, json_encode($fila));
$r = $admin->pedir(COM . 'anular_compra.php', ['id_compra' => $idCompra, 'motivo' => 'x']);
comprobar('con pagos no se puede anular', ($r['json']['success'] ?? true) === false);
comprobar('grifero no accede a compras', $grifero->pedir(COM . 'listar_compras.php', [])['codigo'] === 403);

echo "\n4. Ventas individuales\n";
$sDiesel = (int) $pdo->query("SELECT s.id_surtidor FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE p.tipo = 'DIESEL' LIMIT 1")->fetchColumn();
$venta = ['id_surtidor' => $sDiesel, 'id_tipo_pago' => 4, 'modo' => 'SOLES', 'valor' => 150, 'precio' => 0.01, 'total' => 1];
$r = $grifero->pedir(VEN . 'registrar_venta.php', $venta);
comprobar('registra venta por soles', !empty($r['json']['success']) && (float) $r['json']['galones'] === 10.0, $r['cuerpo']);
comprobar('ignora el precio enviado por el navegador', (float) $pdo->query('SELECT precio_galon FROM ventas ORDER BY id_venta DESC LIMIT 1')->fetchColumn() === 15.0);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['modo' => 'GALONES', 'valor' => 2.5] + $venta);
comprobar('registra venta por galones', (float) ($r['json']['total'] ?? 0) === 37.5, $r['cuerpo']);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['valor' => -5] + $venta);
comprobar('monto negativo rechazado', ($r['json']['success'] ?? true) === false);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['id_tipo_pago' => 1] + $venta);
comprobar('Yape sin código rechazado', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'FACTURA'] + $venta);
comprobar('factura sin RUC rechazada', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
comprobar('grifero no anula ventas', $grifero->pedir(VEN . 'anular_venta.php', ['id_venta' => 1, 'motivo' => 'x'])['codigo'] === 403);
comprobar('grifero no ve la conciliación', $grifero->pedir(VEN . 'conciliacion.php', [])['codigo'] === 403);

$otro = new Cliente($base);
$otro->entrar('grifero2', 'grifero123');
$r = $otro->pedir(VEN . 'listar_ventas.php', []);
comprobar('un grifero no ve ventas de otro', count($r['json']['data'] ?? [1]) === 0, $r['cuerpo']);
comprobar('el dueño sí las ve', count($grifero->pedir(VEN . 'listar_ventas.php', [])['json']['data']) === 2);

$idTercera = (int) ($grifero->pedir(VEN . 'registrar_venta.php', ['valor' => 15] + $venta)['json']['id_venta'] ?? 0);
$r = $admin->pedir(VEN . 'anular_venta.php', ['id_venta' => $idTercera, 'motivo' => 'error']);
comprobar('el administrador anula una venta', !empty($r['json']['success']), $r['cuerpo']);

echo "\n5. Cierre de turno\n";
$sD = $stock($diesel); $sR = $stock($regular); $sP = $stock($premium);
$r = turno($grifero, $pdo);
comprobar('registra el turno', !empty($r['json']['success']), $r['cuerpo']);
$idTurno = (int) ($r['json']['id_reporte'] ?? 0);
comprobar('descuenta diesel (4 surtidores)', $stock($diesel) === $sD - 40, (string) $stock($diesel));
comprobar('descuenta regular', $stock($regular) === $sR - 40, (string) $stock($regular));
comprobar('descuenta premium', $stock($premium) === $sP - 40, (string) $stock($premium));
$kardex = $admin->pedir(INV . 'kardex.php', ['id_tanque' => $diesel])['json']['data'];
comprobar('el kardex refleja el turno y el saldo', $kardex[0]['tipo'] === 'VENTA_TURNO' && (float) $kardex[0]['saldo'] === $stock($diesel), json_encode($kardex[0]));
comprobar('vincula las ventas del grifero', (int) $pdo->query("SELECT COUNT(*) FROM ventas WHERE id_reporte = $idTurno")->fetchColumn() === 2);
comprobar('no vincula la venta anulada', (int) $pdo->query("SELECT COUNT(*) FROM ventas WHERE id_venta = $idTercera AND id_reporte IS NULL")->fetchColumn() === 1);

$conc = array_values(array_filter($admin->pedir(VEN . 'conciliacion.php', [])['json']['data'], fn($f) => (int) $f['id_reporte'] === $idTurno && strpos($f['producto'], 'DIESEL') !== false))[0] ?? null;
comprobar('la conciliación compara lecturas y ventas', $conc && (float) $conc['galones_lecturas'] === 40.0 && (float) $conc['galones_ventas'] === 12.5 && (float) $conc['diferencia'] === 27.5, json_encode($conc));

echo "\n6. Fallo de un módulo no bloquea el turno\n";
$pdo->exec('RENAME TABLE movimientos_tanque TO movimientos_tanque_x');
$r = turno($grifero, $pdo);
comprobar('sin la tabla de kardex, el turno se registra', !empty($r['json']['success']), $r['cuerpo']);
$turnoRoto = (int) ($r['json']['id_reporte'] ?? 0);
$pdo->exec('RENAME TABLE movimientos_tanque_x TO movimientos_tanque');
comprobar('y el efecto fallido no dejó el stock a medias', $stock($diesel) === $sD - 40, (string) $stock($diesel));

echo "\n7. Eliminar un turno revierte\n";
$r = $admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $idTurno, 'motivo' => 'prueba']);
comprobar('elimina el turno', strpos($r['cuerpo'], '"success":true') !== false, $r['cuerpo']);
comprobar('devuelve el stock', $stock($diesel) === $sD && $stock($regular) === $sR && $stock($premium) === $sP, (string) $stock($diesel));
comprobar('libera las ventas', (int) $pdo->query("SELECT COUNT(*) FROM ventas WHERE id_reporte = $idTurno")->fetchColumn() === 0);
$admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $turnoRoto, 'motivo' => 'prueba']);
comprobar('eliminar un turno sin kardex no altera el stock', $stock($diesel) === $sD, (string) $stock($diesel));
$admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $turnoApagado, 'motivo' => 'prueba']);

echo "\n8. Varillaje\n";
$actual = $stock($diesel);
$r = $grifero->pedir(INV . 'registrar_varillaje.php', ['id_tanque' => $diesel, 'fecha' => $hoy, 'galones_fisicos' => $actual - 100, 'medida_cm' => '']);
comprobar('el grifero registra una medición', !empty($r['json']['success']) && (float) $r['json']['diferencia'] === -100.0, $r['cuerpo']);
comprobar('detecta fuera de tolerancia', ($r['json']['fuera_de_tolerancia'] ?? false) === true);
comprobar('medir no altera el stock', $stock($diesel) === $actual);
$r = $grifero->pedir(INV . 'registrar_varillaje.php', ['id_tanque' => $diesel, 'fecha' => $hoy, 'galones_fisicos' => 99999]);
comprobar('medida mayor que la capacidad rechazada', ($r['json']['success'] ?? true) === false);
$r = $grifero->pedir(INV . 'registrar_varillaje.php', ['id_tanque' => $diesel, 'fecha' => '2099-01-01', 'galones_fisicos' => 10]);
comprobar('fecha futura rechazada', ($r['json']['success'] ?? true) === false);
$idVar = (int) $pdo->query('SELECT MAX(id_varillaje) FROM varillajes')->fetchColumn();
comprobar('grifero no aplica ajustes', $grifero->pedir(INV . 'aplicar_ajuste.php', ['id_varillaje' => $idVar])['codigo'] === 403);
$r = $admin->pedir(INV . 'aplicar_ajuste.php', ['id_varillaje' => $idVar]);
comprobar('el administrador ajusta el stock', !empty($r['json']['success']) && $stock($diesel) === $actual - 100, $r['cuerpo']);
$r = $admin->pedir(INV . 'aplicar_ajuste.php', ['id_varillaje' => $idVar]);
comprobar('no se ajusta dos veces', ($r['json']['success'] ?? true) === false && $stock($diesel) === $actual - 100);

echo "\n9. Consistencia del kardex\n";
foreach ([$diesel, $regular, $premium] as $t) {
    $suma = (float) $pdo->query("SELECT SUM(galones) FROM movimientos_tanque WHERE id_tanque = $t")->fetchColumn();
    comprobar("la suma de movimientos coincide con el stock (tanque $t)", abs($suma - $stock($t)) < 0.001, "$suma vs " . $stock($t));
}

echo "\n10. Limpieza\n";
foreach (['pagos_compra', 'compras_detalle', 'movimientos_tanque', 'varillajes', 'surtidor_tanque', 'compras', 'proveedores', 'tanques', 'ventas'] as $tabla) {
    $pdo->exec("DELETE FROM $tabla");
}
$admin->pedir(MODULO, ['codigo' => 'inventario', 'activo' => '0']);
$admin->pedir(MODULO, ['codigo' => 'ventas', 'activo' => '0']);
comprobar('módulos apagados de nuevo', (int) $pdo->query("SELECT COUNT(*) FROM modulos WHERE codigo IN ('inventario','ventas') AND activo = 1")->fetchColumn() === 0);

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
