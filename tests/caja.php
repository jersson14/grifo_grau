<?php
/**
 * Prueba de caja, precios, integridad del turno y avisos contra el sistema en ejecución.
 * Requiere los datos de demostración. Limpia lo que crea y deja los módulos apagados.
 *
 *   php tests/caja.php
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
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : " -> $detalle") . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'caj'); }
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

const CAJA = 'controller/caja/controlador_';
const PROD = 'controller/productos/controlador_';
const MODULO = 'controller/configuracion/controlador_cambiar_modulo.php';
const TURNO = 'controller/turnos/controlador_registrar_turno_completo.php';

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$hoy = date('Y-m-d');

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');

function lecturas(PDO $pdo, float $galones = 10): array
{
    $filas = $pdo->query("SELECT s.id_surtidor, s.lectura_actual, p.precio_actual FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE s.estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
    return array_map(fn($s) => ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => (float) $s['lectura_actual'] + $galones, 'precio' => $s['precio_actual']], $filas);
}
function datos_turno(array $lecturas, float $efectivo, array $pagos = []): array
{
    return ['numero_documento' => 'CAJ-' . random_int(10000, 99999), 'id_usuario' => 0, 'turno' => 'DIA', 'fecha' => date('Y-m-d'), 'hora_inicio' => '06:00', 'hora_fin' => '14:00',
            'lecturas' => json_encode($lecturas), 'pagos' => json_encode($pagos), 'creditos' => '[]', 'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => $efectivo];
}

echo "\n1. Integridad del registro de turno\n";
$l = lecturas($pdo);
$original = $l[0]['lectura_inicial'];
$alterada = $l; $alterada[0]['precio'] = 0.01;
$r = $grifero->pedir(TURNO, datos_turno($alterada, 0));
comprobar('rechaza un precio alterado en el navegador', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$alterada = $l; $alterada[0]['lectura_inicial'] = (float) $original + 5;
$r = $grifero->pedir(TURNO, datos_turno($alterada, 0));
comprobar('rechaza una lectura inicial alterada', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$alterada = $l; $alterada[0]['lectura_final'] = (float) $original - 5;
$r = $grifero->pedir(TURNO, datos_turno($alterada, 0));
comprobar('rechaza una lectura final menor que la inicial', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$alterada = $l; $alterada[] = $l[0];
$r = $grifero->pedir(TURNO, datos_turno($alterada, 0));
comprobar('rechaza un surtidor duplicado', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
comprobar('ningún intento dejó rastro', (float) $pdo->query("SELECT lectura_actual FROM surtidores WHERE id_surtidor = {$l[0]['id_surtidor']}")->fetchColumn() === (float) $original);

echo "\n2. Módulo de caja\n";
$admin->pedir(MODULO, ['codigo' => 'caja', 'activo' => '0']);
comprobar('apagado no responde', $admin->pedir(CAJA . 'listar_turnos.php', [])['codigo'] === 403);
comprobar('apagado no aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'caja/view_caja') === false);
$admin->pedir(MODULO, ['codigo' => 'caja', 'activo' => '1']);
comprobar('encendido aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'caja/view_caja') !== false);
comprobar('la vista abre', $admin->pedir('view/caja/view_caja.php')['codigo'] === 200);
comprobar('el grifero no entra', $grifero->pedir('view/caja/view_caja.php')['codigo'] === 403 && $grifero->pedir(CAJA . 'listar_turnos.php', [])['codigo'] === 403);

// Ventas de 12 surtidores x 10 gal: 4 x 15 + 4 x 13 + 4 x 16 = 1760 gal-soles x 10
$lect = lecturas($pdo);
$ventas = array_sum(array_map(fn($x) => 10 * (float) $x['precio'], $lect));
$r = $grifero->pedir(TURNO, datos_turno($lect, $ventas - 200 - 10, [['id_tipo_pago' => 1, 'codigo_operacion' => 'Y1', 'monto' => 200, 'observaciones' => '']]));
comprobar('turno con S/ 10 de faltante declarado', !empty($r['json']['success']), $r['cuerpo']);
$turno = (int) ($r['json']['id_reporte'] ?? 0);

$fila = array_values(array_filter($admin->pedir(CAJA . 'listar_turnos.php', [])['json']['data'], fn($t) => (int) $t['id_reporte'] === $turno))[0] ?? null;
comprobar('calcula el efectivo esperado', $fila && abs((float) $fila['efectivo_esperado'] - ($ventas - 200)) < 0.01, json_encode($fila));
comprobar('muestra lo declarado', $fila && abs((float) $fila['efectivo_declarado'] - ($ventas - 210)) < 0.01);

echo "\n3. Arqueo\n";
$esperado = $ventas - 200;
$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => $turno, 'cantidades' => json_encode(['100' => -1])]);
comprobar('cantidad negativa rechazada', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => $turno, 'cantidades' => json_encode(['100' => 1.5])]);
comprobar('cantidad decimal rechazada', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => 999999, 'cantidades' => '{}']);
comprobar('turno inexistente rechazado', ($r['json']['success'] ?? true) === false);

// Todo en billetes de 100 salvo el resto, con S/ 25.30 menos de lo esperado.
$objetivo = $esperado - 25.30;
$centimos = (int) round($objetivo * 100);
$cantidades = [];
foreach (['200' => 20000, '100' => 10000, '50' => 5000, '20' => 2000, '10' => 1000, '5' => 500, '2' => 200, '1' => 100, '0.5' => 50, '0.2' => 20, '0.1' => 10] as $d => $v) {
    $cantidades[$d] = intdiv($centimos, $v);
    $centimos %= $v;
}
$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => $turno, 'cantidades' => json_encode($cantidades), 'observaciones' => 'conteo']);
comprobar('registra el arqueo y suma las denominaciones sin error de redondeo', !empty($r['json']['success']) && abs((float) $r['json']['contado'] - $objetivo) < 0.001, $r['cuerpo']);
comprobar('calcula el faltante', abs((float) ($r['json']['diferencia'] ?? 0) + 25.30) < 0.001, (string) ($r['json']['diferencia'] ?? ''));
comprobar('detecta que está fuera de tolerancia', ($r['json']['dentro_tolerancia'] ?? true) === false);

$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => $turno, 'cantidades' => json_encode($cantidades)]);
comprobar('se puede corregir sin duplicar', !empty($r['json']['success']) && (int) $pdo->query("SELECT COUNT(*) FROM arqueos WHERE id_reporte = $turno")->fetchColumn() === 1);

$resumen = $admin->pedir(CAJA . 'resumen.php', [])['json'];
$g = array_values(array_filter($resumen['griferos'], fn($x) => strpos($x['grifero'], 'UNO') !== false))[0] ?? null;
comprobar('el faltante aparece en el grifero correcto', $g && abs((float) $g['faltantes'] + 25.30) < 0.001 && abs((float) $g['pendiente'] + 25.30) < 0.001, json_encode($g));

$idArqueo = (int) $pdo->query("SELECT id_arqueo FROM arqueos WHERE id_reporte = $turno")->fetchColumn();
$r = $admin->pedir(CAJA . 'resolver_diferencia.php', ['id_arqueo' => $idArqueo, 'resolucion' => 'DESCONTADO', 'nota' => '']);
comprobar('resolver sin nota rechazado', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(CAJA . 'resolver_diferencia.php', ['id_arqueo' => $idArqueo, 'resolucion' => 'OTRA', 'nota' => 'x']);
comprobar('resolución inválida rechazada', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(CAJA . 'resolver_diferencia.php', ['id_arqueo' => $idArqueo, 'resolucion' => 'DESCONTADO', 'nota' => 'Acordado con el grifero']);
comprobar('resuelve la diferencia', !empty($r['json']['success']), $r['cuerpo']);
$r = $admin->pedir(CAJA . 'registrar_arqueo.php', ['id_reporte' => $turno, 'cantidades' => json_encode($cantidades)]);
comprobar('un arqueo resuelto ya no se modifica', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
comprobar('queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'ARQUEO_RESUELTO' AND detalle LIKE 'arqueo $idArqueo:%'")->fetchColumn() === 1);

$medios = array_column($admin->pedir(CAJA . 'comparar_medios.php', ['id_reporte' => $turno])['json']['data'], null, 'medio');
comprobar('compara medios de pago', (float) $medios['YAPE']['declarado'] === 200.0 && abs((float) $medios['EFECTIVO']['declarado'] - ($ventas - 210)) < 0.01, json_encode($medios));

echo "\n4. Depósitos\n";
$dep = ['fecha' => $hoy, 'banco' => 'BCP', 'numero_operacion' => 'OP-1', 'monto' => 1000];
comprobar('monto inválido rechazado', ($admin->pedir(CAJA . 'registrar_deposito.php', ['monto' => -1] + $dep)['json']['success'] ?? true) === false);
comprobar('fecha futura rechazada', ($admin->pedir(CAJA . 'registrar_deposito.php', ['fecha' => '2099-01-01'] + $dep)['json']['success'] ?? true) === false);
comprobar('registra el depósito', !empty($admin->pedir(CAJA . 'registrar_deposito.php', $dep)['json']['success']));
comprobar('operación duplicada rechazada', ($admin->pedir(CAJA . 'registrar_deposito.php', $dep)['json']['success'] ?? true) === false);
$resumen = $admin->pedir(CAJA . 'resumen.php', [])['json'];
comprobar('el saldo en caja descuenta el depósito', abs($resumen['saldo_caja'] - ($objetivo - 1000)) < 0.01, json_encode($resumen['saldo_caja']));
$idDep = (int) $pdo->query("SELECT MAX(id_deposito) FROM depositos")->fetchColumn();
comprobar('anular sin motivo rechazado', ($admin->pedir(CAJA . 'anular_deposito.php', ['id_deposito' => $idDep, 'motivo' => ''])['json']['success'] ?? true) === false);
comprobar('anula con motivo', !empty($admin->pedir(CAJA . 'anular_deposito.php', ['id_deposito' => $idDep, 'motivo' => 'error'])['json']['success']));
$resumen = $admin->pedir(CAJA . 'resumen.php', [])['json'];
comprobar('el depósito anulado ya no cuenta', abs($resumen['saldo_caja'] - $objetivo) < 0.01);

echo "\n5. Historial y programación de precios\n";
$prod = $pdo->query("SELECT * FROM productos WHERE tipo = 'REGULAR'")->fetch(PDO::FETCH_ASSOC);
$idp = (int) $prod['id_producto']; $precioOriginal = (float) $prod['precio_actual'];
$admin->pedir(PROD . 'modificar_producto.php', ['id' => $idp, 'nombre' => $prod['nombre'], 'tipo' => $prod['tipo'], 'precio' => $precioOriginal + 1]);
$h = $pdo->query("SELECT * FROM precios_historial WHERE id_producto = $idp ORDER BY id_historial DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
comprobar('un cambio de precio queda en el historial', $h && (float) $h['precio_anterior'] === $precioOriginal && (float) $h['precio_nuevo'] === $precioOriginal + 1, json_encode($h));
$antes = (int) $pdo->query("SELECT COUNT(*) FROM precios_historial")->fetchColumn();
$admin->pedir(PROD . 'modificar_producto.php', ['id' => $idp, 'nombre' => $prod['nombre'], 'tipo' => $prod['tipo'], 'precio' => $precioOriginal + 1]);
comprobar('editar sin cambiar el precio no ensucia el historial', (int) $pdo->query("SELECT COUNT(*) FROM precios_historial")->fetchColumn() === $antes);
$hist = $admin->pedir(PROD . 'historial_precios.php', [])['json']['data'] ?? [];
comprobar('la pantalla de historial lo recibe', $hist && (float) $hist[0]['precio_anterior'] === $precioOriginal, json_encode($hist[0] ?? null));

$r = $admin->pedir(PROD . 'programar_precio.php', ['id_producto' => $idp, 'precio' => 20, 'vigente_desde' => date('Y-m-d\TH:i', time() - 3600)]);
comprobar('una fecha pasada se rechaza', ($r['json']['success'] ?? true) === false);
comprobar('un grifero no programa precios', $grifero->pedir(PROD . 'programar_precio.php', [])['codigo'] === 403);
$r = $admin->pedir(PROD . 'programar_precio.php', ['id_producto' => $idp, 'precio' => 20, 'vigente_desde' => date('Y-m-d\TH:i', time() + 86400)]);
comprobar('programa un cambio futuro', !empty($r['json']['success']), $r['cuerpo']);
$grifero->pedir('controller/surtidores/controlador_surtidores_activos.php', []);
comprobar('antes de su fecha no se aplica', (float) $pdo->query("SELECT precio_actual FROM productos WHERE id_producto = $idp")->fetchColumn() === $precioOriginal + 1);
$pdo->exec("UPDATE precios_programados SET vigente_desde = DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE estado = 'PENDIENTE'");
$grifero->pedir('controller/surtidores/controlador_surtidores_activos.php', []);
comprobar('al llegar su fecha se aplica sola al abrir el turno', (float) $pdo->query("SELECT precio_actual FROM productos WHERE id_producto = $idp")->fetchColumn() === 20.0);
comprobar('queda como aplicado, una sola vez', (int) $pdo->query("SELECT COUNT(*) FROM precios_programados WHERE estado = 'APLICADO'")->fetchColumn() === 1);
$h = $pdo->query("SELECT origen FROM precios_historial ORDER BY id_historial DESC LIMIT 1")->fetchColumn();
comprobar('el historial indica que fue programado', $h === 'PROGRAMADO', (string) $h);
$r = $admin->pedir(PROD . 'programar_precio.php', ['id_producto' => $idp, 'precio' => 30, 'vigente_desde' => date('Y-m-d\TH:i', time() + 86400)]);
$idProg = (int) $pdo->query("SELECT MAX(id_programado) FROM precios_programados")->fetchColumn();
comprobar('se puede cancelar', !empty($admin->pedir(PROD . 'cancelar_programado.php', ['id_programado' => $idProg])['json']['success']));
comprobar('no se cancela dos veces', ($admin->pedir(PROD . 'cancelar_programado.php', ['id_programado' => $idProg])['json']['success'] ?? true) === false);

echo "\n6. Avisos de la página principal\n";
$alertasIniciales = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_alertas'")->fetchColumn();
$pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc) VALUES ('CLIENTE MOROSO', '11111111')");
$cli = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO ventas_credito (id_cliente, numero_vale, monto, saldo_pendiente, fecha_vencimiento) VALUES ($cli, 'V-MORA', 50, 50, DATE_SUB(CURDATE(), INTERVAL 5 DAY))");
$avisos = array_column($admin->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'], null, 'clave');
comprobar('avisa de créditos vencidos', ($avisos['creditos_vencidos']['cantidad'] ?? 0) === 1, json_encode($avisos));
comprobar('el aviso lleva a su pantalla', ($avisos['creditos_vencidos']['vista'] ?? '') === 'creditos/view_creditos_pendientes.php');
comprobar('un grifero no recibe avisos de administración', ($grifero->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'] ?? [1]) === []);
$admin->pedir('controller/configuracion/controlador_guardar.php', ['inicio_alertas' => 'turnos_sin_validar,inventada']);
$avisos = array_column($admin->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'], null, 'clave');
comprobar('respeta lo elegido en Configuración', !isset($avisos['creditos_vencidos']), json_encode($avisos));
comprobar('ignora una opción inventada', $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_alertas'")->fetchColumn() === 'turnos_sin_validar');
$admin->pedir('controller/configuracion/controlador_guardar.php', ['inicio_alertas' => $alertasIniciales]);

echo "\n7. Limpieza\n";
$r = $admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $turno, 'motivo' => 'prueba']);
comprobar('eliminar el turno borra su arqueo', strpos($r['cuerpo'], '"success":true') !== false && (int) $pdo->query("SELECT COUNT(*) FROM arqueos WHERE id_reporte = $turno")->fetchColumn() === 0, $r['cuerpo']);
$pdo->exec("DELETE FROM ventas_credito WHERE id_cliente = $cli");
$pdo->exec("DELETE FROM clientes WHERE id_cliente = $cli");
$pdo->exec("UPDATE productos SET precio_actual = $precioOriginal WHERE id_producto = $idp");
foreach (['precios_historial', 'precios_programados', 'depositos', 'arqueos'] as $t) { $pdo->exec("DELETE FROM $t"); }
$admin->pedir(MODULO, ['codigo' => 'caja', 'activo' => '0']);

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
