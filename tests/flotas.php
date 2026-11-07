<?php
/**
 * Prueba del módulo de flotas contra el sistema en ejecución.
 * Requiere los datos de demostración. Deja el módulo y los datos como estaban.
 *
 *   php tests/flotas.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'flo'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $c = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies, CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => $this->csrf !== '' ? ['X-CSRF-Token: ' . $this->csrf] : []]);
        if ($post !== null) { curl_setopt($c, CURLOPT_POST, true); curl_setopt($c, CURLOPT_POSTFIELDS, $post); }
        $cuerpo = (string) curl_exec($c);
        return ['codigo' => curl_getinfo($c, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo];
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
$moduloAntes = (int) $pdo->query("SELECT activo FROM modulos WHERE codigo = 'flotas'")->fetchColumn();
$maxCliente = (int) $pdo->query('SELECT COALESCE(MAX(id_cliente), 0) FROM clientes')->fetchColumn();
$maxCredito = (int) $pdo->query('SELECT COALESCE(MAX(id_credito), 0) FROM ventas_credito')->fetchColumn();
$maxSolicitud = (int) $pdo->query('SELECT COALESCE(MAX(id_solicitud), 0) FROM solicitudes_flota')->fetchColumn();
$flotasAntes = $pdo->query('SELECT id_cliente FROM flotas')->fetchAll(PDO::FETCH_COLUMN);
$modulo = fn(int $a) => $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'flotas'")->execute([$a]);

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$R = 'controller/flotas/controlador_';
$V = 'controller/turnos/controlador_verificar_vale_flota.php';

// Cliente de demostración que se convierte en flota.
$pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc, estado) VALUES ('Transportes Prueba SAC', '20999999991', 'ACTIVO')");
$idCli = (int) $pdo->lastInsertId();

try {
    echo "1. Acceso\n";
    $modulo(0);
    comprobar('con el módulo apagado no responde', $admin->pedir($R . 'listar.php', [])['codigo'] === 403);
    $r = $grifero->json($V, ['id_cliente' => $idCli, 'placa' => 'ABC-123', 'monto' => 10]);
    comprobar('con el módulo apagado el aviso del turno no estorba', ($r['es_flota'] ?? null) === false, json_encode($r));
    $modulo(1);
    comprobar('con el módulo activo aparece en el menú', strpos($admin->pedir('view/index.php')['cuerpo'], 'flotas/view_flotas.php') !== false);
    comprobar('el grifero no administra flotas', $grifero->pedir($R . 'listar.php', [])['codigo'] === 403);

    echo "\n2. Crear flotas\n";
    $mal = fn(array $x) => $admin->json($R . 'guardar.php', $x + ['nueva' => '1', 'id_cliente' => $idCli, 'dias_credito' => '30']);
    comprobar('rechaza un límite negativo', ($mal(['limite_credito' => '-5'])['success'] ?? true) === false);
    comprobar('rechaza más de 180 días', ($mal(['dias_credito' => '200'])['success'] ?? true) === false);
    comprobar('rechaza un correo mal escrito', ($mal(['correo' => 'no-es-correo'])['success'] ?? true) === false);
    $r = $admin->json($R . 'guardar.php', ['nueva' => '1', 'id_cliente' => $idCli, 'limite_credito' => '500', 'dias_credito' => '15', 'contacto' => 'Ana', 'solo_placas_autorizadas' => '1']);
    comprobar('convierte un cliente existente en flota', ($r['id_cliente'] ?? 0) === $idCli, json_encode($r));
    comprobar('no lo convierte dos veces', ($admin->json($R . 'guardar.php', ['nueva' => '1', 'id_cliente' => $idCli, 'dias_credito' => '30'])['success'] ?? true) === false);
    $r = $admin->json($R . 'guardar.php', ['nueva' => '1', 'nombre' => 'Empresa Nueva Prueba', 'ruc' => '20999999992', 'dias_credito' => '30']);
    $idNueva = (int) ($r['id_cliente'] ?? 0);
    comprobar('crea una empresa nueva como cliente y flota', $idNueva > $maxCliente && $idNueva !== $idCli, json_encode($r));
    comprobar('rechaza un RUC inválido', ($admin->json($R . 'guardar.php', ['nueva' => '1', 'nombre' => 'X', 'ruc' => '123', 'dias_credito' => '30'])['success'] ?? true) === false);

    echo "\n3. Placas\n";
    $r = $admin->json($R . 'guardar_placa.php', ['id_cliente' => $idCli, 'placa' => 'abc-123', 'vehiculo' => 'Tracto', 'conductor' => 'Luis', 'activo' => '1']);
    comprobar('agrega una placa y la guarda normalizada', ($r['id_placa'] ?? 0) > 0 && $pdo->query("SELECT placa FROM flota_placas WHERE id_cliente = $idCli")->fetchColumn() === 'ABC123', json_encode($r));
    comprobar('la misma placa escrita distinto no se repite', ($admin->json($R . 'guardar_placa.php', ['id_cliente' => $idCli, 'placa' => 'ABC 123', 'activo' => '1'])['success'] ?? true) === false);
    comprobar('rechaza una placa demasiado corta', ($admin->json($R . 'guardar_placa.php', ['id_cliente' => $idCli, 'placa' => 'A1', 'activo' => '1'])['success'] ?? true) === false);

    echo "\n4. Consumo, deuda y avisos\n";
    $ins = $pdo->prepare("INSERT INTO ventas_credito (id_cliente, numero_vale, placa, monto, saldo_pendiente, estado, fecha_vencimiento, created_at) VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?, NOW())");
    $ins->execute([$idCli, 'V-T1', 'Abc-123', 300, 300, date('Y-m-d', strtotime('+10 days'))]);
    $ins->execute([$idCli, 'V-T2', 'XYZ-999', 250, 250, date('Y-m-d', strtotime('-2 days'))]);
    $l = $admin->json($R . 'listar.php');
    $f = array_values(array_filter($l['flotas'] ?? [], fn($x) => (int) $x['id_cliente'] === $idCli))[0] ?? null;
    comprobar('suma la deuda y el consumo del mes', $f && $f['deuda'] == 550 && $f['consumo_mes'] == 550, json_encode($f));
    comprobar('marca lo vencido', $f && $f['vencido'] == 250);
    comprobar('detecta el vale de una placa no autorizada', $f && (int) $f['vales_no_autorizados'] === 1);
    comprobar('marca que pasó el límite', $f && $f['sobre_limite'] === true && $f['uso_limite'] == 110);
    comprobar('el nuevo cliente no aparece entre los que se pueden convertir', !in_array($idCli, array_map('intval', array_column($l['clientes'] ?? [], 'id_cliente')), true));

    $d = $admin->json($R . 'detalle.php', ['id_cliente' => $idCli, 'mes' => date('Y-m')]);
    $vales = array_column($d['vales'] ?? [], 'autorizada', 'numero_vale');
    comprobar('el estado de cuenta distingue placas autorizadas', ($vales['V-T1'] ?? null) === true && ($vales['V-T2'] ?? null) === false, json_encode($vales));
    $placas = array_column($d['por_placa'] ?? [], 'total', 'placa');
    comprobar('agrupa el consumo por placa normalizada', ($placas['ABC123'] ?? 0) == 300 && ($placas['XYZ999'] ?? 0) == 250, json_encode($placas));
    comprobar('calcula el disponible', ($d['totales']['disponible'] ?? null) == -50 && ($d['totales']['no_autorizados'] ?? 0) === 1);

    $r = $grifero->json($V, ['id_cliente' => $idCli, 'placa' => 'xyz999', 'monto' => 10]);
    comprobar('al grifero le avisa de la placa no autorizada y del límite', ($r['es_flota'] ?? false) && count($r['avisos'] ?? []) === 2, json_encode($r));
    $r = $grifero->json($V, ['id_cliente' => $idNueva, 'placa' => 'ABC-123', 'monto' => 10]);
    comprobar('sin placas registradas ni límite no hay aviso de límite', ($r['es_flota'] ?? false) && count(array_filter($r['avisos'] ?? [], fn($a) => str_contains($a, 'límite'))) === 0, json_encode($r));
    $idDemo = (int) $pdo->query("SELECT id_cliente FROM clientes WHERE id_cliente <= $maxCliente AND id_cliente NOT IN (SELECT id_cliente FROM flotas) LIMIT 1")->fetchColumn();
    comprobar('un cliente común no recibe avisos', ($grifero->json($V, ['id_cliente' => $idDemo, 'placa' => 'ZZZ-111', 'monto' => 9999])['es_flota'] ?? null) === false);

    echo "\n5. Datos y suspensión\n";
    $r = $admin->json($R . 'guardar.php', ['id_cliente' => $idCli, 'limite_credito' => '', 'dias_credito' => '30', 'estado' => 'SUSPENDIDA', 'solo_placas_autorizadas' => '1']);
    $fila = $pdo->query("SELECT limite_credito, estado FROM flotas WHERE id_cliente = $idCli")->fetch(PDO::FETCH_ASSOC);
    comprobar('un límite vacío queda sin límite y se puede suspender', ($r['success'] ?? false) && $fila['limite_credito'] === null && $fila['estado'] === 'SUSPENDIDA', json_encode($fila));
    $r = $grifero->json($V, ['id_cliente' => $idCli, 'placa' => 'ABC-123', 'monto' => 10]);
    comprobar('una flota suspendida avisa al grifero', in_array('La flota está suspendida.', $r['avisos'] ?? [], true), json_encode($r));

    echo "\n6. Desde una solicitud de la página pública\n";
    $pdo->exec("INSERT INTO solicitudes_flota (empresa, ruc, contacto, telefono, correo, unidades) VALUES ('Logística Solicitud SAC', '20999999993', 'Carla', '987000111', 'carla@ejemplo.pe', 4)");
    $idSol = (int) $pdo->lastInsertId();
    $r = $admin->json($R . 'desde_solicitud.php', ['id_solicitud' => $idSol, 'limite_credito' => '2000', 'dias_credito' => '20']);
    $idDeSol = (int) ($r['id_cliente'] ?? 0);
    $fl = $pdo->query("SELECT f.contacto, f.correo, f.limite_credito, f.id_solicitud, c.nombre_completo, c.dni_ruc FROM flotas f JOIN clientes c USING (id_cliente) WHERE f.id_cliente = $idDeSol")->fetch(PDO::FETCH_ASSOC);
    comprobar('crea la flota con los datos de la solicitud', $fl && $fl['nombre_completo'] === 'Logística Solicitud SAC' && $fl['dni_ruc'] === '20999999993' && $fl['contacto'] === 'Carla' && $fl['limite_credito'] === '2000.00' && (int) $fl['id_solicitud'] === $idSol, json_encode($fl));
    comprobar('la solicitud queda atendida', $pdo->query("SELECT estado FROM solicitudes_flota WHERE id_solicitud = $idSol")->fetchColumn() === 'ATENDIDA');
    comprobar('no se convierte dos veces', ($admin->json($R . 'desde_solicitud.php', ['id_solicitud' => $idSol, 'dias_credito' => '20'])['success'] ?? true) === false);
    $datosPub = $admin->json('controller/publico/controlador_datos.php');
    comprobar('la bandeja de solicitudes ofrece convertir en flota', ($datosPub['flotas'] ?? false) === true);

    echo "\n7. Estado de cuenta en PDF\n";
    $r = $admin->pedir('view/MPDF/REPORTE/estado_cuenta_flota.php?id=' . $idCli . '&mes=' . date('Y-m'));
    comprobar('genera el PDF', $r['codigo'] === 200 && str_starts_with($r['cuerpo'], '%PDF-'), substr($r['cuerpo'], 0, 200));
    comprobar('el grifero no descarga estados de cuenta', $grifero->pedir('view/MPDF/REPORTE/estado_cuenta_flota.php?id=' . $idCli)['codigo'] === 403);
    comprobar('una flota inexistente responde 404', $admin->pedir('view/MPDF/REPORTE/estado_cuenta_flota.php?id=999999')['codigo'] === 404);
} finally {
    $pdo->exec("DELETE FROM ventas_credito WHERE id_credito > $maxCredito");
    $nuevas = implode(',', array_map('intval', array_diff($pdo->query('SELECT id_cliente FROM flotas')->fetchAll(PDO::FETCH_COLUMN), $flotasAntes))) ?: '0';
    $pdo->exec("DELETE FROM flota_placas WHERE id_cliente IN ($nuevas)");
    $pdo->exec("DELETE FROM flotas WHERE id_cliente IN ($nuevas)");
    $pdo->exec("DELETE FROM clientes WHERE id_cliente > $maxCliente");
    $pdo->exec("DELETE FROM solicitudes_flota WHERE id_solicitud > $maxSolicitud");
    $modulo($moduloAntes);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
