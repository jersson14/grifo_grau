<?php
/**
 * Modo demostración (MODO_DEMO=1): lo bloqueado no responde para nadie, ni para el administrador,
 * aunque la base de datos diga otra cosa. Requiere los datos de demostración.
 *
 *   php tests/demo.php
 *
 * Añade MODO_DEMO=1 al .env mientras corre y lo quita al terminar, también si falla.
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'demo'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $curl = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_HTTPHEADER => $this->csrf ? ['X-CSRF-Token: ' . $this->csrf] : [], CURLOPT_TIMEOUT => 60]);
        if ($post !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
        $cuerpo = (string) curl_exec($curl);
        return ['codigo' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'json' => json_decode($cuerpo, true)];
    }
    public function entrar(string $u, string $c): array
    {
        $r = $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
        return $r;
    }
}

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();

// ---------- MODO_DEMO=1 en el .env, con restauración garantizada ----------
$archivoEnv = dirname(__DIR__) . '/.env';
$envOriginal = is_file($archivoEnv) ? file_get_contents($archivoEnv) : null;
$modulosAntes = $pdo->query("SELECT codigo, activo FROM modulos")->fetchAll(PDO::FETCH_KEY_PAIR);
$claveAntes = $pdo->query("SELECT debe_cambiar_clave FROM usuario WHERE usu_usuario = 'grifero1'")->fetchColumn();
$forzarAntes = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'clave_forzar_cambio'")->fetchColumn();

function poner_demo(?string $original, string $archivo, bool $activo): void
{
    $sin = preg_replace('/^\s*MODO_DEMO=.*\R?/m', '', (string) $original);
    file_put_contents($archivo, $activo ? rtrim($sin) . "\nMODO_DEMO=1\n" : $sin);
}

register_shutdown_function(function () use ($archivoEnv, $envOriginal, $pdo, $modulosAntes, $claveAntes, $forzarAntes) {
    if ($envOriginal === null) { @unlink($archivoEnv); } else { file_put_contents($archivoEnv, $envOriginal); }
    $q = $pdo->prepare('UPDATE modulos SET activo = ? WHERE codigo = ?');
    foreach ($modulosAntes as $codigo => $activo) { $q->execute([$activo, $codigo]); }
    $pdo->prepare("UPDATE usuario SET debe_cambiar_clave = ? WHERE usu_usuario = 'grifero1'")->execute([$claveAntes]);
    $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'clave_forzar_cambio'")->execute([$forzarAntes === false ? null : $forzarAntes]);
    echo 'MODO_DEMO en .env al terminar: ' . (preg_match('/^\s*MODO_DEMO=/m', (string) @file_get_contents($archivoEnv)) ? 'SÍ (revisar)' : 'no') . "\n";
});

// Aunque la base diga que están activos, en la demo deben quedar apagados.
$pdo->exec("UPDATE modulos SET activo = 1 WHERE codigo IN ('facturacion', 'chatbot_ia', 'personal', 'ventas', 'tienda', 'inventario', 'caja')");
$pdo->exec("UPDATE configuracion SET valor = 'ADMINISTRADOR' WHERE clave = 'ia_roles'");

echo "\n1. Fuera del modo demostración todo responde\n";
poner_demo($envOriginal, $archivoEnv, false);
$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
foreach (['view/usuario/view_usuario.php', 'view/configuracion/view_configuracion.php', 'view/reportes/view_analisis.php', 'view/personal/view_personal.php'] as $ruta) {
    comprobar("abre $ruta", $admin->pedir($ruta)['codigo'] === 200);
}
comprobar('sin la etiqueta «Demostración»', strpos($admin->pedir('view/index.php')['cuerpo'], 'g-demo') === false);

echo "\n2. Con MODO_DEMO=1: bloqueado incluso para el administrador\n";
poner_demo($envOriginal, $archivoEnv, true);
$bloqueadas = [
    'view/usuario/view_usuario.php'                              => null,
    'view/usuario/view_mi_perfil.php'                            => null,
    'view/usuario/view_cambiar_clave.php'                        => null,
    'controller/usuario/controlador_listar_usuario.php'          => [],
    'controller/usuario/controlador_registro_usuario.php'        => ['usuario' => 'intruso', 'contra' => 'Xy12345678!', 'rol' => 'ADMINISTRADOR'],
    'controller/usuario/controlador_modificar_usuario_contra.php' => ['id' => '1', 'contra' => 'Otra12345!'],
    'controller/usuario/controlador_cambiar_contrasena.php'      => ['actual' => 'admin123', 'nueva' => 'Otra12345!'],
    'controller/usuario/controlador_obtener_perfil.php'          => [],
    'view/administracion/view_roles.php'                         => null,
    'view/administracion/view_menu.php'                          => null,
    'view/administracion/view_auditoria.php'                     => null,
    'controller/administracion/controlador_guardar_rol.php'      => ['codigo' => 'X'],
    'controller/administracion/controlador_listar_auditoria.php' => [],
    'view/configuracion/view_configuracion.php'                  => null,
    'controller/configuracion/controlador_guardar.php'           => ['credito_dias' => '45'],
    'controller/configuracion/controlador_cambiar_modulo.php'    => ['codigo' => 'personal', 'activo' => '1'],
    'controller/respaldos/controlador_generar.php'               => [],
    'view/reportes/view_analisis.php'                            => null,
    'view/facturacion/view_comprobantes.php'                     => null,
    'controller/ia/controlador_preguntar.php'                    => ['pregunta' => 'ventas'],
    'view/personal/view_personal.php'                            => null,
];
foreach ($bloqueadas as $ruta => $post) {
    $r = $admin->pedir($ruta, $post);
    comprobar("no responde: $ruta", $r['codigo'] === 403, (string) $r['codigo']);
}
comprobar('no creó ningún usuario', (int) $pdo->query("SELECT COUNT(*) FROM usuario WHERE usu_usuario = 'intruso'")->fetchColumn() === 0);
comprobar('no cambió la configuración', $pdo->query("SELECT valor FROM configuracion WHERE clave = 'credito_dias'")->fetchColumn() !== '45');
$r = $admin->pedir('view/reportes/view_analisis.php');
comprobar('explica que es una demostración', strpos($r['cuerpo'], 'demostración') !== false, $r['cuerpo']);

$indice = $admin->pedir('view/index.php')['cuerpo'];
comprobar('muestra la etiqueta «Demostración»', strpos($indice, 'g-demo') !== false);
foreach (['usuario/view_usuario.php', 'administracion/view_roles.php', 'administracion/view_menu.php', 'administracion/view_auditoria.php',
          'configuracion/view_configuracion.php', 'reportes/view_analisis.php', 'facturacion/view_comprobantes.php',
          'personal/view_personal.php', 'usuario/view_mi_perfil.php'] as $vista) {
    comprobar("el menú no ofrece $vista", strpos($indice, $vista) === false);
}
comprobar('sin la burbuja del chatbot', strpos($indice, 'chat_panel') === false);
$ventas = $admin->pedir('view/ventas/view_ventas.php')['cuerpo'];
comprobar('la venta no ofrece boleta ni factura', strpos($ventas, 'value="FACTURA"') === false);

echo "\n3. Con MODO_DEMO=1: lo demás funciona\n";
foreach (['view/reportes/view_dashboard.php', 'view/turnos/view_registrar_turno.php', 'view/turnos/view_historial.php', 'view/ventas/view_ventas.php',
          'view/tienda/view_tienda.php', 'view/inventario/view_tanques.php', 'view/caja/view_caja.php', 'view/creditos/view_creditos_pendientes.php',
          'view/reportes/view_ventas_por_fecha.php', 'view/clientes/view_clientes.php', 'view/productos/view_productos.php'] as $ruta) {
    $r = $admin->pedir($ruta);
    comprobar("abre $ruta", $r['codigo'] === 200, (string) $r['codigo']);
}
$r = $admin->pedir('controller/turnos/controlador_tipos_pago.php', []);
comprobar('los controladores comunes responden', $r['codigo'] === 200, (string) $r['codigo']);
$r = $admin->pedir('controller/avisos/controlador_listar.php', []);
comprobar('los avisos no llevan a pantallas bloqueadas', $r['codigo'] === 200 && strpos($r['cuerpo'], 'view_configuracion') === false && strpos($r['cuerpo'], 'view_comprobantes') === false, $r['cuerpo']);

echo "\n4. Con MODO_DEMO=1: la cuenta de prueba no queda atrapada en «cambie su contraseña»\n";
$pdo->exec("UPDATE configuracion SET valor = '1' WHERE clave = 'clave_forzar_cambio'");
$pdo->exec("UPDATE usuario SET debe_cambiar_clave = 1 WHERE usu_usuario = 'grifero1'");
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$r = $grifero->pedir('view/turnos/view_registrar_turno.php');
comprobar('el grifero entra directo a registrar su turno', $r['codigo'] === 200, (string) $r['codigo']);
comprobar('y su inicio no es la pantalla de cambiar clave', strpos($grifero->pedir('view/index.php')['cuerpo'], "'usuario/view_cambiar_clave.php'") === false);

echo "\n5. Al quitar MODO_DEMO=1 vuelve todo\n";
poner_demo($envOriginal, $archivoEnv, false);
$pdo->exec("UPDATE usuario SET debe_cambiar_clave = 0 WHERE usu_usuario = 'grifero1'");
comprobar('Usuarios responde otra vez', $admin->pedir('view/usuario/view_usuario.php')['codigo'] === 200);
comprobar('Facturación responde otra vez', $admin->pedir('view/facturacion/view_comprobantes.php')['codigo'] === 200);

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
