<?php
/**
 * Prueba de copias de seguridad y del acceso a turnos ajenos. La copia se restaura en una base temporal
 * y se compara tabla por tabla con la original. Requiere los datos de demostración.
 *
 *   php tests/respaldos.php [ruta de mysql.exe]
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$mysql = $argv[1] ?? 'C:/xampp/mysql/bin/mysql.exe';
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'res'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $curl = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_HTTPHEADER => $this->csrf ? ['X-CSRF-Token: ' . $this->csrf] : [], CURLOPT_TIMEOUT => 120]);
        if ($post !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
        $cuerpo = (string) curl_exec($curl);
        return ['codigo' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'json' => json_decode($cuerpo, true), 'tipo' => curl_getinfo($curl, CURLINFO_CONTENT_TYPE)];
    }
    public function entrar(string $u, string $c): void
    {
        $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
    }
}

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();
$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$g1 = new Cliente($base);
$g1->entrar('grifero1', 'grifero123');
$g2 = new Cliente($base);
$g2->entrar('grifero2', 'grifero123');
$idG1 = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();

echo "\n1. Turnos ajenos\n";
$sur = $pdo->query("SELECT s.id_surtidor, s.lectura_actual, p.precio_actual FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE s.estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
$lect = array_map(fn($s) => ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => (float) $s['lectura_actual'] + 1, 'precio' => $s['precio_actual']], $sur);
$r = $g1->pedir('controller/turnos/controlador_registrar_turno_completo.php', ['numero_documento' => 'RES-' . random_int(1000, 9999), 'id_usuario' => 0, 'turno' => 'DIA', 'fecha' => date('Y-m-d'), 'hora_inicio' => '06:00', 'hora_fin' => '14:00', 'lecturas' => json_encode($lect),
    'pagos' => json_encode([['id_tipo_pago' => 4, 'codigo_operacion' => '', 'monto' => 5, 'observaciones' => '']]), 'creditos' => '[]', 'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => 0]);
$turno = (int) ($r['json']['id_reporte'] ?? 0);
comprobar('grifero1 registra un turno', $turno > 0, $r['cuerpo']);
$lectura = (int) $pdo->query("SELECT id_lectura FROM lecturas_turno WHERE id_reporte = $turno LIMIT 1")->fetchColumn();
$pago = (int) $pdo->query("SELECT id_pago_reporte FROM pagos_reporte WHERE id_reporte = $turno LIMIT 1")->fetchColumn();

foreach (['detalle_turno', 'detalle_lecturas', 'listar_pagos_turno', 'listar_creditos_turno', 'obtener_lecturas_turno', 'calcular_totales_turno', 'cuadre_caja'] as $c) {
    comprobar("grifero2 no lee $c de un turno ajeno", $g2->pedir("controller/turnos/controlador_$c.php", ['id_reporte' => $turno])['codigo'] === 403);
}
comprobar('grifero2 no abre el PDF ajeno', $g2->pedir("view/MPDF/REPORTE/reporte_turno.php?id=$turno")['codigo'] === 403);
comprobar('grifero2 no cambia una lectura ajena', $g2->pedir('controller/turnos/controlador_actualizar_lectura_turno.php', ['id_lectura' => $lectura, 'lectura_actual' => 1])['codigo'] === 403);
comprobar('grifero2 no borra un pago ajeno', $g2->pedir('controller/turnos/controlador_eliminar_pago_reporte.php', ['id_pago' => $pago])['codigo'] === 403);
comprobar('grifero2 no registra pagos en un turno ajeno', $g2->pedir('controller/turnos/controlador_registrar_pago.php', ['id_reporte' => $turno, 'id_tipo_pago' => 4, 'codigo_operacion' => '', 'monto' => 1, 'observaciones' => ''])['codigo'] === 403);
comprobar('el pago ajeno sigue ahí', (int) $pdo->query("SELECT COUNT(*) FROM pagos_reporte WHERE id_pago_reporte = $pago")->fetchColumn() === 1);
comprobar('un turno inexistente responde 404', $g2->pedir('controller/turnos/controlador_detalle_turno.php', ['id_reporte' => 999999])['codigo'] === 404);
comprobar('el dueño sí lee su turno', $g1->pedir('controller/turnos/controlador_detalle_turno.php', ['id_reporte' => $turno])['codigo'] === 200);
$pdf = $g1->pedir("view/MPDF/REPORTE/reporte_turno.php?id=$turno");
comprobar('el dueño abre su PDF', $pdf['codigo'] === 200 && strncmp($pdf['cuerpo'], '%PDF', 4) === 0);
comprobar('el administrador lee cualquiera', $admin->pedir('controller/turnos/controlador_detalle_lecturas.php', ['id_reporte' => $turno])['codigo'] === 200);

echo "\n2. Varias máquinas\n";
$prod = (int) $pdo->query("SELECT id_producto FROM productos LIMIT 1")->fetchColumn();
$pdo->exec("INSERT INTO surtidores (numero_maquina, codigo, id_producto, lectura_actual) VALUES (3, 'X1', $prod, 500)");
$nuevo = (int) $pdo->lastInsertId();
$vista = $g1->pedir('view/turnos/view_registrar_turno.php')['cuerpo'];
comprobar('el formulario ya no tiene máquinas fijas', strpos($vista, 'tabla_lecturas_maquina_1') === false && strpos($vista, 'id="maquinas_lecturas"') !== false);
$pdf = $admin->pedir("view/MPDF/REPORTE/reporte_turno.php?id=$turno");
comprobar('el PDF se sigue generando', strncmp($pdf['cuerpo'], '%PDF', 4) === 0);
$admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $turno, 'motivo' => 'prueba']);
$pdo->exec("DELETE FROM surtidores WHERE id_surtidor = $nuevo");

echo "\n3. Copias de seguridad\n";
foreach (glob(dirname(__DIR__) . '/respaldos/respaldo_*') as $f) { unlink($f); }
comprobar('un grifero no genera copias', $g1->pedir('controller/respaldos/controlador_generar.php', [])['codigo'] === 403);
$avisos = array_column($admin->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'], null, 'clave');
comprobar('sin copias, el Dashboard avisa', isset($avisos['respaldo_antiguo']), json_encode(array_keys($avisos)));
$r = $admin->pedir('controller/respaldos/controlador_generar.php', []);
comprobar('genera una copia', !empty($r['json']['success']) && $r['json']['bytes'] > 1000, $r['cuerpo']);
$archivo = (string) ($r['json']['archivo'] ?? '');
$avisos = array_column($admin->pedir('controller/dashboard/controlador_alertas.php', [])['json']['data'], null, 'clave');
comprobar('con una copia reciente, el aviso desaparece', !isset($avisos['respaldo_antiguo']));
comprobar('la carpeta no se sirve por web', $admin->pedir("respaldos/$archivo")['codigo'] === 403);
comprobar('un grifero no la descarga', $g1->pedir("controller/respaldos/controlador_descargar.php?archivo=$archivo")['codigo'] === 403);
comprobar('no se puede pedir otro archivo', $admin->pedir('controller/respaldos/controlador_descargar.php?archivo=../.env')['codigo'] === 404);
$descarga = $admin->pedir("controller/respaldos/controlador_descargar.php?archivo=$archivo");
comprobar('el administrador la descarga', $descarga['codigo'] === 200 && strlen($descarga['cuerpo']) === (int) $r['json']['bytes']);

echo "\n4. La copia se restaura y queda idéntica\n";
$sql = str_ends_with($archivo, '.gz') ? gzdecode($descarga['cuerpo']) : $descarga['cuerpo'];
comprobar('incluye tablas, datos y procedimientos', strpos($sql, 'CREATE TABLE `usuario`') !== false && strpos($sql, 'INSERT INTO `usuario`') !== false && strpos($sql, 'CREATE PROCEDURE `SP_LISTAR_USUARIO`') !== false);
comprobar('sin DEFINER, restaurable en un hosting', strpos($sql, 'DEFINER=') === false);
$tmp = tempnam(sys_get_temp_dir(), 'sql');
file_put_contents($tmp, $sql);
$pdo->exec('DROP DATABASE IF EXISTS grifo2_prueba_restauracion');
$pdo->exec('CREATE DATABASE grifo2_prueba_restauracion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$salida = shell_exec('"' . $mysql . '" -u root -P 3307 -h 127.0.0.1 grifo2_prueba_restauracion < "' . $tmp . '" 2>&1');
unlink($tmp);
comprobar('mysql la importa sin errores', trim((string) $salida) === '', (string) $salida);

$distintas = [];
foreach ($pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN) as $tabla) {
    if ($tabla === 'auditoria') {
        // Generar y descargar la copia se registran después de hacerla: se comparan las filas que ya existían.
        $maximo = (int) $pdo->query('SELECT COALESCE(MAX(id_auditoria), 0) FROM grifo2_prueba_restauracion.auditoria')->fetchColumn();
        $huella = fn($db) => $pdo->query("SELECT COUNT(*), COALESCE(SUM(CRC32(CONCAT_WS('|', id_auditoria, usuario, accion, detalle, creado_en))), 0) FROM $db.auditoria WHERE id_auditoria <= $maximo")->fetch(PDO::FETCH_NUM);
        if ($huella('grifo2') !== $huella('grifo2_prueba_restauracion')) { $distintas[] = $tabla; }
        continue;
    }
    $a = $pdo->query("CHECKSUM TABLE grifo2.`$tabla`")->fetch(PDO::FETCH_NUM)[1];
    $b = $pdo->query("CHECKSUM TABLE grifo2_prueba_restauracion.`$tabla`")->fetch(PDO::FETCH_NUM)[1];
    if ($a !== $b) { $distintas[] = $tabla; }
}
comprobar('todas las tablas son idénticas a la original', !$distintas, implode(', ', $distintas));
$procs = fn($db) => (int) $pdo->query("SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '$db'")->fetchColumn();
comprobar('restaura los procedimientos', $procs('grifo2') === $procs('grifo2_prueba_restauracion') && $procs('grifo2') > 0);
$hash = $pdo->query("SELECT usu_contrasenia FROM grifo2_prueba_restauracion.usuario WHERE usu_usuario = 'admin'")->fetchColumn();
comprobar('las contraseñas siguen funcionando', password_verify('admin123', (string) $hash));
$pdo->exec('DROP DATABASE grifo2_prueba_restauracion');

echo "\n5. Conservación\n";
$admin->pedir('controller/configuracion/controlador_guardar.php', ['respaldo_conservar' => '2']);
for ($i = 0; $i < 2; $i++) { sleep(1); $admin->pedir('controller/respaldos/controlador_generar.php', []); }
$lista = $admin->pedir('controller/respaldos/controlador_listar.php', [])['json']['data'] ?? [];
comprobar('conserva solo las 2 más recientes', count($lista) === 2 && count(glob(dirname(__DIR__) . '/respaldos/respaldo_*')) === 2, (string) count($lista));
comprobar('la más reciente va primero', strcmp($lista[0]['archivo'], $lista[1]['archivo']) > 0);
$r = $admin->pedir('controller/respaldos/controlador_eliminar.php', ['archivo' => $lista[1]['archivo']]);
comprobar('elimina una copia', !empty($r['json']['success']) && count(glob(dirname(__DIR__) . '/respaldos/respaldo_*')) === 1);
comprobar('eliminar un nombre inventado falla', ($admin->pedir('controller/respaldos/controlador_eliminar.php', ['archivo' => '../index.php'])['json']['success'] ?? true) === false);
comprobar('index.php sigue ahí', is_file(dirname(__DIR__) . '/index.php'));
comprobar('queda en la auditoría', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion IN ('RESPALDO_GENERADO', 'RESPALDO_DESCARGADO', 'RESPALDO_ELIMINADO')")->fetchColumn() >= 4);
$admin->pedir('controller/configuracion/controlador_guardar.php', ['respaldo_conservar' => '14']);
$cli = shell_exec('"' . PHP_BINARY . '" "' . dirname(__DIR__) . '/scripts/respaldo.php" 2>&1');
comprobar('el script para cron funciona', preg_match('/^respaldo_\d{8}_\d{6}_[0-9a-f]{6}\.sql/', (string) $cli) === 1, (string) $cli);
foreach (glob(dirname(__DIR__) . '/respaldos/respaldo_*') as $f) { unlink($f); }

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
