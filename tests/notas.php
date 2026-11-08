<?php
/**
 * Prueba de las notas de crédito y débito y de la factura de vales, contra una imitación de Nubefact. Requiere los datos de demostración,
 * NUBEFACT_RUTA=http://127.0.0.1:8766/api en .env y el simulador tests/mock_nubefact.php en marcha.
 *
 *   php tests/notas.php <MOCK_DIR>
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$dirMock = rtrim($argv[1], '/\\') . '/';
$base = rtrim(getenv('GRIFO_BASE') ?: 'http://localhost/grifo_grau', '/');
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'not'); }
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

const CPE = 'controller/facturacion/controlador_';
const GUARDAR = 'controller/configuracion/controlador_guardar.php';
const MODULO = 'controller/configuracion/controlador_cambiar_modulo.php';

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$modo = fn(string $m) => file_put_contents($dirMock . 'modo', $m);
$enviados = function () use ($dirMock) {
    $f = $dirMock . 'peticiones.log';
    $todas = is_file($f) ? array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($f)))) : [];
    return array_values(array_filter(array_map(fn($p) => $p['datos'] ?? [], $todas), fn($d) => ($d['operacion'] ?? '') === 'generar_comprobante'));
};
$ultimo = function () use ($enviados) { $l = $enviados(); return $l ? $l[count($l) - 1] : []; };
foreach (['documentos.json', 'peticiones.log'] as $f) { if (is_file($dirMock . $f)) { unlink($dirMock . $f); } }
$modo('ok');

$maxComprobante = (int) $pdo->query('SELECT COALESCE(MAX(id_comprobante), 0) FROM comprobantes')->fetchColumn();
$maxAuditoria = (int) $pdo->query('SELECT COALESCE(MAX(id_auditoria), 0) FROM auditoria')->fetchColumn();
$maxCredito = (int) $pdo->query('SELECT COALESCE(MAX(id_credito), 0) FROM ventas_credito')->fetchColumn();
$maxCliente = (int) $pdo->query('SELECT COALESCE(MAX(id_cliente), 0) FROM clientes')->fetchColumn();
$seriesAntes = $pdo->query('SELECT serie, ultimo_numero FROM series_comprobante')->fetchAll(PDO::FETCH_KEY_PAIR);
$configAntes = $pdo->query("SELECT clave, valor FROM configuracion WHERE clave IN ('factura_token', 'factura_ruta')")->fetchAll(PDO::FETCH_KEY_PAIR);
$moduloAntes = (int) $pdo->query("SELECT activo FROM modulos WHERE codigo = 'facturacion'")->fetchColumn();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$json = fn(Cliente $c, string $r, array $p = []) => $c->pedir($r, $p)['json'] ?? [];
$falla = fn(array $r, string $texto = '') => ($r['success'] ?? true) === false && ($texto === '' || mb_stripos((string) ($r['message'] ?? ''), $texto) !== false);

try {
    $admin->pedir(MODULO, ['codigo' => 'facturacion', 'activo' => '1']);
    $admin->pedir(GUARDAR, ['factura_token' => 'token-de-prueba']);
    $pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc, direccion) VALUES ('TRANSPORTES NOTAS SAC', '20100000017', 'AV GRAU 2')");
    $cliRuc = (int) $pdo->lastInsertId();
    $pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc) VALUES ('PERSONA NOTAS', '45678912')");
    $cliDni = (int) $pdo->lastInsertId();
    $vale = function (int $cliente, string $numero, float $monto, string $estado = 'PENDIENTE') use ($pdo) {
        $pdo->exec("INSERT INTO ventas_credito (id_cliente, numero_vale, placa, monto, saldo_pendiente, estado, created_at) VALUES ($cliente, '$numero', 'ABC123', $monto, $monto, '$estado', NOW())");
        return (int) $pdo->lastInsertId();
    };
    $v1 = $vale($cliRuc, 'N-1', 100);
    $v2 = $vale($cliRuc, 'N-2', 50.50);
    $v3 = $vale($cliRuc, 'N-3', 20);
    $vale($cliRuc, 'N-4', 999, 'ANULADO');
    $vd = $vale($cliDni, 'N-5', 40);
    $hoy = date('Y-m-d');

    echo "1. Acceso y configuración\n";
    comprobar('el grifero no puede facturar vales', $grifero->pedir(CPE . 'facturar_vales.php', ['id_cliente' => $cliRuc, 'vales' => "$v1", 'tipo' => 'FACTURA'])['codigo'] === 403);
    comprobar('la serie de nota de crédito de factura debe empezar con F', $falla($json($admin, GUARDAR, ['factura_serie_nc_factura' => 'B001'])));
    comprobar('la de nota de débito de boleta debe empezar con B', $falla($json($admin, GUARDAR, ['factura_serie_nd_boleta' => 'F001'])));

    echo "\n2. Facturar vales\n";
    $lista = $json($admin, CPE . 'vales_cliente.php', ['id_cliente' => $cliRuc, 'desde' => $hoy, 'hasta' => $hoy]);
    comprobar('lista los vales del cliente sin los anulados', count($lista['data'] ?? []) === 3, json_encode($lista));
    comprobar('el cliente aparece entre los que tienen vales por facturar', in_array($cliRuc, array_map('intval', array_column($json($admin, CPE . 'datos_notas.php')['clientes'] ?? [], 'id_cliente')), true));
    comprobar('una factura exige RUC', $falla($json($admin, CPE . 'facturar_vales.php', ['id_cliente' => $cliDni, 'vales' => "$vd", 'tipo' => 'FACTURA']), 'RUC'));
    comprobar('no acepta vales de otro cliente', $falla($json($admin, CPE . 'facturar_vales.php', ['id_cliente' => $cliRuc, 'vales' => "$v1,$vd", 'tipo' => 'FACTURA'])));
    $r = $json($admin, CPE . 'facturar_vales.php', ['id_cliente' => $cliRuc, 'vales' => "$v1,$v2", 'tipo' => 'FACTURA']);
    comprobar('emite una factura por dos vales', ($r['success'] ?? false) === true && ($r['estado'] ?? '') === 'ACEPTADO', json_encode($r));
    $idFac = (int) ($r['id_comprobante'] ?? 0);
    $doc = $ultimo();
    comprobar('con una línea por vale y el total de ambos', count($doc['items'] ?? []) === 2 && ($doc['total'] ?? '') === '150.50' && ($doc['tipo_de_comprobante'] ?? '') === '1', json_encode($doc));
    $sumaGravada = array_sum(array_map(fn($i) => (float) $i['subtotal'], $doc['items'] ?? []));
    $sumaIgv = array_sum(array_map(fn($i) => (float) $i['igv'], $doc['items'] ?? []));
    comprobar('las líneas suman exactamente la gravada y el IGV del comprobante', abs($sumaGravada - (float) ($doc['total_gravada'] ?? 0)) < 0.001 && abs($sumaIgv - (float) ($doc['total_igv'] ?? 0)) < 0.001);
    comprobar('cada línea nombra su vale y la placa', str_contains($doc['items'][0]['descripcion'] ?? '', 'vale N-1') && str_contains($doc['items'][0]['descripcion'] ?? '', 'ABC123'));
    comprobar('un vale facturado no se factura dos veces', $falla($json($admin, CPE . 'facturar_vales.php', ['id_cliente' => $cliRuc, 'vales' => "$v1", 'tipo' => 'FACTURA'])));
    comprobar('y deja de aparecer en la lista', count($json($admin, CPE . 'vales_cliente.php', ['id_cliente' => $cliRuc, 'desde' => $hoy, 'hasta' => $hoy])['data'] ?? []) === 1);

    echo "\n3. Notas de crédito\n";
    comprobar('exige el sustento', $falla($json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '09', 'sustento' => '', 'monto' => 10])));
    comprobar('rechaza un motivo que no es del catálogo', $falla($json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '99', 'sustento' => 'x', 'monto' => 10])));
    comprobar('no puede pasar del total', $falla($json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '09', 'sustento' => 'Rebaja', 'monto' => 200]), 'no pasar'));
    $r = $json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '09', 'sustento' => 'Rebaja por pronto pago', 'monto' => 10]);
    comprobar('emite una nota de crédito parcial', ($r['success'] ?? false) === true && str_starts_with($r['comprobante'] ?? '', 'FC01-'), json_encode($r));
    $doc = $ultimo();
    comprobar('indica el documento que modifica y el motivo', ($doc['tipo_de_comprobante'] ?? '') === '3' && ($doc['documento_que_se_modifica_tipo'] ?? '') === '1'
        && ($doc['documento_que_se_modifica_serie'] ?? '') === 'F001' && ($doc['tipo_de_nota_de_credito'] ?? '') === '9' && ($doc['total'] ?? '') === '10.00', json_encode($doc));
    $r = $json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '01', 'sustento' => 'Cliente anuló el pedido', 'monto' => 1]);
    $montoNc = (float) $pdo->query('SELECT total FROM comprobantes WHERE id_comprobante = ' . (int) ($r['id_comprobante'] ?? 0))->fetchColumn();
    comprobar('la de anulación va por todo lo que queda, sin importar el monto enviado', ($r['success'] ?? false) === true && abs($montoNc - 140.50) < 0.001, json_encode($r) . " total $montoNc");
    comprobar('con el comprobante ya cubierto, no admite otra', $falla($json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NCREDITO', 'motivo' => '09', 'sustento' => 'Otra', 'monto' => 1]), 'todo su importe'));
    $idNc = (int) $pdo->query("SELECT MAX(id_comprobante) FROM comprobantes WHERE tipo = 'NCREDITO'")->fetchColumn();
    comprobar('no se emite una nota sobre otra nota', $falla($json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idNc, 'clase' => 'NCREDITO', 'motivo' => '09', 'sustento' => 'x', 'monto' => 1])));
    comprobar('la nota queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE id_auditoria > $maxAuditoria AND accion = 'NOTA_CREDITO' AND detalle LIKE '%Rebaja por pronto pago%'")->fetchColumn() === 1);

    echo "\n4. Nota de débito\n";
    $r = $json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idFac, 'clase' => 'NDEBITO', 'motivo' => '01', 'sustento' => 'Intereses por pago tardío', 'monto' => 5]);
    $doc = $ultimo();
    comprobar('emite una nota de débito con su serie y motivo', ($r['success'] ?? false) === true && str_starts_with($r['comprobante'] ?? '', 'FD01-')
        && ($doc['tipo_de_comprobante'] ?? '') === '4' && ($doc['tipo_de_nota_de_debito'] ?? '') === '1', json_encode([$r, $doc]));

    echo "\n5. Boleta de vales y su nota\n";
    $r = $json($admin, CPE . 'facturar_vales.php', ['id_cliente' => $cliDni, 'vales' => "$vd", 'tipo' => 'BOLETA']);
    $idBol = (int) ($r['id_comprobante'] ?? 0);
    comprobar('emite una boleta de vales a un cliente con DNI', ($r['success'] ?? false) === true && str_starts_with($r['comprobante'] ?? '', 'B001-'), json_encode($r));
    $r = $json($admin, CPE . 'crear_nota.php', ['id_referencia' => $idBol, 'clase' => 'NCREDITO', 'motivo' => '06', 'sustento' => 'Devolución', 'monto' => 0]);
    comprobar('su nota de crédito usa la serie de boletas', ($r['success'] ?? false) === true && str_starts_with($r['comprobante'] ?? '', 'BC01-'), json_encode($r));

    echo "\n6. Anular libera los vales\n";
    $lst = $json($admin, CPE . 'listar_comprobantes.php', ['desde' => $hoy, 'hasta' => $hoy]);
    $fila = array_values(array_filter($lst['data'] ?? [], fn($x) => (int) $x['id_comprobante'] === $idFac))[0] ?? null;
    comprobar('la lista muestra cuánto queda y los vales del comprobante', $fila !== null && abs((float) $fila['restante']) < 0.001 && (int) $fila['vales'] === 2, json_encode($fila));
    $admin->pedir(CPE . 'anular_comprobante.php', ['id_comprobante' => $idFac, 'motivo' => 'ERROR EN EL CLIENTE']);
    $admin->pedir(CPE . 'procesar_cola.php', []);
    comprobar('la anulación se confirma', $pdo->query("SELECT estado FROM comprobantes WHERE id_comprobante = $idFac")->fetchColumn() === 'ANULADO');
    comprobar('y sus vales vuelven a poder facturarse', count($json($admin, CPE . 'vales_cliente.php', ['id_cliente' => $cliRuc, 'desde' => $hoy, 'hasta' => $hoy])['data'] ?? []) === 3);
} finally {
    $ids = "SELECT id_comprobante FROM comprobantes WHERE id_comprobante > $maxComprobante";
    $pdo->exec("DELETE FROM comprobante_vales WHERE id_comprobante IN ($ids)");
    $pdo->exec("DELETE FROM comprobante_items WHERE id_comprobante IN ($ids)");
    $pdo->exec("DELETE FROM comprobantes WHERE id_comprobante > $maxComprobante ORDER BY id_comprobante DESC");
    $pdo->exec("DELETE FROM ventas_credito WHERE id_credito > $maxCredito");
    $pdo->exec("DELETE FROM clientes WHERE id_cliente > $maxCliente");
    $pdo->exec('DELETE FROM series_comprobante');
    $s = $pdo->prepare('INSERT INTO series_comprobante (serie, ultimo_numero) VALUES (?, ?)');
    foreach ($seriesAntes as $serie => $n) { $s->execute([$serie, $n]); }
    $pdo->exec("UPDATE configuracion SET valor = NULL WHERE clave IN ('factura_token', 'factura_ruta')");
    $c = $pdo->prepare('UPDATE configuracion SET valor = ? WHERE clave = ?');
    foreach ($configAntes as $clave => $valor) { $c->execute([$valor, $clave]); }
    $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'facturacion'")->execute([$moduloAntes]);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
