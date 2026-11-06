<?php
/**
 * Prueba de la facturación contra una imitación de Nubefact. Requiere los datos de demostración,
 * NUBEFACT_RUTA=http://127.0.0.1:8766/api en .env y el simulador tests/mock_nubefact.php en marcha.
 *
 *   php tests/facturacion.php <MOCK_DIR>
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$dirMock = rtrim($argv[1], '/\\') . '/';
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'fac'); }
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
const VEN = 'controller/ventas/controlador_';
const GUARDAR = 'controller/configuracion/controlador_guardar.php';
const MODULO = 'controller/configuracion/controlador_cambiar_modulo.php';

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$modo = fn(string $m) => file_put_contents($dirMock . 'modo', $m);
$peticiones = function () use ($dirMock) {
    $f = $dirMock . 'peticiones.log';
    return is_file($f) ? array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($f)))) : [];
};
$limpiarLog = fn() => file_put_contents($dirMock . 'peticiones.log', '');
$estado = fn(int $id) => $pdo->query("SELECT estado FROM comprobantes WHERE id_venta = $id")->fetchColumn();

foreach (['documentos.json', 'peticiones.log'] as $f) { if (is_file($dirMock . $f)) { unlink($dirMock . $f); } }
$modo('ok');

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$admin->pedir(MODULO, ['codigo' => 'ventas', 'activo' => '1']);

$pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc, direccion) VALUES ('EMPRESA PRUEBA SAC', '20100000009', 'AV LIMA 1')");
$cliRuc = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc) VALUES ('PERSONA PRUEBA', '12345678')");
$cliDni = (int) $pdo->lastInsertId();
$surtidor = (int) $pdo->query("SELECT s.id_surtidor FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE p.tipo = 'DIESEL' LIMIT 1")->fetchColumn();
$venta = ['id_surtidor' => $surtidor, 'id_tipo_pago' => 4, 'modo' => 'SOLES', 'valor' => 150];

echo "\n1. Módulo apagado\n";
$admin->pedir(MODULO, ['codigo' => 'facturacion', 'activo' => '0']);
comprobar('la pantalla no abre', $admin->pedir('view/facturacion/view_comprobantes.php')['codigo'] === 403);
comprobar('no aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'facturacion/view_comprobantes') === false);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
comprobar('la venta se registra sin comprobante', !empty($r['json']['success']) && empty($r['json']['comprobante']), $r['cuerpo']);
comprobar('no se contacta a Nubefact', count($peticiones()) === 0);
$idv = (int) $r['json']['id_venta'];
comprobar('la venta queda sin tipo de comprobante', $pdo->query("SELECT tipo_comprobante FROM ventas WHERE id_venta = $idv")->fetchColumn() === 'NINGUNO');
comprobar('el formulario no ofrece el selector', strpos($grifero->pedir('view/ventas/view_ventas.php')['cuerpo'], '<option value="BOLETA">') === false);

echo "\n2. Configuración\n";
$admin->pedir(MODULO, ['codigo' => 'facturacion', 'activo' => '1']);
comprobar('encendido aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'facturacion/view_comprobantes') !== false);
comprobar('el grifero no abre comprobantes', $grifero->pedir('view/facturacion/view_comprobantes.php')['codigo'] === 403 && $grifero->pedir(CPE . 'listar_comprobantes.php', [])['codigo'] === 403);
comprobar('el formulario ofrece boleta y factura', strpos($grifero->pedir('view/ventas/view_ventas.php')['cuerpo'], '<option value="BOLETA">') !== false);
$r = $admin->pedir(GUARDAR, ['factura_ruta' => 'http://sitio-malicioso.com/api']);
comprobar('una ruta que no es de Nubefact se rechaza', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(GUARDAR, ['factura_ruta' => 'https://api.nubefact.com.atacante.com/api/v1/x']);
comprobar('un dominio parecido también', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(GUARDAR, ['factura_ruta' => 'https://api.nubefact.com/api/v1/03989d1a-6c8c-4b71-b1cd-7d37001deaa0']);
$r = $admin->pedir(GUARDAR, ['factura_ruta' => '', 'factura_serie_boleta' => 'B001']);
comprobar('la ruta puede quedar vacía al guardar lo demás', !empty($r['json']['success']), $r['cuerpo']);
comprobar('una ruta válida se guarda', !empty($r['json']['success']), $r['cuerpo']);
comprobar('serie de factura sin F se rechaza', ($admin->pedir(GUARDAR, ['factura_serie_factura' => 'B001'])['json']['success'] ?? true) === false);
comprobar('serie de boleta sin B se rechaza', ($admin->pedir(GUARDAR, ['factura_serie_boleta' => 'F001'])['json']['success'] ?? true) === false);
comprobar('sin token, la prueba de conexión avisa', ($admin->pedir(CPE . 'probar_conexion.php', [])['json']['success'] ?? true) === false);

$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
comprobar('sin token, la venta se guarda igual', !empty($r['json']['success']), $r['cuerpo']);
$sinToken = (int) ($r['json']['id_venta'] ?? 0);
comprobar('y el comprobante queda pendiente', $estado($sinToken) === 'PENDIENTE', (string) $estado($sinToken));

$admin->pedir(GUARDAR, ['factura_token' => 'token-de-prueba']);
$cfg = $admin->pedir('controller/configuracion/controlador_obtener.php', []);
comprobar('el token nunca se devuelve', strpos($cfg['cuerpo'], 'token-de-prueba') === false && ($cfg['json']['valores']['factura_token_configurado'] ?? false) === true);
comprobar('está cifrado en la base', strpos((string) $pdo->query("SELECT valor FROM configuracion WHERE clave = 'factura_token'")->fetchColumn(), 'token-de-prueba') === false);
$r = $admin->pedir(CPE . 'probar_conexion.php', []);
comprobar('la prueba de conexión llega al servicio', !empty($r['json']['success']) && strpos($r['json']['respuesta'], 'no encontrado') !== false, $r['cuerpo']);
$aud = (string) $pdo->query("SELECT detalle FROM auditoria WHERE accion = 'guardar' ORDER BY id_auditoria DESC LIMIT 1")->fetchColumn();
comprobar('la auditoría no guarda el token', strpos($aud, 'token-de-prueba') === false, $aud);

echo "\n3. Validaciones de la venta\n";
$antes = (int) $pdo->query('SELECT COUNT(*) FROM ventas')->fetchColumn();
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'FACTURA'] + $venta);
comprobar('factura sin cliente rechazada', ($r['json']['success'] ?? true) === false);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'FACTURA', 'id_cliente' => $cliDni] + $venta);
comprobar('factura con DNI rechazada', ($r['json']['success'] ?? true) === false);
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA', 'valor' => 800] + $venta);
comprobar('boleta sobre el tope sin documento rechazada', ($r['json']['success'] ?? true) === false && strpos($r['json']['message'], 'DNI') !== false, $r['cuerpo']);
comprobar('ningún rechazo guardó la venta', (int) $pdo->query('SELECT COUNT(*) FROM ventas')->fetchColumn() === $antes);

echo "\n4. Emisión\n";
$limpiarLog();
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'FACTURA', 'id_cliente' => $cliRuc, 'placa' => 'abc-123'] + $venta);
comprobar('emite la factura', ($r['json']['comprobante']['estado'] ?? '') === 'ACEPTADO', $r['cuerpo']);
comprobar('devuelve serie y número', ($r['json']['comprobante']['serie'] ?? '') === 'F001' && (int) ($r['json']['comprobante']['numero'] ?? 0) === 1, $r['cuerpo']);
$env = $peticiones()[0] ?? [];
comprobar('autentica como pide Nubefact', ($env['auth'] ?? '') === 'Token token="token-de-prueba"', $env['auth'] ?? '');
$doc = $env['datos'] ?? [];
comprobar('operación y tipo correctos', ($doc['operacion'] ?? '') === 'generar_comprobante' && ($doc['tipo_de_comprobante'] ?? '') === '1');
comprobar('datos del cliente', ($doc['cliente_tipo_de_documento'] ?? '') === '6' && ($doc['cliente_numero_de_documento'] ?? '') === '20100000009' && ($doc['cliente_denominacion'] ?? '') === 'EMPRESA PRUEBA SAC');
comprobar('fecha en el formato del ejemplo oficial', ($doc['fecha_de_emision'] ?? '') === date('d-m-Y'), $doc['fecha_de_emision'] ?? '');
comprobar('totales: 150 con IGV son 127.12 + 22.88', ($doc['total_gravada'] ?? '') === '127.12' && ($doc['total_igv'] ?? '') === '22.88' && ($doc['total'] ?? '') === '150.00', json_encode([$doc['total_gravada'] ?? '', $doc['total_igv'] ?? '', $doc['total'] ?? '']));
$item = $doc['items'][0] ?? [];
comprobar('el ítem cuadra', abs((float) $item['cantidad'] * (float) $item['valor_unitario'] - 127.12) < 0.02 && abs((float) $item['cantidad'] * (float) $item['precio_unitario'] - 150) < 0.02 && $item['subtotal'] === '127.12' && $item['total'] === '150.00', json_encode($item));
comprobar('incluye la placa', ($doc['placa_vehiculo'] ?? '') === 'ABC-123');

$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'FACTURA', 'id_cliente' => $cliRuc] + $venta);
comprobar('el número siguiente es consecutivo', (int) ($r['json']['comprobante']['numero'] ?? 0) === 2, $r['cuerpo']);

$limpiarLog();
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA', 'id_cliente' => $cliDni] + $venta);
$doc = $peticiones()[0]['datos'] ?? [];
comprobar('boleta con DNI', ($doc['tipo_de_comprobante'] ?? '') === '2' && ($doc['cliente_tipo_de_documento'] ?? '') === '1' && ($doc['cliente_numero_de_documento'] ?? '') === '12345678' && strpos((string) $doc['serie'], 'B') === 0, json_encode($doc));
$limpiarLog();
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
$doc = $peticiones()[0]['datos'] ?? [];
comprobar('boleta sin cliente', ($doc['cliente_tipo_de_documento'] ?? '') === '-' && ($doc['cliente_denominacion'] ?? '') === 'VARIOS', json_encode($doc));
$limpiarLog();
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA', 'modo' => 'GALONES', 'valor' => 3.333] + $venta);
$doc = $peticiones()[0]['datos'] ?? [];
comprobar('venta por galones con decimales cuadra', abs((float) $doc['items'][0]['cantidad'] * (float) $doc['items'][0]['precio_unitario'] - (float) $doc['total']) < 0.02, json_encode($doc['items'][0] ?? null));
$ventas = $grifero->pedir(VEN . 'listar_ventas.php', [])['json']['data'] ?? [];
comprobar('el listado de ventas muestra el comprobante', !empty($ventas[0]['comprobante_estado']) && !empty($ventas[0]['comprobante_enlace']), json_encode($ventas[0] ?? null));

echo "\n5. Caídas: la venta nunca se pierde\n";
$modo('caida');
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
comprobar('con Nubefact caído, la venta se registra', !empty($r['json']['success']), $r['cuerpo']);
$idCaida = (int) $r['json']['id_venta'];
comprobar('el comprobante queda pendiente', $estado($idCaida) === 'PENDIENTE');
comprobar('el mensaje no expone detalles internos', strpos($r['cuerpo'], '503') === false, $r['cuerpo']);
$numCaida = (int) $pdo->query("SELECT numero FROM comprobantes WHERE id_venta = $idCaida")->fetchColumn();
$avisos = array_column($admin->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'], null, 'clave');
comprobar('el Dashboard avisa', isset($avisos['comprobantes_problema']), json_encode(array_keys($avisos)));
$modo('ok');
$r = $admin->pedir(CPE . 'procesar_cola.php', []);
comprobar('al volver el servicio se envía', !empty($r['json']['success']) && $estado($idCaida) === 'ACEPTADO', $r['cuerpo']);
comprobar('conserva su número', (int) $pdo->query("SELECT numero FROM comprobantes WHERE id_venta = $idCaida")->fetchColumn() === $numCaida);
comprobar('también envía el que esperaba el token', $estado($sinToken) === 'ACEPTADO', (string) $estado($sinToken));

echo "\n6. Respuesta perdida: sin duplicar\n";
$modo('caida_tras_registrar');
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
$idPerdida = (int) $r['json']['id_venta'];
comprobar('sin respuesta queda pendiente', $estado($idPerdida) === 'PENDIENTE');
$modo('ok');
$limpiarLog();
$idCpe = (int) $pdo->query("SELECT id_comprobante FROM comprobantes WHERE id_venta = $idPerdida")->fetchColumn();
$r = $admin->pedir(CPE . 'reintentar.php', ['id_comprobante' => $idCpe]);
$ops = array_map(fn($p) => $p['datos']['operacion'], $peticiones());
comprobar('consulta antes de reenviar y no reenvía', $ops === ['consultar_comprobante'], json_encode($ops));
comprobar('recupera el estado aceptado', $estado($idPerdida) === 'ACEPTADO', (string) $estado($idPerdida));
comprobar('recupera los enlaces', strpos((string) $pdo->query("SELECT enlace FROM comprobantes WHERE id_venta = $idPerdida")->fetchColumn(), 'nubefact.test') !== false);

echo "\n7. SUNAT sin confirmar y rechazos\n";
$modo('sunat_pendiente');
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
$idEnv = (int) $r['json']['id_venta'];
comprobar('sin confirmación queda por confirmar', $estado($idEnv) === 'ENVIADO', (string) $estado($idEnv));
$modo('ok');
$admin->pedir(CPE . 'procesar_cola.php', []);
comprobar('la cola lo actualiza', $estado($idEnv) === 'ACEPTADO', (string) $estado($idEnv));

$modo('rechazo');
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
$idRec = (int) $r['json']['id_venta'];
comprobar('un rechazo no borra la venta', !empty($r['json']['success']) && $estado($idRec) === 'RECHAZADO', $r['cuerpo']);
comprobar('guarda el motivo', strpos((string) $pdo->query("SELECT ultimo_error FROM comprobantes WHERE id_venta = $idRec")->fetchColumn(), 'no coincide') !== false);
$modo('ok');
$limpiarLog();
$admin->pedir(CPE . 'procesar_cola.php', []);
comprobar('la cola no reenvía rechazados por su cuenta', $estado($idRec) === 'RECHAZADO' && count(array_filter($peticiones(), fn($p) => $p['datos']['operacion'] === 'generar_comprobante')) === 0);
$idCpe = (int) $pdo->query("SELECT id_comprobante FROM comprobantes WHERE id_venta = $idRec")->fetchColumn();
$admin->pedir(CPE . 'reintentar.php', ['id_comprobante' => $idCpe]);
comprobar('el reintento manual sí', $estado($idRec) === 'ACEPTADO', (string) $estado($idRec));
comprobar('sin números repetidos', (int) $pdo->query('SELECT COUNT(*) - COUNT(DISTINCT serie, numero) FROM comprobantes')->fetchColumn() === 0);

echo "\n8. Anulación\n";
$idCpe = (int) $pdo->query("SELECT id_comprobante FROM comprobantes WHERE id_venta = $idEnv")->fetchColumn();
$r = $admin->pedir(VEN . 'anular_venta.php', ['id_venta' => $idEnv, 'motivo' => 'x']);
comprobar('no se anula la venta con comprobante vigente', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
comprobar('sin motivo se rechaza', ($admin->pedir(CPE . 'anular_comprobante.php', ['id_comprobante' => $idCpe, 'motivo' => ''])['json']['success'] ?? true) === false);
$limpiarLog();
$modo('sunat_pendiente');
$r = $admin->pedir(CPE . 'anular_comprobante.php', ['id_comprobante' => $idCpe, 'motivo' => 'ERROR DEL SISTEMA']);
$p = $peticiones()[0]['datos'] ?? [];
comprobar('envía la anulación con el formato oficial', ($p['operacion'] ?? '') === 'generar_anulacion' && ($p['motivo'] ?? '') === 'ERROR DEL SISTEMA' && ($p['tipo_de_comprobante'] ?? '') === '2', json_encode($p));
comprobar('queda por confirmar', $estado($idEnv) === 'ANULACION_PENDIENTE', (string) $estado($idEnv));
comprobar('aún no se anula la venta', ($admin->pedir(VEN . 'anular_venta.php', ['id_venta' => $idEnv, 'motivo' => 'x'])['json']['success'] ?? true) === false);
$modo('ok');
$admin->pedir(CPE . 'procesar_cola.php', []);
comprobar('al confirmarse queda anulado', $estado($idEnv) === 'ANULADO', (string) $estado($idEnv));
$r = $admin->pedir(VEN . 'anular_venta.php', ['id_venta' => $idEnv, 'motivo' => 'x']);
comprobar('ahora sí se anula la venta', !empty($r['json']['success']), $r['cuerpo']);
comprobar('queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'COMPROBANTE_ANULADO'")->fetchColumn() >= 1);

$modo('rechazo');
$r = $grifero->pedir(VEN . 'registrar_venta.php', ['tipo_comprobante' => 'BOLETA'] + $venta);
$idCancel = (int) $r['json']['id_venta'];
$modo('ok');
$admin->pedir(VEN . 'anular_venta.php', ['id_venta' => $idCancel, 'motivo' => 'error']);
comprobar('anular una venta rechazada cancela su comprobante', $estado($idCancel) === 'CANCELADO', (string) $estado($idCancel));
$limpiarLog();
$admin->pedir(CPE . 'procesar_cola.php', []);
comprobar('un cancelado nunca se envía', count(array_filter($peticiones(), fn($p) => $p['datos']['operacion'] === 'generar_comprobante')) === 0);

echo "\n9. Permisos y turno\n";
comprobar('un grifero no anula comprobantes', $grifero->pedir(CPE . 'anular_comprobante.php', ['id_comprobante' => $idCpe, 'motivo' => 'x'])['codigo'] === 403);
comprobar('el administrador ve la bandeja', count($admin->pedir(CPE . 'listar_comprobantes.php', [])['json']['data'] ?? []) > 5);
comprobar('filtra por estado', count($admin->pedir(CPE . 'listar_comprobantes.php', ['estado' => 'ANULADO'])['json']['data']) === 1);
$modo('caida');
$sur = $pdo->query("SELECT s.id_surtidor, s.lectura_actual, p.precio_actual FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE s.estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
$lect = array_map(fn($s) => ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => (float) $s['lectura_actual'] + 1, 'precio' => $s['precio_actual']], $sur);
$r = $grifero->pedir('controller/turnos/controlador_registrar_turno_completo.php', ['numero_documento' => 'FAC-' . random_int(1000, 9999), 'id_usuario' => 0, 'turno' => 'DIA', 'fecha' => date('Y-m-d'), 'hora_inicio' => '06:00', 'hora_fin' => '14:00', 'lecturas' => json_encode($lect), 'pagos' => '[]', 'creditos' => '[]', 'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => 0]);
comprobar('con Nubefact caído, el turno se cierra', !empty($r['json']['success']), $r['cuerpo']);
if (!empty($r['json']['id_reporte'])) { $admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $r['json']['id_reporte'], 'motivo' => 'prueba']); }
$modo('ok');

echo "\n10. Limpieza\n";
foreach (['comprobantes', 'series_comprobante', 'ventas'] as $t) { $pdo->exec("DELETE FROM $t"); }
$pdo->exec("DELETE FROM clientes WHERE id_cliente IN ($cliRuc, $cliDni)");
$pdo->exec("UPDATE configuracion SET valor = NULL WHERE clave IN ('factura_token', 'factura_ruta')");
$admin->pedir(MODULO, ['codigo' => 'facturacion', 'activo' => '0']);
$admin->pedir(MODULO, ['codigo' => 'ventas', 'activo' => '0']);
comprobar('módulos apagados', (int) $pdo->query("SELECT COUNT(*) FROM modulos WHERE codigo IN ('facturacion', 'ventas') AND activo = 1")->fetchColumn() === 0);

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
