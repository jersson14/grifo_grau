<?php
/**
 * Prueba de la página pública y su administración contra el sistema en ejecución.
 * Requiere los datos de demostración. Deja el módulo y el contenido como estaban.
 *
 *   php tests/pagina_publica.php
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
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : " -> " . substr($detalle, 0, 300)) . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'pub'); }
    public function pedir(string $ruta, ?array $post = null, array $cabeceras = []): array
    {
        $c = curl_init($this->base . '/' . $ruta);
        $h = $cabeceras;
        if ($this->csrf !== '') { $h[] = 'X-CSRF-Token: ' . $this->csrf; }
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies, CURLOPT_HTTPHEADER => $h, CURLOPT_TIMEOUT => 30]);
        if ($post !== null) { curl_setopt($c, CURLOPT_POST, true); curl_setopt($c, CURLOPT_POSTFIELDS, $post); }
        $cuerpo = (string) curl_exec($c);
        return ['codigo' => curl_getinfo($c, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'destino' => (string) curl_getinfo($c, CURLINFO_REDIRECT_URL)];
    }
    public function json(string $ruta, array $post = []): array
    {
        return json_decode($this->pedir($ruta, $post)['cuerpo'], true) ?: [];
    }
    public function entrar(string $u, string $c): void
    {
        $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
    }
}

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();

// Estado original para restaurarlo al final.
$moduloAntes = (int) $pdo->query("SELECT activo FROM modulos WHERE codigo = 'pagina_publica'")->fetchColumn();
$datosAntes = $pdo->query('SELECT clave, valor FROM pagina_publica')->fetchAll(PDO::FETCH_KEY_PAIR);
$maxServicio = (int) $pdo->query('SELECT COALESCE(MAX(id_servicio), 0) FROM pagina_servicios')->fetchColumn();
$maxPromo = (int) $pdo->query('SELECT COALESCE(MAX(id_promocion), 0) FROM promociones')->fetchColumn();
$maxSolicitud = (int) $pdo->query('SELECT COALESCE(MAX(id_solicitud), 0) FROM solicitudes_flota')->fetchColumn();
$precioAntes = $pdo->query('SELECT id_producto, precio_actual FROM productos WHERE id_producto = 2')->fetch(PDO::FETCH_ASSOC);
$modulo = fn(int $activo) => $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'pagina_publica'")->execute([$activo]);

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');

try {
    echo "1. Qué muestra la dirección del sistema\n";
    $modulo(0);
    $visita = new Cliente($base);
    $r = $visita->pedir('index.php');
    comprobar('con el módulo apagado muestra el acceso', strpos($r['cuerpo'], 'id="txt_usuario"') !== false);
    comprobar('el acceso también está en login.php', strpos($visita->pedir('login.php')['cuerpo'], 'id="txt_usuario"') !== false);
    $r = $admin->pedir('index.php?vista_previa=1');
    comprobar('quien gestiona la página la ve en vista previa', strpos($r['cuerpo'], 'id="form_flota"') !== false && strpos($r['cuerpo'], 'Vista previa') !== false);
    $r = $grifero->pedir('index.php?vista_previa=1');
    comprobar('el grifero no tiene vista previa: va a su panel', $r['codigo'] === 302 && strpos($r['destino'], 'view/index.php') !== false, $r['codigo'] . ' ' . $r['destino']);
    $r = $visita->pedir('publico/solicitud_flota.php', ['empresa' => 'X']);
    comprobar('con el módulo apagado no se reciben solicitudes', $r['codigo'] === 404, (string) $r['codigo']);

    $modulo(1);
    $r = $visita->pedir('index.php');
    comprobar('con el módulo activo muestra la página pública', strpos($r['cuerpo'], 'id="form_flota"') !== false && strpos($r['cuerpo'], 'txt_usuario') === false);
    comprobar('la página enlaza el acceso del personal', strpos($r['cuerpo'], 'href="login.php"') !== false);
    $r = $admin->pedir('index.php');
    comprobar('con sesión abierta lleva al panel', $r['codigo'] === 302 && strpos($r['destino'], 'view/index.php') !== false);

    echo "\n2. Precios en vivo\n";
    $pdo->exec("UPDATE pagina_publica SET valor = '1' WHERE clave = 'mostrar_precios'");
    $pdo->exec('UPDATE productos SET precio_actual = 13.450 WHERE id_producto = 2');
    $pdo->exec("INSERT INTO precios_historial (id_producto, precio_anterior, precio_nuevo, origen) VALUES (2, 13.000, 13.450, 'TEST_PUB')");
    $r = $visita->pedir('index.php');
    comprobar('muestra el precio vigente de Productos', strpos($r['cuerpo'], 'data-valor="13.45"') !== false);
    // El cambio se mide desde el primer precio del día, aunque haya habido varios cambios hoy.
    $inicioDia = (float) $pdo->query('SELECT precio_anterior FROM precios_historial WHERE id_producto = 2 AND creado_en >= CURDATE() ORDER BY id_historial LIMIT 1')->fetchColumn();
    $esperado = 'sello-sube mt-3" aria-hidden="true"><i class="fas fa-arrow-up"></i>Subió ' . number_format(13.45 - $inicioDia, 2);
    comprobar('marca el cambio del día con su monto', strpos($r['cuerpo'], $esperado) !== false, $esperado);
    $pdo->exec("UPDATE pagina_publica SET valor = '0' WHERE clave = 'mostrar_precios'");
    comprobar('los precios se pueden ocultar', strpos($visita->pedir('index.php')['cuerpo'], 'class="lodera"') === false);
    $pdo->exec("UPDATE pagina_publica SET valor = '1' WHERE clave = 'mostrar_precios'");

    echo "\n3. Datos de la página\n";
    $r = $grifero->pedir('controller/publico/controlador_guardar_datos.php', ['frase' => 'X']);
    comprobar('el grifero no puede editar la página', $r['codigo'] === 403, (string) $r['codigo']);
    $r = $admin->json('controller/publico/controlador_guardar_datos.php', ['frase' => '', 'mapa_url' => 'http://inseguro.com', 'whatsapp' => 'abc']);
    comprobar('valida cada campo y no guarda nada si hay errores', ($r['guardado'] ?? true) === false && isset($r['errores']['frase'], $r['errores']['mapa_url'], $r['errores']['whatsapp']), json_encode($r));
    $r = $admin->json('controller/publico/controlador_guardar_datos.php', [
        'frase' => 'Crédito ñandú <script>window.x=1</script>', 'whatsapp' => '987 654 321', 'direccion' => 'Calle Prueba 1', 'mapa_url' => '', 'clave_ajena' => 'no',
    ]);
    comprobar('guarda los datos válidos', ($r['guardado'] ?? false) === true, json_encode($r));
    comprobar('ignora campos que no están permitidos', (int) $pdo->query("SELECT COUNT(*) FROM pagina_publica WHERE clave = 'clave_ajena'")->fetchColumn() === 0);
    $cuerpo = $visita->pedir('index.php')['cuerpo'];
    comprobar('el texto con HTML se muestra escapado', strpos($cuerpo, '<script>window.x=1</script>') === false && strpos($cuerpo, '&lt;script&gt;') !== false);
    comprobar('las tildes y la eñe guardadas desde el panel llegan intactas', strpos($cuerpo, 'Crédito ñandú') !== false);
    comprobar('un celular de 9 dígitos se enlaza con el código del país', strpos($cuerpo, 'https://wa.me/51987654321') !== false);
    comprobar('sin enlace de mapa se busca la dirección', strpos($cuerpo, 'google.com/maps/search/?api=1&amp;query=') !== false);

    echo "\n4. Servicios y promociones\n";
    $r = $admin->json('controller/publico/controlador_guardar_servicio.php', ['titulo' => 'Lavado de prueba', 'descripcion' => 'Rápido', 'icono' => 'soap', 'activo' => '1']);
    $idServicio = (int) ($r['id_servicio'] ?? 0);
    comprobar('agrega un servicio', $idServicio > 0, json_encode($r));
    $r = $admin->json('controller/publico/controlador_guardar_servicio.php', ['titulo' => 'Malo', 'icono' => 'bomb', 'activo' => '1']);
    comprobar('rechaza un icono fuera de la lista', ($r['success'] ?? true) === false);
    comprobar('el servicio aparece en la página', strpos($visita->pedir('index.php')['cuerpo'], 'Lavado de prueba') !== false);
    $admin->json('controller/publico/controlador_mover_servicio.php', ['id_servicio' => $idServicio, 'direccion' => 'ARRIBA']);
    $orden = $pdo->query('SELECT id_servicio FROM pagina_servicios ORDER BY orden, id_servicio')->fetchAll(PDO::FETCH_COLUMN);
    comprobar('subir un servicio cambia su orden', array_search((string) $idServicio, array_map('strval', $orden), true) === count($orden) - 2);
    $admin->json('controller/publico/controlador_guardar_servicio.php', ['id_servicio' => $idServicio, 'titulo' => 'Lavado de prueba', 'icono' => 'soap', 'activo' => '0']);
    comprobar('un servicio oculto no se muestra', strpos($visita->pedir('index.php')['cuerpo'], 'Lavado de prueba') === false);

    $hoy = date('Y-m-d');
    $r = $admin->json('controller/publico/controlador_guardar_promocion.php', ['titulo' => 'Promo vigente de prueba', 'detalle' => 'Promoción con crédito', 'desde' => $hoy, 'hasta' => date('Y-m-d', strtotime('+5 days')), 'activo' => '1']);
    comprobar('agrega una promoción', ($r['id_promocion'] ?? 0) > 0, json_encode($r));
    $admin->json('controller/publico/controlador_guardar_promocion.php', ['titulo' => 'Promo vencida de prueba', 'desde' => date('Y-m-d', strtotime('-10 days')), 'hasta' => date('Y-m-d', strtotime('-1 day')), 'activo' => '1']);
    $admin->json('controller/publico/controlador_guardar_promocion.php', ['titulo' => 'Promo futura de prueba', 'desde' => date('Y-m-d', strtotime('+3 days')), 'hasta' => '', 'activo' => '1']);
    $r = $admin->json('controller/publico/controlador_guardar_promocion.php', ['titulo' => 'Al revés', 'desde' => $hoy, 'hasta' => date('Y-m-d', strtotime('-1 day')), 'activo' => '1']);
    comprobar('rechaza una fecha final anterior a la inicial', ($r['success'] ?? true) === false);
    $cuerpo = $visita->pedir('index.php')['cuerpo'];
    comprobar('el detalle de la promoción conserva sus tildes', strpos($cuerpo, 'Promoción con crédito') !== false);
    comprobar('solo muestra las promociones vigentes', strpos($cuerpo, 'Promo vigente de prueba') !== false && strpos($cuerpo, 'Promo vencida de prueba') === false && strpos($cuerpo, 'Promo futura de prueba') === false);
    $estados = array_column($admin->json('controller/publico/controlador_datos.php')['promociones'] ?? [], 'estado', 'titulo');
    comprobar('el panel distingue vigente, vencida y programada', ($estados['Promo vigente de prueba'] ?? '') === 'VIGENTE' && ($estados['Promo vencida de prueba'] ?? '') === 'VENCIDA' && ($estados['Promo futura de prueba'] ?? '') === 'PROGRAMADA', json_encode($estados));

    echo "\n5. Solicitudes de flota\n";
    $pdo->exec("DELETE FROM solicitudes_flota WHERE ip IN ('127.0.0.1', '::1')");
    $visita2 = new Cliente($base);
    $pagina = $visita2->pedir('index.php')['cuerpo'];
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $pagina, $m);
    $token = $m[1] ?? '';
    $json = ['Accept: application/json'];
    $r = json_decode($visita2->pedir('publico/solicitud_flota.php', ['empresa' => 'Sin token', 'contacto' => 'A', 'telefono' => '987654321'], $json)['cuerpo'], true);
    comprobar('sin el token del formulario se rechaza', ($r['success'] ?? true) === false);
    $r = json_decode($visita2->pedir('publico/solicitud_flota.php', ['csrf_token' => $token, 'empresa' => '', 'ruc' => '123', 'contacto' => '', 'telefono' => 'abc'], $json)['cuerpo'], true);
    comprobar('devuelve los errores por campo', isset($r['errores']['empresa'], $r['errores']['ruc'], $r['errores']['contacto'], $r['errores']['telefono']), json_encode($r));
    $r = json_decode($visita2->pedir('publico/solicitud_flota.php', ['csrf_token' => $token, 'empresa' => 'Robot SAC', 'contacto' => 'R', 'telefono' => '987654321', 'sitio_web' => 'spam'], $json)['cuerpo'], true);
    comprobar('un robot que llena el campo trampa no se guarda', ($r['success'] ?? false) === true && (int) $pdo->query("SELECT COUNT(*) FROM solicitudes_flota WHERE empresa = 'Robot SAC'")->fetchColumn() === 0);
    $valida = ['csrf_token' => $token, 'empresa' => 'Transportes <b>Prueba</b> SAC', 'ruc' => '20123456789', 'contacto' => 'Ana Prueba', 'telefono' => '987 654 321', 'unidades' => '12', 'galones_mes' => '3000', 'mensaje' => 'Hola'];
    $r = json_decode($visita2->pedir('publico/solicitud_flota.php', $valida, $json)['cuerpo'], true);
    comprobar('registra una solicitud válida', ($r['success'] ?? false) === true, json_encode($r));
    $visita2->pedir('publico/solicitud_flota.php', $valida, $json);
    $visita2->pedir('publico/solicitud_flota.php', $valida, $json);
    $r = json_decode($visita2->pedir('publico/solicitud_flota.php', $valida, $json)['cuerpo'], true);
    comprobar('limita a 3 solicitudes por hora desde la misma conexión', ($r['success'] ?? true) === false && (int) $pdo->query("SELECT COUNT(*) FROM solicitudes_flota WHERE ruc = '20123456789'")->fetchColumn() === 3);
    $pdo->exec("DELETE FROM solicitudes_flota WHERE ip IN ('127.0.0.1', '::1') AND id_solicitud > $maxSolicitud AND id_solicitud NOT IN (SELECT * FROM (SELECT MIN(id_solicitud) FROM solicitudes_flota WHERE ruc = '20123456789') t)");
    $sinJs = $visita2->pedir('publico/solicitud_flota.php', ['csrf_token' => $token, 'empresa' => 'Sin JS SAC', 'contacto' => 'B', 'telefono' => 'mal']);
    comprobar('sin JavaScript vuelve al formulario', $sinJs['codigo'] === 302 && strpos($sinJs['destino'], 'index.php#flotas') !== false);
    $cuerpo = $visita2->pedir('index.php')['cuerpo'];
    comprobar('y muestra el error junto al campo con lo escrito', strpos($cuerpo, 'id="error_telefono"') !== false && strpos($cuerpo, 'value="Sin JS SAC"') !== false);

    $lista = $admin->json('controller/publico/controlador_solicitudes.php', ['estado' => 'NUEVA'])['solicitudes'] ?? [];
    $sol = array_values(array_filter($lista, fn($s) => $s['ruc'] === '20123456789'))[0] ?? null;
    comprobar('el administrador ve la solicitud nueva', $sol !== null && $sol['unidades'] == 12);
    comprobar('no expone la IP del visitante', $sol !== null && !array_key_exists('ip', $sol));
    $r = $grifero->pedir('controller/publico/controlador_solicitudes.php', ['estado' => '']);
    comprobar('el grifero no ve las solicitudes', $r['codigo'] === 403, (string) $r['codigo']);
    $r = $admin->json('controller/publico/controlador_estado_solicitud.php', ['id_solicitud' => $sol['id_solicitud'] ?? 0, 'estado' => 'ATENDIDA', 'nota' => 'Llamado']);
    $fila = $pdo->query('SELECT estado, nota, atendido_por FROM solicitudes_flota WHERE id_solicitud = ' . (int) ($sol['id_solicitud'] ?? 0))->fetch(PDO::FETCH_ASSOC);
    comprobar('cambia el estado y registra quién la atendió', ($r['success'] ?? false) && $fila['estado'] === 'ATENDIDA' && $fila['nota'] === 'Llamado' && (int) $fila['atendido_por'] === 1, json_encode($fila));
    $r = $admin->json('controller/publico/controlador_estado_solicitud.php', ['id_solicitud' => $sol['id_solicitud'] ?? 0, 'estado' => 'INVENTADO']);
    comprobar('rechaza un estado que no existe', ($r['success'] ?? true) === false);

    echo "\n6. Menú y permisos\n";
    $menu = $admin->pedir('view/index.php')['cuerpo'];
    comprobar('el administrador tiene «Página pública» en el menú', strpos($menu, 'publico/view_pagina_publica.php') !== false);
    comprobar('el grifero no la tiene', strpos($grifero->pedir('view/index.php')['cuerpo'], 'publico/view_pagina_publica.php') === false);
    $r = $admin->pedir('controller/usuario/controlador_cerrar_sesion.php');
    comprobar('cerrar sesión lleva al acceso', strpos($r['destino'], 'login.php') !== false, $r['destino']);
} finally {
    $pdo->exec("DELETE FROM pagina_servicios WHERE id_servicio > $maxServicio");
    $pdo->exec("DELETE FROM promociones WHERE id_promocion > $maxPromo");
    $pdo->exec("DELETE FROM solicitudes_flota WHERE id_solicitud > $maxSolicitud");
    $pdo->exec("DELETE FROM precios_historial WHERE origen = 'TEST_PUB'");
    $pdo->prepare('UPDATE productos SET precio_actual = ? WHERE id_producto = ?')->execute([$precioAntes['precio_actual'], $precioAntes['id_producto']]);
    $restaurar = $pdo->prepare('UPDATE pagina_publica SET valor = ? WHERE clave = ?');
    foreach ($datosAntes as $clave => $valor) { $restaurar->execute([$valor, $clave]); }
    $modulo($moduloAntes);
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
