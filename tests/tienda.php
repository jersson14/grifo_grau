<?php
/**
 * Prueba del módulo de tienda contra el sistema en ejecución.
 * Requiere los datos de demostración. Deja el módulo y los datos como estaban.
 *
 *   php tests/tienda.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'tie'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $c = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies, CURLOPT_TIMEOUT => 30,
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
$modulosAntes = $pdo->query("SELECT codigo, activo FROM modulos WHERE codigo IN ('tienda', 'flotas')")->fetchAll(PDO::FETCH_KEY_PAIR);
$maxProducto = (int) $pdo->query('SELECT COALESCE(MAX(id_producto), 0) FROM tienda_productos')->fetchColumn();
$maxVenta = (int) $pdo->query('SELECT COALESCE(MAX(id_venta), 0) FROM tienda_ventas')->fetchColumn();
$alertasAntes = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'inicio_alertas'")->fetchColumn();
$modulo = fn(int $a) => $pdo->prepare("UPDATE modulos SET activo = ? WHERE codigo = 'tienda'")->execute([$a]);

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$R = 'controller/tienda/controlador_';
$hoy = date('Y-m-d');
$stock = fn(int $id) => (float) $pdo->query("SELECT stock FROM tienda_productos WHERE id_producto = $id")->fetchColumn();
$vender = fn(Cliente $c, array $items, string $medio = 'EFECTIVO', string $op = '') => $c->json($R . 'vender.php', ['items' => json_encode($items), 'medio_pago' => $medio, 'codigo_operacion' => $op]);

try {
    echo "1. Acceso\n";
    $modulo(0);
    comprobar('con el módulo apagado no responde', $grifero->pedir($R . 'datos.php', [])['codigo'] === 403);
    $modulo(1);
    $d = $grifero->json($R . 'datos.php');
    comprobar('el grifero puede vender (permiso por defecto)', isset($d['productos']) && ($d['gestiona'] ?? true) === false, json_encode($d));
    comprobar('el grifero no crea productos', $grifero->pedir($R . 'guardar_producto.php', ['nombre' => 'X', 'categoria' => 'Otros', 'unidad' => 'UND', 'precio_venta' => '1'])['codigo'] === 403);
    comprobar('el menú del grifero tiene la tienda', strpos($grifero->pedir('view/index.php')['cuerpo'], 'tienda/view_tienda.php') !== false);

    echo "\n2. Productos\n";
    $mal = fn(array $x) => $admin->json($R . 'guardar_producto.php', $x + ['nombre' => 'Producto', 'categoria' => 'Otros', 'unidad' => 'UND', 'precio_venta' => '10']);
    comprobar('rechaza un precio en cero', ($mal(['precio_venta' => '0'])['success'] ?? true) === false);
    comprobar('rechaza una categoría inventada', ($mal(['categoria' => 'Armas'])['success'] ?? true) === false);
    $r = $admin->json($R . 'guardar_producto.php', ['nombre' => 'Aceite 20W-50 prueba', 'codigo' => 'TEST-ACE', 'categoria' => 'Lubricantes', 'unidad' => 'GLN', 'precio_venta' => '85.00', 'costo' => '60', 'stock_minimo' => '3', 'stock_inicial' => '10']);
    $aceite = (int) ($r['id_producto'] ?? 0);
    comprobar('crea un producto con stock inicial', $aceite > 0 && $stock($aceite) == 10, json_encode($r));
    comprobar('el stock inicial queda en el kardex', $pdo->query("SELECT tipo FROM tienda_movimientos WHERE id_producto = $aceite")->fetchColumn() === 'ENTRADA');
    comprobar('no repite códigos', ($mal(['codigo' => 'TEST-ACE'])['success'] ?? true) === false);
    $agua = (int) ($admin->json($R . 'guardar_producto.php', ['nombre' => 'Agua 625 ml prueba', 'categoria' => 'Bebidas', 'unidad' => 'UND', 'precio_venta' => '2.50', 'stock_inicial' => '2'])['id_producto'] ?? 0);

    echo "\n3. Stock\n";
    $r = $admin->json($R . 'entrada.php', ['id_producto' => $aceite, 'cantidad' => '5', 'costo' => '62', 'detalle' => 'Factura F001-99']);
    comprobar('registra un ingreso y actualiza el costo', ($r['stock'] ?? 0) == 15 && (float) $pdo->query("SELECT costo FROM tienda_productos WHERE id_producto = $aceite")->fetchColumn() == 62, json_encode($r));
    comprobar('rechaza un ingreso en cero', ($admin->json($R . 'entrada.php', ['id_producto' => $aceite, 'cantidad' => '0'])['success'] ?? true) === false);
    $r = $admin->json($R . 'ajustar.php', ['id_producto' => $aceite, 'contado' => '14', 'motivo' => 'Conteo de prueba']);
    comprobar('ajusta al conteo físico', ($r['stock'] ?? 0) == 14 && (float) $pdo->query("SELECT cantidad FROM tienda_movimientos WHERE id_producto = $aceite AND tipo = 'AJUSTE'")->fetchColumn() == -1, json_encode($r));
    comprobar('un conteo igual al stock no ajusta', ($admin->json($R . 'ajustar.php', ['id_producto' => $aceite, 'contado' => '14', 'motivo' => 'x'])['success'] ?? true) === false);
    comprobar('el ajuste exige motivo', ($admin->json($R . 'ajustar.php', ['id_producto' => $aceite, 'contado' => '13', 'motivo' => ''])['success'] ?? true) === false);

    echo "\n4. Venta\n";
    $r = $vender($grifero, [['id_producto' => $aceite, 'cantidad' => 2, 'precio' => 0.01], ['id_producto' => $agua, 'cantidad' => 1]]);
    $idV1 = (int) ($r['id_venta'] ?? 0);
    comprobar('el grifero vende con el precio del sistema (no el del navegador)', ($r['total'] ?? 0) == 172.50 && str_starts_with((string) ($r['numero'] ?? ''), 'T-'), json_encode($r));
    comprobar('descuenta el stock', $stock($aceite) == 12 && $stock($agua) == 1);
    comprobar('cada salida queda en el kardex con su venta', (int) $pdo->query("SELECT COUNT(*) FROM tienda_movimientos WHERE id_venta = $idV1 AND tipo = 'VENTA'")->fetchColumn() === 2);
    $r = $vender($grifero, [['id_producto' => $aceite, 'cantidad' => 1], ['id_producto' => $agua, 'cantidad' => 5]]);
    comprobar('sin stock suficiente no vende nada', ($r['success'] ?? true) === false && str_contains($r['message'] ?? '', 'Stock insuficiente') && $stock($aceite) == 12, json_encode($r));
    comprobar('Yape exige código de operación', ($vender($grifero, [['id_producto' => $agua, 'cantidad' => 1]], 'YAPE')['success'] ?? true) === false);
    $r = $vender($admin, [['id_producto' => $agua, 'cantidad' => 1]], 'YAPE', 'OP-123');
    $idV2 = (int) ($r['id_venta'] ?? 0);
    comprobar('vende con Yape y código', $idV2 > 0 && $stock($agua) == 0, json_encode($r));
    comprobar('un producto agotado no se vende', ($vender($grifero, [['id_producto' => $agua, 'cantidad' => 1]])['success'] ?? true) === false);
    comprobar('rechaza cantidades inválidas', ($vender($grifero, [['id_producto' => $aceite, 'cantidad' => -1]])['success'] ?? true) === false);

    echo "\n5. Ventas, resumen y anulación\n";
    $vg = $grifero->json($R . 'ventas.php', ['desde' => $hoy, 'hasta' => $hoy]);
    $ids = array_map('intval', array_column($vg['ventas'] ?? [], 'id_venta'));
    comprobar('el grifero ve solo sus ventas', in_array($idV1, $ids, true) && !in_array($idV2, $ids, true));
    $va = $admin->json($R . 'ventas.php', ['desde' => $hoy, 'hasta' => $hoy]);
    $medios = array_column($va['resumen']['por_medio'] ?? [], 'total', 'medio');
    comprobar('el resumen separa por medio de pago', ($medios['EFECTIVO'] ?? 0) >= 172.50 && ($medios['YAPE'] ?? 0) >= 2.50, json_encode($medios));
    $prod = array_column($va['resumen']['por_producto'] ?? [], null, 'nombre');
    comprobar('calcula el margen con el costo del momento', ($prod['Aceite 20W-50 prueba']['margen'] ?? null) == 46.0, json_encode($prod['Aceite 20W-50 prueba'] ?? null));
    comprobar('el grifero no anula ventas', $grifero->pedir($R . 'anular.php', ['id_venta' => $idV1, 'motivo' => 'x'])['codigo'] === 403);
    comprobar('anular exige motivo', ($admin->json($R . 'anular.php', ['id_venta' => $idV1, 'motivo' => ''])['success'] ?? true) === false);
    comprobar('anula y devuelve el stock', ($admin->json($R . 'anular.php', ['id_venta' => $idV1, 'motivo' => 'Prueba'])['success'] ?? false) === true && $stock($aceite) == 14 && $stock($agua) == 1);
    comprobar('no anula dos veces', ($admin->json($R . 'anular.php', ['id_venta' => $idV1, 'motivo' => 'Otra'])['success'] ?? true) === false);
    $kardex = $admin->json($R . 'kardex.php', ['id_producto' => $aceite])['movimientos'] ?? [];
    comprobar('el kardex muestra la anulación y el stock resultante', ($kardex[0]['tipo'] ?? '') === 'ANULACION' && $kardex[0]['stock_resultante'] == 14, json_encode($kardex[0] ?? null));

    echo "\n6. Avisos del tablero\n";
    $pdo->exec("UPDATE configuracion SET valor = CONCAT(valor, ',tienda_stock_bajo') WHERE clave = 'inicio_alertas' AND valor NOT LIKE '%tienda_stock_bajo%'");
    $pdo->exec("UPDATE tienda_productos SET stock_minimo = 5 WHERE id_producto = $agua");
    $avisos = array_column(json_decode($admin->pedir('controller/dashboard/controlador_alertas.php', [])['cuerpo'], true)['data'] ?? [], null, 'clave');
    comprobar('avisa de productos de la tienda con stock bajo', ($avisos['tienda_stock_bajo']['cantidad'] ?? 0) >= 1, json_encode($avisos));
    $opciones = array_column(json_decode($admin->pedir('controller/configuracion/controlador_obtener.php', [])['cuerpo'], true)['alertas'] ?? [], 'clave');
    comprobar('los dos avisos nuevos se pueden elegir en Configuración', in_array('tienda_stock_bajo', $opciones, true) && in_array('flotas_sobre_limite', $opciones, true), json_encode($opciones));
} finally {
    $pdo->exec("DELETE FROM tienda_movimientos WHERE id_producto > $maxProducto");
    $pdo->exec("DELETE FROM tienda_ventas_detalle WHERE id_venta > $maxVenta");
    $pdo->exec("DELETE FROM tienda_ventas WHERE id_venta > $maxVenta");
    $pdo->exec("DELETE FROM tienda_productos WHERE id_producto > $maxProducto");
    $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'inicio_alertas'")->execute([$alertasAntes]);
    foreach ($modulosAntes as $codigo => $activo) {
        $pdo->prepare('UPDATE modulos SET activo = ? WHERE codigo = ?')->execute([$activo, $codigo]);
    }
}

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
