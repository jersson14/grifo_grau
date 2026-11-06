<?php
/**
 * Prueba de humo contra el sistema en ejecución. Requiere los datos de demostración
 * (php database/migrate.php --demo). Deja la base como estaba.
 *
 *   php tests/smoke.php [http://localhost/grifo_grau]
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$base   = rtrim($argv[1] ?? 'http://localhost/grifo_grau', '/');
$raiz   = dirname(__DIR__);
$fallos = 0;
$total  = 0;

function comprobar(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $total;
    $total++;
    if (!$ok) {
        $fallos++;
    }
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : " -> $detalle") . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';

    public function __construct(private string $base)
    {
        $this->cookies = tempnam(sys_get_temp_dir(), 'smoke');
    }

    public function pedir(string $ruta, ?array $post = null, bool $conCsrf = true): array
    {
        $curl = curl_init($this->base . '/' . $ruta);
        $cabeceras = $conCsrf && $this->csrf !== '' ? ['X-CSRF-Token: ' . $this->csrf] : [];
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR      => $this->cookies,
            CURLOPT_COOKIEFILE     => $this->cookies,
            CURLOPT_HTTPHEADER     => $cabeceras,
            CURLOPT_TIMEOUT        => 30,
        ]);
        if ($post !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        $cuerpo = (string) curl_exec($curl);
        return ['codigo' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo];
    }

    public function entrar(string $usuario, string $contrasena): array
    {
        $r = $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $usuario, 'c' => $contrasena]);
        $panel = $this->pedir('view/index.php');
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $panel['cuerpo'], $m)) {
            $this->csrf = $m[1];
        }
        return $r;
    }
}

$archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz . '/controller', FilesystemIterator::SKIP_DOTS));
$controladores = [];
foreach ($archivos as $archivo) {
    if ($archivo->getExtension() === 'php' && strpos($archivo->getFilename(), 'controlador_') === 0) {
        $controladores[] = str_replace('\\', '/', substr($archivo->getPathname(), strlen($raiz) + 1));
    }
}
$vistas = array_merge(glob($raiz . '/view/*/view_*.php'), [$raiz . '/view/MPDF/REPORTE/reporte_turno.php']);
$vistas = array_map(fn($v) => str_replace('\\', '/', substr($v, strlen($raiz) + 1)), $vistas);
$publicos = ['controller/usuario/controlador_iniciar_sesion.php', 'controller/usuario/controlador_cerrar_sesion.php'];

echo "\n1. Sin sesión todo está cerrado\n";
$anonimo = new Cliente($base);
$abiertos = [];
foreach (array_merge($controladores, $vistas, ['consulta-dni-ajax.php', 'consultar-ruc-ajax.php']) as $ruta) {
    if (in_array($ruta, $publicos, true)) {
        continue;
    }
    $r = $anonimo->pedir($ruta, []);
    if ($r['codigo'] !== 401) {
        $abiertos[] = "$ruta ({$r['codigo']})";
    }
}
comprobar(count($controladores) . ' controladores y ' . count($vistas) . ' vistas responden 401', !$abiertos, implode(', ', $abiertos));

$r = $anonimo->pedir('controller/usuario/controlador_crear_sesion.php', ['idusuario' => 1, 'rol' => 'ADMINISTRADOR']);
comprobar('la sesión falsificada ya no existe', $r['codigo'] === 404, (string) $r['codigo']);
$r = $anonimo->pedir('view/usuario/view_usuario.php');
comprobar('sigue sin acceso tras intentarlo', $r['codigo'] === 401, (string) $r['codigo']);

$r = $anonimo->pedir('controller/usuario/controlador_registro_usuario.php', ['usu' => 'intruso', 'contra' => 'x', 'rol' => 'ADMINISTRADOR']);
comprobar('no se puede crear un usuario sin sesión', $r['codigo'] === 401, (string) $r['codigo']);

foreach (['.env', 'database/schema/000_base_provisional.sql', 'config/env.php', 'tests/smoke.php', 'README.md'] as $ruta) {
    $r = $anonimo->pedir($ruta);
    comprobar("$ruta no se sirve por web", $r['codigo'] === 403, (string) $r['codigo']);
}

echo "\n2. Inicio de sesión\n";
$r = $anonimo->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'admin', 'c' => 'incorrecta']);
comprobar('contraseña incorrecta rechazada', strpos($r['cuerpo'], '"ok":false') !== false, $r['cuerpo']);

$ataque = new Cliente($base);
for ($i = 0; $i < 5; $i++) {
    $r = $ataque->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => 'admin_inexistente', 'c' => 'x']);
}
comprobar('bloqueo tras 5 intentos', strpos($r['cuerpo'], 'bloqueado') !== false, $r['cuerpo']);

$admin = new Cliente($base);
$r = $admin->entrar('admin', 'admin123');
comprobar('administrador entra', strpos($r['cuerpo'], '"ok":true') !== false, $r['cuerpo']);
comprobar('la respuesta no expone el hash', strpos($r['cuerpo'], '$2y$') === false && strpos($r['cuerpo'], 'contrasenia') === false);
comprobar('el panel entrega token CSRF', $admin->csrf !== '');

