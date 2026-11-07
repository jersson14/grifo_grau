<?php
/**
 * Prueba del módulo de personal contra el sistema en ejecución.
 * Requiere los datos de demostración. Deja el módulo y los datos como estaban.
 *
 *   php tests/personal.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'per'); }
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
$moduloAntes = (int) $pdo->query("SELECT activo FROM modulos WHERE codigo = 'personal'")->fetchColumn();
$maxPersonal = (int) $pdo->query('SELECT COALESCE(MAX(id_personal), 0) FROM personal')->fetchColumn();
$maxReporte = (int) $pdo->query('SELECT COALESCE(MAX(id_reporte), 0) FROM reportes_turno')->fetchColumn();
$modulo = fn(int $a) => $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'personal'")->execute([$a]);
$idGrifero = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();
// Si una ficha existente ya está vinculada al grifero, se desvincula durante la prueba y se restaura al final.
$fichaPrevia = (int) $pdo->query("SELECT COALESCE(MAX(id_personal), 0) FROM personal WHERE id_usuario = $idGrifero")->fetchColumn();
if ($fichaPrevia) { $pdo->exec("UPDATE personal SET id_usuario = NULL WHERE id_personal = $fichaPrevia"); }

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$R = 'controller/personal/controlador_';
$hoy = date('Y-m-d');
$mes = date('Y-m');
$lunes = date('Y-m-d', strtotime('monday this week'));
$exito = fn(array $r) => ($r['success'] ?? false) === true;
$falla = fn(array $r) => ($r['success'] ?? true) === false;
$de = fn(array $filas, int $id) => array_values(array_filter($filas, fn($p) => (int) $p['id_personal'] === $id))[0] ?? null;

try {
    echo "1. Acceso\n";
    $modulo(0);
    comprobar('con el módulo apagado no responde', $admin->pedir($R . 'datos.php', [])['codigo'] === 403);
    $modulo(1);
    comprobar('el grifero no entra', $grifero->pedir($R . 'datos.php', [])['codigo'] === 403);
    comprobar('el grifero no ve la pantalla', $grifero->pedir('view/personal/view_personal.php')['codigo'] === 403);
    $d = $admin->json($R . 'datos.php');
    comprobar('el administrador recibe cargos, turnos, estados y tipos', $exito($d) && in_array('Grifero', $d['cargos'], true) && count($d['turnos']) === 3 && count($d['estados']) === 4 && count($d['tipos']) === 3, json_encode($d));
    comprobar('ofrece usuarios activos para vincular', in_array($idGrifero, array_map('intval', array_column($d['usuarios'] ?? [], 'id_usuario')), true));

    echo "\n2. Fichas\n";
    comprobar('exige nombres', $falla($admin->json($R . 'guardar.php', ['nombres' => '', 'cargo' => 'Grifero'])));
    comprobar('valida el DNI', $falla($admin->json($R . 'guardar.php', ['nombres' => 'Prueba', 'dni' => '1234', 'cargo' => 'Grifero'])));
    comprobar('valida el cargo', $falla($admin->json($R . 'guardar.php', ['nombres' => 'Prueba', 'cargo' => 'Gerente general'])));
    comprobar('no admite sueldo negativo', $falla($admin->json($R . 'guardar.php', ['nombres' => 'Prueba', 'cargo' => 'Grifero', 'sueldo' => '-5'])));
    $r = $admin->json($R . 'guardar.php', ['nombres' => 'Juan Prueba Quispe', 'dni' => '40000001', 'cargo' => 'Grifero', 'sueldo' => '1200', 'id_usuario' => $idGrifero, 'activo' => '1']);
    $id1 = (int) ($r['id_personal'] ?? 0);
    comprobar('crea una ficha vinculada al grifero', $id1 > 0, json_encode($r));
    comprobar('no repite el DNI', $falla($admin->json($R . 'guardar.php', ['nombres' => 'Otro', 'dni' => '40000001', 'cargo' => 'Cajero'])));
    comprobar('no vincula dos fichas al mismo usuario', $falla($admin->json($R . 'guardar.php', ['nombres' => 'Otro', 'cargo' => 'Cajero', 'id_usuario' => $idGrifero])));
    $id2 = (int) ($admin->json($R . 'guardar.php', ['nombres' => 'Rosa Prueba Mamani', 'cargo' => 'Limpieza', 'activo' => '1'])['id_personal'] ?? 0);
    comprobar('crea una ficha sin DNI ni usuario', $id2 > 0);
    $libres = array_map('intval', array_column($admin->json($R . 'datos.php')['usuarios'], 'id_usuario'));
    comprobar('el usuario vinculado ya no se ofrece a otras fichas', !in_array($idGrifero, $libres, true));
    $libres = array_map('intval', array_column($admin->json($R . 'datos.php', ['id_personal' => $id1])['usuarios'], 'id_usuario'));
    comprobar('pero sí al editar su propia ficha', in_array($idGrifero, $libres, true));
    comprobar('edita la ficha', $exito($admin->json($R . 'guardar.php', ['id_personal' => $id1, 'nombres' => 'Juan Prueba Quispe', 'dni' => '40000001', 'cargo' => 'Grifero', 'sueldo' => '1300', 'id_usuario' => $idGrifero, 'activo' => '1'])));
    comprobar('guarda el sueldo nuevo', (float) $pdo->query("SELECT sueldo FROM personal WHERE id_personal = $id1")->fetchColumn() === 1300.0);

    echo "\n3. Horario semanal\n";
    comprobar('rechaza un turno no válido', $falla($admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => $lunes, 'turno' => 'TARDE'])));
    comprobar('asigna el turno de día', $exito($admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => $lunes, 'turno' => 'DIA'])));
    comprobar('cambia el turno sin duplicar', $exito($admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => $lunes, 'turno' => 'NOCHE']))
        && (int) $pdo->query("SELECT COUNT(*) FROM personal_horario WHERE id_personal = $id1")->fetchColumn() === 1);
    $admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => date('Y-m-d', strtotime($lunes . ' +1 day')), 'turno' => 'DESCANSO']);
    $h = $admin->json($R . 'horario.php', ['fecha' => date('Y-m-d', strtotime($lunes . ' +3 days'))]);
    $fila = $de($h['personas'] ?? [], $id1);
    comprobar('la semana empieza el lunes y trae siete días', ($h['dias'][0] ?? '') === $lunes && count($h['dias']) === 7);
    comprobar('muestra los turnos asignados', $fila !== null && $fila['turnos'][0] === 'NOCHE' && $fila['turnos'][1] === 'DESCANSO' && $fila['turnos'][2] === '');
    $siguiente = date('Y-m-d', strtotime($lunes . ' +7 days'));
    $admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => date('Y-m-d', strtotime($siguiente . ' +1 day')), 'turno' => 'DIA']);
    $r = $admin->json($R . 'copiar_semana.php', ['lunes' => $siguiente]);
    comprobar('copiar la semana solo llena los días vacíos', ($r['copiados'] ?? -1) === 1
        && $pdo->query("SELECT turno FROM personal_horario WHERE id_personal = $id1 AND fecha = DATE_ADD('$siguiente', INTERVAL 1 DAY)")->fetchColumn() === 'DIA', json_encode($r));
    comprobar('copiar exige un lunes', $falla($admin->json($R . 'copiar_semana.php', ['lunes' => date('Y-m-d', strtotime($lunes . ' +2 days'))])));
    comprobar('un turno vacío quita la asignación', $exito($admin->json($R . 'guardar_horario.php', ['id_personal' => $id1, 'fecha' => $siguiente, 'turno' => '']))
        && (int) $pdo->query("SELECT COUNT(*) FROM personal_horario WHERE id_personal = $id1 AND fecha = '$siguiente'")->fetchColumn() === 0);

    echo "\n4. Asistencia\n";
    $pdo->exec("INSERT INTO personal_horario (id_personal, fecha, turno) VALUES ($id1, CURDATE(), 'DIA') ON DUPLICATE KEY UPDATE turno = 'DIA'");
    $pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado) VALUES ('PER-TEST-1', $idGrifero, 'DIA', CURDATE(), 'CERRADO')");
    $idReporte = (int) $pdo->lastInsertId();
    comprobar('no marca días futuros', $falla($admin->json($R . 'marcar.php', ['id_personal' => $id1, 'fecha' => date('Y-m-d', strtotime('+1 day')), 'estado' => 'PRESENTE'])));
    comprobar('valida la hora', $falla($admin->json($R . 'marcar.php', ['id_personal' => $id1, 'fecha' => $hoy, 'estado' => 'PRESENTE', 'hora_entrada' => '25:00'])));
    comprobar('marca una tardanza con hora', $exito($admin->json($R . 'marcar.php', ['id_personal' => $id1, 'fecha' => $hoy, 'estado' => 'TARDANZA', 'hora_entrada' => '07:20', 'observacion' => 'Llegó tarde'])));
    comprobar('marcar de nuevo actualiza el mismo día', $exito($admin->json($R . 'marcar.php', ['id_personal' => $id1, 'fecha' => $hoy, 'estado' => 'PRESENTE', 'hora_entrada' => '06:55', 'hora_salida' => '19:00']))
        && (int) $pdo->query("SELECT COUNT(*) FROM personal_asistencia WHERE id_personal = $id1")->fetchColumn() === 1);
    comprobar('una falta no guarda horas', $exito($admin->json($R . 'marcar.php', ['id_personal' => $id2, 'fecha' => $hoy, 'estado' => 'FALTA', 'hora_entrada' => '08:00']))
        && $pdo->query("SELECT hora_entrada FROM personal_asistencia WHERE id_personal = $id2")->fetchColumn() === null);
    $fila = $de($admin->json($R . 'asistencia.php', ['fecha' => $hoy])['personas'] ?? [], $id1);
    comprobar('muestra el turno programado y el turno abierto en el sistema', $fila !== null && $fila['programado'] === 'DIA' && (int) $fila['turnos_sistema'] === 1 && $fila['estado'] === 'PRESENTE', json_encode($fila));

    echo "\n5. Adelantos, descuentos y faltantes de caja\n";
    comprobar('el monto debe ser positivo', $falla($admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'ADELANTO', 'monto' => '0', 'motivo' => 'x'])));
    comprobar('exige el motivo', $falla($admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'ADELANTO', 'monto' => '100', 'motivo' => ''])));
    comprobar('rechaza un tipo no válido', $falla($admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'PRESTAMO', 'monto' => '100', 'motivo' => 'x'])));
    $mov1 = (int) ($admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'ADELANTO', 'monto' => '200', 'motivo' => 'Adelanto de quincena'])['id_movimiento'] ?? 0);
    comprobar('registra un adelanto', $mov1 > 0);
    $admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'BONO', 'monto' => '50', 'motivo' => 'Bono por puntualidad']);
    $mov3 = (int) ($admin->json($R . 'registrar_movimiento.php', ['id_personal' => $id1, 'fecha' => $hoy, 'tipo' => 'ADELANTO', 'monto' => '999', 'motivo' => 'Por error'])['id_movimiento'] ?? 0);
    comprobar('anular exige motivo', $falla($admin->json($R . 'anular_movimiento.php', ['id_movimiento' => $mov3, 'motivo' => ''])));
    comprobar('anula con motivo', $exito($admin->json($R . 'anular_movimiento.php', ['id_movimiento' => $mov3, 'motivo' => 'Monto equivocado'])));
    comprobar('no se anula dos veces', $falla($admin->json($R . 'anular_movimiento.php', ['id_movimiento' => $mov3, 'motivo' => 'otra vez'])));
    comprobar('la anulación queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE detalle LIKE '%\"id_movimiento\":\"$mov3\"%'")->fetchColumn() >= 1);

    $pdo->exec("INSERT INTO arqueos (id_reporte, denominaciones, efectivo_contado, efectivo_esperado, diferencia, resolucion, id_usuario) VALUES ($idReporte, '{}', 480, 515.50, -35.50, 'DESCONTADO', 1)");
    $idArqueo = (int) $pdo->lastInsertId();
    $pendientes = fn() => array_map('intval', array_column($admin->json($R . 'movimientos.php', ['mes' => $mes])['faltantes'] ?? [], 'id_arqueo'));
    $m = $admin->json($R . 'movimientos.php', ['mes' => $mes]);
    $f = array_values(array_filter($m['faltantes'] ?? [], fn($x) => (int) $x['id_arqueo'] === $idArqueo))[0] ?? null;
    comprobar('el faltante descontado en caja aparece por descontar con su grifero', $f !== null && (int) $f['id_personal'] === $id1 && abs((float) $f['monto'] - 35.50) < 0.001, json_encode($m['faltantes'] ?? null));
    $r = $admin->json($R . 'descontar_faltante.php', ['id_arqueo' => $idArqueo]);
    comprobar('lo registra como descuento', $exito($r) && $pdo->query("SELECT CONCAT(tipo, ' ', monto) FROM personal_movimientos WHERE id_arqueo = $idArqueo")->fetchColumn() === 'DESCUENTO 35.50', json_encode($r));
    comprobar('no se descuenta dos veces', $falla($admin->json($R . 'descontar_faltante.php', ['id_arqueo' => $idArqueo])));
    comprobar('sale de la lista de pendientes', !in_array($idArqueo, $pendientes(), true));
    $movDesc = (int) $pdo->query("SELECT id_movimiento FROM personal_movimientos WHERE id_arqueo = $idArqueo")->fetchColumn();
    $admin->json($R . 'anular_movimiento.php', ['id_movimiento' => $movDesc, 'motivo' => 'Se condonó']);
    comprobar('al anular el descuento el faltante vuelve a pendientes', in_array($idArqueo, $pendientes(), true));
    $admin->json($R . 'descontar_faltante.php', ['id_arqueo' => $idArqueo]);

    echo "\n6. Resumen del mes\n";
    $res = $admin->json($R . 'resumen.php', ['mes' => $mes]);
    $p1 = $de($res['personas'] ?? [], $id1);
    $p2 = $de($res['personas'] ?? [], $id2);
    comprobar('cuenta asistencia y turnos del sistema', $p1 !== null && (int) $p1['asistidos'] === 1 && (int) $p1['turnos_sistema'] === 1, json_encode($p1));
    comprobar('el neto resta adelantos y descuentos y suma bonos, sin anulados', $p1 !== null && abs($p1['adelantos'] - 200) < 0.001 && abs($p1['descuentos'] - 35.50) < 0.001
        && abs($p1['bonos'] - 50) < 0.001 && abs($p1['neto'] - (1300 - 200 - 35.50 + 50)) < 0.001, json_encode($p1));
    comprobar('cuenta las faltas y sin sueldo no estima neto', $p2 !== null && (int) $p2['faltas'] === 1 && $p2['neto'] === null);

    echo "\n7. Cese\n";
    $admin->json($R . 'guardar.php', ['id_personal' => $id2, 'nombres' => 'Rosa Prueba Mamani', 'cargo' => 'Limpieza', 'activo' => '0']);
    comprobar('una persona cesada sale del horario y del resumen', $de($admin->json($R . 'horario.php', ['fecha' => $hoy])['personas'] ?? [], $id2) === null
        && $de($admin->json($R . 'resumen.php', ['mes' => $mes])['personas'] ?? [], $id2) === null);
} finally {
    $ids = "SELECT id_personal FROM personal WHERE id_personal > $maxPersonal";
    foreach (['personal_movimientos', 'personal_asistencia', 'personal_horario'] as $t) {
        $pdo->exec("DELETE FROM $t WHERE id_personal IN ($ids)");
    }
    $pdo->exec("DELETE FROM personal WHERE id_personal > $maxPersonal");
    $pdo->exec("DELETE FROM arqueos WHERE id_reporte IN (SELECT id_reporte FROM reportes_turno WHERE id_reporte > $maxReporte AND numero_documento LIKE 'PER-TEST-%')");
    $pdo->exec("DELETE FROM reportes_turno WHERE id_reporte > $maxReporte AND numero_documento LIKE 'PER-TEST-%'");
    if ($fichaPrevia) { $pdo->exec("UPDATE personal SET id_usuario = $idGrifero WHERE id_personal = $fichaPrevia"); }
    $modulo($moduloAntes);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
