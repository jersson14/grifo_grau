<?php
/**
 * Prueba de textos con caracteres especiales contra el sistema en ejecución.
 * Un nombre con & comillas y < > se guarda tal cual, se ve igual en la lista y sale escapado en el PDF y el Excel.
 *
 *   php tests/texto.php
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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'tex'); }
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
$maxCliente = (int) $pdo->query('SELECT COALESCE(MAX(id_cliente), 0) FROM clientes')->fetchColumn();
$maxCredito = (int) $pdo->query('SELECT COALESCE(MAX(id_credito), 0) FROM ventas_credito')->fetchColumn();
$nombre = 'O\'Brien & Hijos "SAC" <b>negrita</b>';
$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');

try {
    echo "1. Se guarda tal cual\n";
    $admin->pedir('controller/clientes/controlador_registrar_cliente.php', ['nombre' => $nombre, 'dni' => '20999999993', 'telefono' => '999 888 777', 'direccion' => 'Av. Grau & Jr. Lima']);
    $fila = $pdo->query("SELECT id_cliente, nombre_completo, direccion FROM clientes WHERE dni_ruc = '20999999993'")->fetch(PDO::FETCH_ASSOC);
    comprobar('el nombre queda en la base sin entidades HTML', $fila && $fila['nombre_completo'] === $nombre, json_encode($fila));
    comprobar('la dirección también', $fila && $fila['direccion'] === 'Av. Grau & Jr. Lima');
    $id = (int) ($fila['id_cliente'] ?? 0);

    $r = $admin->json('controller/creditos/controlador_agregar_credito_manual.php', ['id_cliente' => $id, 'numero_vale' => 'V-1&2', 'monto' => '120', 'observaciones' => 'Pago "pendiente" <urgente>', 'placa' => 'abc-123']);
    $vale = $pdo->query("SELECT numero_vale, observaciones FROM ventas_credito WHERE id_cliente = $id")->fetch(PDO::FETCH_ASSOC);
    comprobar('un vale con símbolos se guarda tal cual', $vale && $vale['numero_vale'] === 'V-1&2' && $vale['observaciones'] === 'Pago "pendiente" <urgente>', json_encode([$r, $vale]));

    echo "\n2. Al editar no se escapa dos veces\n";
    $admin->pedir('controller/clientes/controlador_modificar_cliente.php', ['id' => $id, 'nombre' => $nombre, 'dni' => '20999999993', 'telefono' => '999 888 777', 'direccion' => 'Av. Grau & Jr. Lima']);
    $otra = $pdo->query("SELECT nombre_completo FROM clientes WHERE id_cliente = $id")->fetchColumn();
    comprobar('guardar de nuevo el mismo nombre no lo cambia', $otra === $nombre, (string) $otra);

    echo "\n3. Las salidas escapan una sola vez\n";
    $pdf = $admin->pedir('controller/creditos/controlador_exportar_historial_vales_excel.php?id_cliente=' . $id);
    comprobar('el Excel muestra el nombre escapado una vez', strpos($pdf['cuerpo'], 'O&#039;Brien &amp; Hijos &quot;SAC&quot; &lt;b&gt;negrita&lt;/b&gt;') !== false && strpos($pdf['cuerpo'], '&amp;amp;') === false && strpos($pdf['cuerpo'], '<b>negrita') === false, substr($pdf['cuerpo'], 0, 200));
    comprobar('y el vale con su símbolo', strpos($pdf['cuerpo'], 'V-1&amp;2') !== false);
    $p = $admin->pedir('controller/creditos/controlador_exportar_historial_vales_pdf.php?id_cliente=' . $id);
    comprobar('el PDF del historial se genera', $p['codigo'] === 200 && str_starts_with($p['cuerpo'], '%PDF'), $p['codigo'] . ' ' . substr($p['cuerpo'], 0, 150));
} finally {
    $pdo->exec("DELETE FROM ventas_credito WHERE id_credito > $maxCredito");
    $pdo->exec("DELETE FROM clientes WHERE id_cliente > $maxCliente");
}

echo "\n4. La función esc() decodifica antes de escapar\n";
$codigo = file_get_contents(dirname(__DIR__) . '/controller/_bootstrap.php');
preg_match('/function esc\(\$valor\): string\s*\{\s*return (.*?);\s*\}/s', $codigo, $m);
$esc = eval('return function ($valor) { return ' . $m[1] . '; };');
comprobar('un dato antiguo ya escapado no se escapa dos veces', $esc('A &amp; B') === 'A &amp; B');
comprobar('un dato nuevo se escapa', $esc('A & <b>') === 'A &amp; &lt;b&gt;');
comprobar('acepta null', $esc(null) === '');

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