echo "\n3. CSRF\n";
$r = $admin->pedir('controller/usuario/controlador_listar_usuario.php', [], false);
comprobar('POST sin token rechazado', $r['codigo'] === 403, (string) $r['codigo']);
$r = $admin->pedir('controller/usuario/controlador_listar_usuario.php', []);
comprobar('POST con token aceptado', $r['codigo'] === 200 && strpos($r['cuerpo'], 'admin') !== false, $r['cuerpo']);
comprobar('la lista de usuarios no expone hashes', strpos($r['cuerpo'], '$2y$') === false);

echo "\n4. Permisos por rol\n";
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
foreach (['controller/usuario/controlador_listar_usuario.php', 'controller/usuario/controlador_registro_usuario.php',
          'controller/turnos/controlador_eliminar_turno.php', 'controller/turnos/controlador_validar_reporte.php',
          'controller/productos/controlador_modificar_producto.php', 'controller/creditos/controlador_eliminar_credito.php',
          'view/usuario/view_usuario.php'] as $ruta) {
    $r = $grifero->pedir($ruta, []);
    comprobar("grifero sin acceso a " . basename($ruta), $r['codigo'] === 403, (string) $r['codigo']);
}
foreach (['view/turnos/view_registrar_turno.php', 'view/creditos/view_creditos_pendientes.php', 'view/clientes/view_clientes.php', 'view/usuario/view_mi_perfil.php'] as $ruta) {
    $r = $grifero->pedir($ruta);
    comprobar('grifero abre ' . basename($ruta), $r['codigo'] === 200, (string) $r['codigo']);
}

$r = $grifero->pedir('controller/usuario/controlador_obtener_perfil.php', ['id_usuario' => 1]);
$perfil = json_decode($r['cuerpo'], true);
comprobar('un grifero no puede leer el perfil de otro', ($perfil['usu_usuario'] ?? '') === 'grifero1', $r['cuerpo']);
comprobar('el perfil no expone el hash', !isset($perfil['usu_contrasenia']));

echo "\n5. Subida de archivos\n";
$falso = tempnam(sys_get_temp_dir(), 'img');
file_put_contents($falso, '<?php echo "ejecutado"; ?>');
$r = $admin->pedir('controller/usuario/controlador_registro_usuario.php', [
    'dni' => '1', 'nombre' => 'X', 'apelli' => 'X', 'correo' => '', 'tele' => '', 'dire' => '', 'usu' => 'con_foto_mala',
    'contra' => 'x', 'rol' => 'GRIFERO', 'nombrefoto' => 'shell.php',
    'foto' => new CURLFile($falso, 'image/png', 'shell.php'),
]);
comprobar('un .php disfrazado de imagen se rechaza', trim($r['cuerpo']) === '0', $r['cuerpo']);
$r = $anonimo->pedir('controller/usuario/fotos/shell.php');
comprobar('no quedó ningún archivo ejecutable', $r['codigo'] === 404 || $r['codigo'] === 403, (string) $r['codigo']);

echo "\n6. Flujo completo de un turno\n";
$lista = json_decode($grifero->pedir('controller/surtidores/controlador_surtidores_activos.php', [])['cuerpo'], true);
comprobar('surtidores disponibles', is_array($lista) && count($lista) === 12, count((array) $lista) . ' surtidores');
$cliente = json_decode($grifero->pedir('controller/clientes/controlador_clientes_activos.php', [])['cuerpo'], true);
$idCliente = $cliente[0]['id_cliente'] ?? ($cliente['data'][0]['id_cliente'] ?? null);

$lecturas = [];
$esperado = 0.0;
foreach ($lista as $s) {
    $final = (float) $s['lectura_actual'] + 10;
    $lecturas[] = ['id_surtidor' => $s['id_surtidor'], 'lectura_inicial' => $s['lectura_actual'], 'lectura_final' => $final, 'precio' => $s['precio_actual']];
    $esperado += 10 * (float) $s['precio_actual'];
}

$numero = 'SMK-' . random_int(1000, 9999);
$r = $grifero->pedir('controller/turnos/controlador_registrar_turno_completo.php', [
    'numero_documento' => $numero, 'id_usuario' => 1, 'turno' => 'DIA', 'fecha' => date('Y-m-d'), 'hora_inicio' => '06:00', 'hora_fin' => '14:00',
    'lecturas' => json_encode($lecturas),
    'pagos' => json_encode([['id_tipo_pago' => 1, 'codigo_operacion' => 'OP1', 'monto' => 100, 'observaciones' => '']]),
    'creditos' => json_encode([['id_cliente' => $idCliente, 'numero_vale' => 'V-' . $numero, 'placa' => 'abc-123', 'monto' => 50, 'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days'))]]),
    'descuentos' => 0, 'otros_gastos' => 0, 'monto_efectivo' => $esperado - 150,
]);
$turno = json_decode($r['cuerpo'], true);
comprobar('el grifero registra el turno', !empty($turno['success']), $r['cuerpo']);
$idReporte = (int) ($turno['id_reporte'] ?? 0);

$pdo = (function () use ($raiz) {
    require_once $raiz . '/model/model_conexion.php';
    return conexionBD::conexionPDO();
})();
$fila = $pdo->query("SELECT * FROM reportes_turno WHERE id_reporte = $idReporte")->fetch(PDO::FETCH_ASSOC);
$idGrifero = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();
comprobar('el turno queda a nombre de quien inició sesión, no del enviado', (int) $fila['id_usuario'] === $idGrifero, (string) $fila['id_usuario']);
comprobar('total de ventas correcto', abs((float) $fila['total_ventas'] - $esperado) < 0.01, $fila['total_ventas'] . " vs $esperado");
comprobar('galones correctos', abs((float) $fila['total_galones'] - 120) < 0.01, $fila['total_galones']);
comprobar('caja cuadrada, sin faltante', abs((float) $fila['monto_faltante']) < 0.01, $fila['monto_faltante']);
comprobar('la lectura del surtidor avanzó', (float) $pdo->query("SELECT lectura_actual FROM surtidores WHERE id_surtidor = {$lista[0]['id_surtidor']}")->fetchColumn() === (float) $lista[0]['lectura_actual'] + 10);

$r = $grifero->pedir('controller/turnos/controlador_listar_turnos.php', ['filtro_usuario' => 1]);
$turnos = json_decode($r['cuerpo'], true)['data'] ?? [];
comprobar('el grifero solo ve sus turnos', $turnos && count(array_filter($turnos, fn($t) => (int) $t['id_usuario'] !== $idGrifero)) === 0, $r['cuerpo']);

$r = $admin->pedir('controller/turnos/controlador_validar_reporte.php', ['id_reporte' => $idReporte]);
comprobar('el administrador valida el turno', trim($r['cuerpo']) === '1', $r['cuerpo']);

$r = $admin->pedir('view/MPDF/REPORTE/reporte_turno.php?id=' . $idReporte);
comprobar('el PDF del turno se genera', $r['codigo'] === 200 && strncmp($r['cuerpo'], '%PDF', 4) === 0, substr($r['cuerpo'], 0, 200));

echo "\n7. Crédito y pagos\n";
$idCredito = (int) $pdo->query("SELECT id_credito FROM ventas_credito WHERE id_reporte = $idReporte")->fetchColumn();
$r = $grifero->pedir('controller/creditos/controlador_registrar_pago_credito.php', [
    'id_credito' => $idCredito, 'id_tipo_pago' => 4, 'codigo_operacion' => '', 'monto_pagado' => 20, 'descuento' => 0,
    'fecha_pago' => date('Y-m-d'), 'id_usuario' => $idGrifero, 'observaciones' => 'prueba',
]);
comprobar('pago parcial registrado', trim($r['cuerpo']) === '1', $r['cuerpo']);
comprobar('saldo actualizado a 30', (float) $pdo->query("SELECT saldo_pendiente FROM ventas_credito WHERE id_credito = $idCredito")->fetchColumn() === 30.0);
$r = $grifero->pedir('controller/creditos/controlador_registrar_pago_credito.php', [
    'id_credito' => $idCredito, 'id_tipo_pago' => 4, 'codigo_operacion' => '', 'monto_pagado' => 999, 'descuento' => 0,
    'fecha_pago' => date('Y-m-d'), 'id_usuario' => $idGrifero, 'observaciones' => '',
]);
comprobar('un pago mayor al saldo se rechaza', trim($r['cuerpo']) === '-1', $r['cuerpo']);
$r = $grifero->pedir('controller/creditos/controlador_anular_pago_credito.php', ['id_credito' => $idCredito]);
comprobar('un grifero no puede anular pagos', $r['codigo'] === 403, (string) $r['codigo']);
$r = $admin->pedir('controller/creditos/controlador_anular_pago_credito.php', ['id_credito' => $idCredito]);
comprobar('el administrador anula el pago', strpos($r['cuerpo'], '1') !== false || strpos($r['cuerpo'], 'true') !== false, $r['cuerpo']);
comprobar('saldo restaurado a 50', (float) $pdo->query("SELECT saldo_pendiente FROM ventas_credito WHERE id_credito = $idCredito")->fetchColumn() === 50.0);

echo "\n8. Limpieza\n";
$r = $admin->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $idReporte, 'motivo' => 'prueba automatica']);
comprobar('el turno se elimina', strpos($r['cuerpo'], '"success":true') !== false, $r['cuerpo']);
comprobar('las lecturas se revirtieron', (float) $pdo->query("SELECT lectura_actual FROM surtidores WHERE id_surtidor = {$lista[0]['id_surtidor']}")->fetchColumn() === (float) $lista[0]['lectura_actual']);
$pdo->exec("DELETE FROM login_intentos");

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
