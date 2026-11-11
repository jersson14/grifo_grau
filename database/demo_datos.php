<?php
/**
 * Datos de prueba realistas para la demostración: 60 días de turnos, ventas, créditos de flotas y sus pagos,
 * compras de combustible, inventario de tanques, arqueos, depósitos, gastos, tienda y página pública.
 * Las fechas son relativas al día en que se ejecuta. Solo por consola.
 *
 *   php database/demo_datos.php              Llena una base recién instalada con `migrate.php --demo`.
 *   php database/demo_datos.php --reiniciar  Borra TODA la base y la vuelve a crear con datos de hoy.
 *                                            Solo con MODO_DEMO=1: pensado para un cron nocturno de la demo.
 *   php database/demo_datos.php --dias=90    Cambia el periodo (por defecto 60 días).
 *
 * Usa la base del .env o de las variables DB_* del entorno (DB_NAME=grifo_demo php database/demo_datos.php).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$raiz = dirname(__DIR__);
require_once $raiz . '/config/env.php';
$opciones = getopt('', ['reiniciar', 'dias::']);
$dias = max(14, min(180, (int) ($opciones['dias'] ?? 60)));

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3307'), env('DB_NAME')),
    env('DB_USER', 'root'),
    env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->exec("SET time_zone = '-05:00'");

// ---------- Reinicio completo: solo en una instalación de demostración ----------
if (isset($opciones['reiniciar'])) {
    if (!modo_demo()) {
        fwrite(STDERR, "--reiniciar borra toda la base. Solo funciona con MODO_DEMO=1 en el .env.\n");
        exit(1);
    }
    echo 'Reiniciando la base ' . env('DB_NAME') . "\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    foreach ($pdo->query("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()")->fetchAll(PDO::FETCH_NUM) as [$tabla, $tipo]) {
        $pdo->exec(($tipo === 'VIEW' ? 'DROP VIEW ' : 'DROP TABLE ') . "`$tabla`");
    }
    foreach ($pdo->query("SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()")->fetchAll(PDO::FETCH_NUM) as [$rutina, $tipo]) {
        $pdo->exec("DROP $tipo `$rutina`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    // migrate.php lee $argv: se instala igual que `php database/migrate.php --demo`.
    $argvOriginal = $argv;
    $argv = ['migrate.php', '--demo'];
    require __DIR__ . '/migrate.php';
    $argv = $argvOriginal;
}

// ---------- Comprobaciones ----------
if ((int) $pdo->query('SELECT COUNT(*) FROM reportes_turno')->fetchColumn() > 0) {
    fwrite(STDERR, "La base ya tiene turnos. Este script solo llena una base recién instalada con `migrate.php --demo`.\n");
    exit(1);
}
$admin = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'admin'")->fetchColumn();
if (!$admin) {
    fwrite(STDERR, "No está el usuario admin de la demostración. Instale primero con `php database/migrate.php --demo`.\n");
    exit(1);
}

require_once $raiz . '/model/model_conexion.php';
foreach (['model_inventario', 'model_compras', 'model_caja', 'model_gastos_grifo', 'model_tienda', 'model_flotas', 'model_creditos', 'model_eventos_turno'] as $m) {
    require_once $raiz . "/model/$m.php";
}

mt_srand(20261111);
$azar = fn(float $min, float $max): float => $min + mt_rand() / mt_getrandmax() * ($max - $min);
$elegir = fn(array $lista) => $lista[array_rand($lista)];
$operacion = fn(): string => (string) mt_rand(10000000, 99999999);
$hoy = date('Y-m-d');
$inicio = date('Y-m-d', strtotime("-$dias days"));
$dia = fn(int $desplazamiento): string => date('Y-m-d', strtotime("$inicio +$desplazamiento days"));
$paso = function (string $texto) { echo "  $texto\n"; };

echo "Generando $dias días de datos de demostración desde $inicio\n";

// ---------- Empresa, usuarios y página pública ----------
$pdo->exec("UPDATE empresa SET nombre = 'GRIFO SAN JUAN (DEMO)', email = 'contacto@grifo-demo.pe', telefono = '01 456 7890',
            direccion = 'Av. Panamericana Norte km 25, Lima'");
$crearUsuario = $pdo->prepare("INSERT INTO usuario (usu_nombre, usu_apellido, usu_usuario, usu_contrasenia, usu_rol, usu_foto, usu_estatus)
                               VALUES (?, ?, ?, ?, ?, 'img/avatar.png', 'ACTIVO')");
foreach ([['SOFÍA', 'RAMOS', 'supervisor1', 'supervisor123', 'SUPERVISOR'], ['MIGUEL', 'TORRES', 'contador1', 'contador123', 'CONTADOR']] as [$n, $a, $u, $c, $r]) {
    if (!$pdo->query("SELECT COUNT(*) FROM usuario WHERE usu_usuario = " . $pdo->quote($u))->fetchColumn()) {
        $crearUsuario->execute([$n, $a, $u, password_hash($c, PASSWORD_DEFAULT), $r]);
    }
}
$pdo->exec("UPDATE usuario SET usu_nombre = 'CARLOS', usu_apellido = 'MENDOZA' WHERE usu_usuario = 'grifero1'");
$pdo->exec("UPDATE usuario SET usu_nombre = 'LUIS', usu_apellido = 'QUISPE' WHERE usu_usuario = 'grifero2'");
$griferos = $pdo->query("SELECT id_usuario FROM usuario WHERE usu_rol = 'GRIFERO' ORDER BY id_usuario")->fetchAll(PDO::FETCH_COLUMN);
$supervisor = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'supervisor1'")->fetchColumn();

$pdo->exec("UPDATE modulos SET activo = 1 WHERE codigo IN ('pagina_publica', 'inventario', 'caja', 'gastos', 'tienda', 'flotas', 'ventas')");
$guardarPagina = $pdo->prepare('INSERT INTO pagina_publica (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
foreach ([
    'frase' => 'Tanque lleno, ruta segura', 'horario' => 'Abierto las 24 horas, todos los días',
    'descripcion' => 'Combustible de calidad, atención las 24 horas y crédito para empresas con flota.',
    'direccion' => 'Av. Panamericana Norte km 25, Lima', 'referencia' => 'Frente al paradero Los Álamos',
    'whatsapp' => '987654321', 'telefono' => '014567890', 'mostrar_precios' => '1', 'mostrar_grafico' => '1',
] as $clave => $valor) {
    $guardarPagina->execute([$clave, $valor]);
}
$pdo->exec('DELETE FROM promociones');
$promo = $pdo->prepare('INSERT INTO promociones (titulo, detalle, desde, hasta, activo, id_usuario) VALUES (?, ?, ?, ?, 1, ?)');
$promo->execute(['Doble punto para flotas', 'Las empresas con crédito acumulan el doble de puntos este mes.', date('Y-m-d', strtotime('-10 days')), date('Y-m-t'), $admin]);
$promo->execute(['Lavado gratis los martes', 'Por cada carga mayor a 10 galones de Premium 95.', date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('+2 days')), $admin]);
$promo->execute(['Aceite con 10 % de descuento', 'En aceites de motor de la tienda, pagando con Yape.', date('Y-m-d', strtotime('-3 days')), null, $admin]);
$servicio = $pdo->prepare('INSERT INTO pagina_servicios (titulo, descripcion, icono, orden, activo) VALUES (?, ?, ?, ?, 1)');
$servicio->execute(['Tienda y lubricantes', 'Aceites, aditivos, bebidas y snacks para el camino.', 'store', 50]);
$servicio->execute(['Aire y agua', 'Calibra tus llantas sin costo.', 'wind', 60]);
$pdo->exec("UPDATE tienda_productos SET nombre = TRIM(REPLACE(nombre, '(ejemplo)', ''))");
$pdo->exec("UPDATE promociones SET titulo = TRIM(REPLACE(titulo, '(ejemplo)', '')), detalle = TRIM(REPLACE(detalle, '(ejemplo)', ''))");
$pdo->exec("UPDATE pagina_publica SET valor = TRIM(REPLACE(valor, '(dato de ejemplo)', '')) WHERE clave = 'referencia'");
$paso('empresa, usuarios supervisor1 y contador1, página pública');

// ---------- Clientes y flotas ----------
$flotas = [];
$modeloFlotas = new Modelo_Flotas();
foreach ([
    ['TRANSPORTES RÁPIDO DEL NORTE S.A.C.', '20601234561', 'Jorge Salazar', '987111222', 15000, 30, [['ABC-123', 'Volvo FH 460', 'Pedro Huamán'], ['ABD-456', 'Volvo FH 460', 'Raúl Cárdenas'], ['B7K-881', 'Scania R450', 'Iván Rojas'], ['C2M-310', 'Scania R450', 'Julio Paredes']]],
    ['AGRÍCOLA LOS VALLES S.A.', '20512345672', 'María Gonzales', '987333444', 8000, 30, [['F4T-552', 'Toyota Hilux', 'Ernesto Vega'], ['F4T-553', 'Toyota Hilux', 'Óscar Lima'], ['T1Z-900', 'Tractor John Deere', 'Félix Mamani']]],
    ['CONSTRUCTORA ANDINA E.I.R.L.', '20498765433', 'Ricardo Flores', '987555666', 12000, 15, [['D9R-210', 'Volquete Volvo FMX', 'Hugo Ccori'], ['D9R-211', 'Volquete Volvo FMX', 'Walter Sánchez'], ['M3N-074', 'Mixer Mercedes', 'Alan Chávez']]],
    ['EMPRESA DE TRANSPORTES SAN JUAN S.A.C.', '20587654324', 'Lucía Paredes', '987777888', 10000, 30, [['P2Q-118', 'Mercedes Sprinter', 'Diego Alarcón'], ['P2Q-119', 'Mercedes Sprinter', 'Kevin Ramos'], ['P2Q-120', 'Hyundai County', 'Manuel Prado']]],
    ['DISTRIBUIDORA COMERCIAL LIMA S.A.C.', '20456789015', 'Andrés Núñez', '987999000', 5000, 15, [['K8L-301', 'Hyundai HD78', 'Jhon Torres'], ['K8L-302', 'JAC 1063', 'Renzo Díaz']]],
] as [$nombre, $ruc, $contacto, $telefono, $limite, $diasCredito, $placas]) {
    $id = $modeloFlotas->Guardar([
        'nueva' => '1', 'nombre' => $nombre, 'ruc' => $ruc, 'contacto' => $contacto, 'telefono' => $telefono, 'correo' => '',
        'limite_credito' => (string) $limite, 'dias_credito' => (string) $diasCredito, 'solo_placas' => '1', 'estado' => 'ACTIVA', 'observaciones' => '',
    ], $admin);
    foreach ($placas as [$placa, $vehiculo, $conductor]) {
        $modeloFlotas->Guardar_Placa($id, null, $placa, $vehiculo, $conductor, true);
    }
    $flotas[] = ['id' => $id, 'dias' => $diasCredito, 'placas' => array_column($placas, 0), 'peso' => $limite];
}
$pdo->exec("UPDATE flotas SET creado_en = '$inicio 09:00:00'");
$nuevoCliente = $pdo->prepare("INSERT INTO clientes (nombre_completo, dni_ruc, telefono, direccion, estado, id_usuario, created_at) VALUES (?, ?, ?, ?, 'ACTIVO', ?, ?)");
foreach ([
    ['JUAN CARLOS PÉREZ LÓPEZ', '41234567', '988123456', 'Mz. B Lt. 4, Los Olivos'], ['ROSA ELENA HUAMÁN QUISPE', '42345678', '988234567', 'Jr. Las Flores 230'],
    ['TAXI SEGURO EXPRESS S.A.C.', '20612345670', '988345678', 'Av. Universitaria 1200'], ['MOTOTAXIS UNIDOS DEL NORTE', '20623456781', '988456789', 'Calle 5, Puente Piedra'],
    ['FERRETERÍA EL CONSTRUCTOR E.I.R.L.', '20634567892', '988567890', 'Av. Naranjal 455'], ['PEDRO ANTONIO VARGAS RÍOS', '43456789', '988678901', 'Urb. San Diego'],
] as [$nombre, $doc, $telefono, $direccion]) {
    $nuevoCliente->execute([$nombre, $doc, $telefono, $direccion, $admin, "$inicio 09:00:00"]);
}
$clientesSueltos = $pdo->query("SELECT id_cliente FROM clientes WHERE id_cliente NOT IN (SELECT id_cliente FROM flotas) AND nombre_completo <> 'CLIENTE DEMO'")->fetchAll(PDO::FETCH_COLUMN);
$paso(count($flotas) . ' flotas con placas y ' . count($clientesSueltos) . ' clientes');

// ---------- Tanques, proveedores e inventario inicial ----------
$inventario = new Modelo_Inventario();
$compras = new Modelo_Compras();
$productos = $pdo->query("SELECT id_producto, tipo FROM productos WHERE estado = 'ACTIVO' ORDER BY id_producto")->fetchAll(PDO::FETCH_KEY_PAIR);
$capacidad = ['DIESEL' => 12000, 'REGULAR' => 6000, 'PREMIUM' => 4000];
$margen = ['DIESEL' => 1.55, 'REGULAR' => 1.85, 'PREMIUM' => 2.10];
$tanques = [];
foreach ($productos as $idProducto => $tipo) {
    $cap = $capacidad[$tipo] ?? 5000;
    $inventario->Guardar_Tanque(0, 'Tanque ' . ucfirst(strtolower($tipo)), (int) $idProducto, $cap, round($cap * 0.2), round($cap * 0.75), $admin);
    $tanques[$tipo] = ['id' => (int) $pdo->query("SELECT MAX(id_tanque) FROM tanques WHERE id_producto = $idProducto")->fetchColumn(), 'cap' => $cap, 'producto' => (int) $idProducto];
}
foreach ($pdo->query('SELECT s.id_surtidor, p.tipo FROM surtidores s JOIN productos p ON p.id_producto = s.id_producto')->fetchAll(PDO::FETCH_NUM) as [$idSurtidor, $tipo]) {
    if (isset($tanques[$tipo])) {
        $inventario->Asignar_Surtidor((int) $idSurtidor, $tanques[$tipo]['id']);
    }
}
$pdo->exec("UPDATE tanques SET creado_en = '$inicio 07:00:00'");
$pdo->exec("UPDATE movimientos_tanque SET creado_en = '$inicio 07:00:00'");
$compras->Guardar_Proveedor(0, '20100000011', 'DISTRIBUIDORA DE COMBUSTIBLES DEL PACÍFICO S.A.C.', '014445566', 'Av. Argentina 3200, Callao');
$compras->Guardar_Proveedor(0, '20100000022', 'PETRO ANDES DISTRIBUCIONES S.A.', '014447788', 'Av. Néstor Gambetta km 8, Callao');
$proveedores = $pdo->query('SELECT id_proveedor FROM proveedores ORDER BY id_proveedor')->fetchAll(PDO::FETCH_COLUMN);
$paso(count($tanques) . ' tanques con stock inicial y 2 proveedores');

// ---------- Precio vigente en cada momento (según el historial de precios) ----------
$historial = [];
foreach ($pdo->query('SELECT id_producto, precio_anterior, precio_nuevo, UNIX_TIMESTAMP(creado_en) AS t FROM precios_historial ORDER BY creado_en, id_historial')->fetchAll(PDO::FETCH_ASSOC) as $h) {
    $historial[(int) $h['id_producto']][] = $h;
}
$precioActual = $pdo->query('SELECT id_producto, precio_actual FROM productos')->fetchAll(PDO::FETCH_KEY_PAIR);
$precioEn = function (int $idProducto, int $t) use ($historial, $precioActual): float {
    $lista = $historial[$idProducto] ?? [];
    if (!$lista) {
        return (float) $precioActual[$idProducto];
    }
    $precio = (float) ($lista[0]['precio_anterior'] ?? $lista[0]['precio_nuevo']);
    foreach ($lista as $h) {
        if ((int) $h['t'] <= $t) {
            $precio = (float) $h['precio_nuevo'];
        }
    }
    return $precio;
};

// ---------- Turnos, compras, arqueos y depósitos día por día ----------
$surtidores = $pdo->query("SELECT s.id_surtidor, s.id_producto, s.lectura_actual, p.tipo FROM surtidores s
                           JOIN productos p ON p.id_producto = s.id_producto WHERE s.estado = 'ACTIVO' ORDER BY s.id_surtidor")->fetchAll(PDO::FETCH_ASSOC);
$lectura = array_column($surtidores, 'lectura_actual', 'id_surtidor');
$tiposPago = $pdo->query('SELECT codigo, id_tipo_pago FROM tipos_pago')->fetchAll(PDO::FETCH_KEY_PAIR);
$caja = new Modelo_Caja();

$insTurno = $pdo->prepare("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, hora_inicio, hora_fin, estado, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, 'CERRADO', ?)");
$insLectura = $pdo->prepare('INSERT INTO lecturas_turno (id_reporte, id_surtidor, lectura_anterior, lectura_actual, galones_vendidos, precio_galon, total_soles, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$insPago = $pdo->prepare('INSERT INTO pagos_reporte (id_reporte, id_tipo_pago, codigo_operacion, monto, observaciones, created_at) VALUES (?, ?, ?, ?, ?, ?)');
$insCredito = $pdo->prepare("INSERT INTO ventas_credito (id_reporte, id_cliente, id_usuario_grifero, numero_vale, placa, monto, saldo_pendiente, estado, fecha_vencimiento, observaciones, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', ?, '', ?)");
$totales = $pdo->prepare('UPDATE reportes_turno SET total_diesel = ?, total_regular = ?, total_premium = ?, total_ventas = ?, galones_diesel = ?, galones_regular = ?,
                          galones_premium = ?, total_galones = ?, monto_descuentos = ?, monto_otros_gastos = ?, monto_yape = ?, monto_bcp = ?, monto_visa = ?,
                          monto_efectivo = ?, monto_credito = ?, total_pagos = ?, monto_faltante = ?, id_administrador = ?, fecha_aprobacion = ?, updated_at = ?
                          WHERE id_reporte = ?');

// Galones por turno de día para cada combustible; la noche vende alrededor de la mitad.
$base = ['DIESEL' => 390, 'REGULAR' => 200, 'PREMIUM' => 95];
$numeroDoc = 1;
$numeroVale = 1001;
$efectivoPorDepositar = 0.0;
$contador = ['turnos' => 0, 'vales' => 0, 'compras' => 0, 'depositos' => 0, 'arqueos' => 0];
$faltantes = [];

for ($d = 0; $d < $dias; $d++) {
    $fecha = $dia($d);
    $finDeSemana = (int) date('N', strtotime($fecha)) >= 6;

    // Compra de combustible cuando un tanque baja del 40 %.
    $lineas = [];
    foreach ($tanques as $tipo => $t) {
        $stock = (float) $pdo->query("SELECT stock_actual FROM tanques WHERE id_tanque = {$t['id']}")->fetchColumn();
        if ($stock < $t['cap'] * 0.4) {
            $galones = floor(($t['cap'] * 0.92 - $stock) / 500) * 500;
            $costo = round($precioEn($t['producto'], strtotime("$fecha 07:00")) - $margen[$tipo] + $azar(-0.08, 0.08), 4);
            $lineas[] = ['id_tanque' => $t['id'], 'galones' => $galones, 'costo_galon' => $costo];
        }
    }
    if ($lineas) {
        $numero = 'F001-' . str_pad((string) (2300 + $contador['compras'] * 7), 8, '0', STR_PAD_LEFT);
        $compra = $compras->Registrar_Compra([
            'id_proveedor' => $elegir($proveedores), 'tipo_documento' => 'FACTURA', 'numero_documento' => $numero, 'fecha_compra' => $fecha,
            'fecha_vencimiento' => date('Y-m-d', strtotime("$fecha +15 days")), 'percepcion' => 0, 'observaciones' => 'Descarga de cisterna',
        ], $lineas, $admin);
        $idCompra = (int) ($compra['id_compra'] ?? $pdo->query('SELECT MAX(id_compra) FROM compras')->fetchColumn());
        $pdo->exec("UPDATE compras SET creado_en = '$fecha 07:30:00' WHERE id_compra = $idCompra");
        $pdo->exec("UPDATE movimientos_tanque SET creado_en = '$fecha 07:30:00' WHERE id_compra = $idCompra");
        if (strtotime("$fecha +12 days") < time()) {
            $total = (float) $pdo->query("SELECT total FROM compras WHERE id_compra = $idCompra")->fetchColumn();
            $compras->Registrar_Pago($idCompra, $total, date('Y-m-d', strtotime("$fecha +12 days")), 'TRANSFERENCIA', $operacion(), $admin);
        }
        $contador['compras']++;
    }

    foreach ([['DIA', '06:00:00', '18:00:00', 1.0], ['NOCHE', '18:00:00', '06:00:00', 0.52]] as $k => [$turno, $horaInicio, $horaFin, $factor]) {
        $grifero = (int) $griferos[($d + $k) % count($griferos)];
        $cierre = $turno === 'DIA' ? "$fecha 18:00:00" : date('Y-m-d', strtotime("$fecha +1 day")) . ' 06:00:00';
        if (strtotime($cierre) > time()) {
            continue; // el turno aún no termina
        }
        $tendencia = 1 + $d * 0.003;
        $insTurno->execute(['DOC-' . str_pad((string) $numeroDoc++, 4, '0', STR_PAD_LEFT), $grifero, $turno, $fecha, $horaInicio, $horaFin, $cierre]);
        $idReporte = (int) $pdo->lastInsertId();

        // Lecturas: el combustible del turno se reparte entre los surtidores de cada tipo.
        $porTipo = [];
        foreach ($surtidores as $s) {
            $porTipo[$s['tipo']][] = $s;
        }
        $soles = ['DIESEL' => 0, 'REGULAR' => 0, 'PREMIUM' => 0];
        $gal = ['DIESEL' => 0, 'REGULAR' => 0, 'PREMIUM' => 0];
        $totalVentas = 0.0;
        $totalGalones = 0.0;
        foreach ($porTipo as $tipo => $lista) {
            $semana = $finDeSemana ? ($tipo === 'DIESEL' ? 0.85 : 1.15) : 1.0;
            $galonesTipo = ($base[$tipo] ?? 80) * $factor * $semana * $tendencia * $azar(0.8, 1.2);
            $pesos = array_map(fn() => $azar(0.6, 1.4), $lista);
            foreach ($lista as $i => $s) {
                $id = (int) $s['id_surtidor'];
                $galones = round($galonesTipo * $pesos[$i] / array_sum($pesos), 3);
                $precio = $precioEn((int) $s['id_producto'], strtotime($cierre) - 3600);
                $total = round($galones * $precio, 2);
                $anterior = (float) $lectura[$id];
                $lectura[$id] = round($anterior + $galones, 3);
                $insLectura->execute([$idReporte, $id, $anterior, $lectura[$id], $galones, $precio, $total, $cierre]);
                $totalVentas += $total;
                $totalGalones += $galones;
                if (isset($soles[$tipo])) {
                    $soles[$tipo] += $total;
                    $gal[$tipo] += $galones;
                }
            }
        }
        $totalVentas = round($totalVentas, 2);

        // Vales de crédito de las flotas.
        $creditos = 0.0;
        for ($v = 0, $n = mt_rand($turno === 'DIA' ? 1 : 0, $turno === 'DIA' ? 4 : 2); $v < $n; $v++) {
            $f = $flotas[array_rand($flotas)];
            $monto = round($azar(80, 520) / 5) * 5;
            $insCredito->execute([$idReporte, $f['id'], $grifero, 'V-' . str_pad((string) $numeroVale++, 6, '0', STR_PAD_LEFT), $elegir($f['placas']),
                                  $monto, $monto, date('Y-m-d', strtotime("$fecha +{$f['dias']} days")), $cierre]);
            $creditos += $monto;
            $contador['vales']++;
        }
        // De vez en cuando, un cliente suelto con vale.
        if ($turno === 'DIA' && mt_rand(1, 6) === 1) {
            $monto = round($azar(40, 160) / 5) * 5;
            $insCredito->execute([$idReporte, $elegir($clientesSueltos), $grifero, 'V-' . str_pad((string) $numeroVale++, 6, '0', STR_PAD_LEFT), '',
                                  $monto, $monto, date('Y-m-d', strtotime("$fecha +15 days")), $cierre]);
            $creditos += $monto;
            $contador['vales']++;
        }

        // Pagos electrónicos.
        $medios = ['YAPE' => 0.0, 'BCP' => 0.0, 'VISA' => 0.0];
        $ultimoPago = null;
        foreach (['YAPE' => [0.12, 0.2, 3], 'VISA' => [0.05, 0.11, 2], 'BCP' => [0.0, 0.04, 1]] as $codigo => [$min, $max, $partes]) {
            $monto = round($totalVentas * $azar($min, $max), 2);
            if ($monto < 20) {
                continue;
            }
            $n = mt_rand(1, $partes);
            for ($p = 0; $p < $n; $p++) {
                $parte = $p === $n - 1 ? round($monto - round($monto / $n, 2) * ($n - 1), 2) : round($monto / $n, 2);
                $insPago->execute([$idReporte, $tiposPago[$codigo], $operacion(), $parte, '', $cierre]);
                $ultimoPago = [(int) $pdo->lastInsertId(), $codigo];
            }
            $medios[$codigo] = $monto;
        }
        $totalPagos = round(array_sum($medios), 2);
        $descuentos = mt_rand(1, 5) === 1 ? round($azar(5, 30), 2) : 0.0;
        $otrosGastos = mt_rand(1, 7) === 1 ? round($azar(10, 45), 2) : 0.0;

        // Cuadre: casi siempre exacto; a veces falta o sobra algo.
        $suerte = mt_rand(1, 100);
        $faltante = $suerte <= 74 ? 0.0 : ($suerte <= 90 ? -round($azar(2, 38), 1) : round($azar(0.5, 9), 1));
        $neto = $totalVentas - $descuentos;
        // El efectivo va en múltiplos de 10 céntimos; los céntimos que sobran los absorbe el último pago electrónico.
        $exacto = round($neto - $totalPagos - $creditos - $otrosGastos + $faltante, 2);
        $efectivo = round($exacto, 1);
        if ($ultimoPago && abs($exacto - $efectivo) > 0.001) {
            $ajuste = round($exacto - $efectivo, 2);
            $pdo->prepare('UPDATE pagos_reporte SET monto = monto + ? WHERE id_pago_reporte = ?')->execute([$ajuste, $ultimoPago[0]]);
            $medios[$ultimoPago[1]] = round($medios[$ultimoPago[1]] + $ajuste, 2);
            $totalPagos = round($totalPagos + $ajuste, 2);
        }
        $faltante = round($totalPagos + $creditos + $otrosGastos + $efectivo - $neto, 2);

        $aprobado = strtotime($cierre) < strtotime('-2 days');
        $totales->execute([
            round($soles['DIESEL'], 2), round($soles['REGULAR'], 2), round($soles['PREMIUM'], 2), $totalVentas,
            round($gal['DIESEL'], 3), round($gal['REGULAR'], 3), round($gal['PREMIUM'], 3), round($totalGalones, 3),
            $descuentos, $otrosGastos, $medios['YAPE'], $medios['BCP'], $medios['VISA'], $efectivo, $creditos, $totalPagos, $faltante,
            $aprobado ? $admin : null, $aprobado ? date('Y-m-d H:i:s', strtotime("$cierre +3 hours")) : null, $cierre, $idReporte,
        ]);

        $pdo->beginTransaction();
        Eventos_Turno::Al_Cerrar($pdo, $idReporte);
        $pdo->commit();
        $pdo->exec("UPDATE movimientos_tanque SET creado_en = '$cierre' WHERE id_reporte = $idReporte");

        // Arqueo del efectivo entregado, salvo los dos últimos turnos (quedan pendientes en Caja).
        if (strtotime($cierre) < strtotime('-1 day')) {
            $resto = (int) round($efectivo * 10);
            $cantidades = [];
            foreach (Modelo_Caja::DENOMINACIONES as $den) {
                $decimos = (int) round((float) $den * 10);
                $cantidades[$den] = intdiv($resto, $decimos);
                $resto -= $cantidades[$den] * $decimos;
            }
            $arqueo = $caja->Registrar_Arqueo($idReporte, $cantidades, '', $supervisor ?: $admin);
            $idArqueo = (int) $pdo->query("SELECT id_arqueo FROM arqueos WHERE id_reporte = $idReporte")->fetchColumn();
            $pdo->exec("UPDATE arqueos SET creado_en = '$cierre' + INTERVAL 20 MINUTE WHERE id_arqueo = $idArqueo");
            if ($faltante < -0.05) {
                $faltantes[] = [$idArqueo, $cierre];
            } elseif ($faltante > 0.05) {
                $caja->Resolver_Diferencia($idArqueo, 'JUSTIFICADO', 'Sobrante por vuelto redondeado', $supervisor ?: $admin);
            }
            $contador['arqueos']++;
        }
        $efectivoPorDepositar += $efectivo;
        $contador['turnos']++;
    }

    // Depósito al banco cada dos días con la mayor parte del efectivo.
    if ($d % 2 === 1 && $efectivoPorDepositar > 1000 && strtotime("$fecha +1 day 10:00") < time()) {
        $monto = floor($efectivoPorDepositar * $azar(0.82, 0.9) / 10) * 10;
        $caja->Registrar_Deposito(date('Y-m-d', strtotime("$fecha +1 day")), $elegir(['BCP', 'BCP', 'Interbank', 'BBVA']), $operacion(), $monto, 'Depósito de ventas', $supervisor ?: $admin);
        $pdo->exec("UPDATE depositos SET creado_en = '" . date('Y-m-d', strtotime("$fecha +1 day")) . " 10:30:00' WHERE id_deposito = " . (int) $pdo->query('SELECT MAX(id_deposito) FROM depositos')->fetchColumn());
        $efectivoPorDepositar -= $monto;
        $contador['depositos']++;
    }

    // Varillaje semanal de cada tanque.
    if ($d % 7 === 6) {
        foreach ($tanques as $t) {
            $teorico = (float) $pdo->query("SELECT stock_actual FROM tanques WHERE id_tanque = {$t['id']}")->fetchColumn();
            $inventario->Registrar_Varillaje($t['id'], $fecha, null, round($teorico + $azar(-14, 4), 1), $supervisor ?: $admin, 'Medición semanal');
        }
    }
}

// Faltantes: los antiguos resueltos, los recientes quedan por resolver en Caja.
foreach ($faltantes as [$idArqueo, $cierre]) {
    if (strtotime($cierre) < strtotime('-7 days')) {
        $caja->Resolver_Diferencia($idArqueo, $elegir(['DESCONTADO', 'CONDONADO', 'DESCONTADO']), 'Revisado con el grifero', $supervisor ?: $admin);
    }
}
$pdo->exec("UPDATE varillajes SET creado_en = CONCAT(fecha, ' 05:30:00')");
$pdo->exec("UPDATE arqueos SET actualizado_en = creado_en + INTERVAL 1 DAY WHERE resolucion IS NOT NULL AND resolucion <> 'PENDIENTE'");

// Lecturas finales en los surtidores.
$fijarLectura = $pdo->prepare('UPDATE surtidores SET lectura_actual = ? WHERE id_surtidor = ?');
foreach ($lectura as $id => $valor) {
    $fijarLectura->execute([$valor, $id]);
}
$paso("{$contador['turnos']} turnos, {$contador['vales']} vales, {$contador['compras']} compras de combustible, {$contador['arqueos']} arqueos, {$contador['depositos']} depósitos");

// ---------- Cobranza de los vales ----------
$creditosModelo = new Modelo_Creditos();
$cobrados = 0;
foreach ($pdo->query("SELECT vc.id_credito, vc.monto, vc.fecha_vencimiento, DATE(vc.created_at) AS fecha FROM ventas_credito vc ORDER BY vc.id_credito")->fetchAll(PDO::FETCH_ASSOC) as $v) {
    $plazo = max(1, (strtotime($v['fecha_vencimiento']) - strtotime($v['fecha'])) / 86400);
    $pago = strtotime($v['fecha'] . ' +' . (int) round($plazo * $azar(0.5, 1.1)) . ' days');
    $suerte = mt_rand(1, 100);
    if ($pago > time() || $suerte > 88) {
        continue; // aún no se cobra, o quedó vencido
    }
    $monto = $suerte <= 78 ? (float) $v['monto'] : round((float) $v['monto'] * $azar(0.3, 0.7), 2);
    $medio = $elegir(['BCP', 'BCP', 'YAPE', 'EFECTIVO']);
    $creditosModelo->Registrar_Pago_Credito($v['id_credito'], $tiposPago[$medio], $medio === 'EFECTIVO' ? '' : $operacion(), $monto, 0, date('Y-m-d', $pago), $admin, 'Cobranza');
    $cobrados++;
}
$pdo->exec('UPDATE historial_pagos_credito SET created_at = CONCAT(fecha_pago, " 11:00:00")');
$paso("$cobrados vales cobrados total o parcialmente");

// ---------- Gastos del grifo ----------
$gastos = new Modelo_Gastos_Grifo();
$categoria = $pdo->query('SELECT nombre, id_categoria FROM categorias_gasto')->fetchAll(PDO::FETCH_KEY_PAIR);
$cat = function (string $inicio) use ($categoria): int {
    foreach ($categoria as $nombre => $id) {
        if (stripos($nombre, $inicio) === 0) {
            return (int) $id;
        }
    }
    return (int) end($categoria);
};
$registrosGasto = 0;
for ($d = 0; $d < $dias; $d++) {
    $fecha = $dia($d);
    $diaMes = (int) date('j', strtotime($fecha));
    $lista = [];
    if ($diaMes === 5) {
        $lista[] = [$cat('Luz'), 'Recibo de luz del mes', $azar(780, 980), 'TRANSFERENCIA', 'Recibo'];
        $lista[] = [$cat('Agua'), 'Recibo de agua del mes', $azar(110, 160), 'TRANSFERENCIA', 'Recibo'];
        $lista[] = [$cat('Internet'), 'Internet y telefonía', 159.90, 'TRANSFERENCIA', 'Recibo'];
    }
    if ($diaMes === 1) {
        $lista[] = [$cat('Alquiler'), 'Alquiler del terreno', 4500, 'TRANSFERENCIA', 'Factura'];
        $lista[] = [$cat('Sueldos'), 'Planilla del mes anterior', 7800, 'TRANSFERENCIA', 'Planilla'];
    }
    if ($d % 7 === 2) {
        $lista[] = [$cat('Útiles'), $elegir(['Útiles de limpieza', 'Papel y útiles de oficina', 'Franelas y detergente']), $azar(35, 95), 'EFECTIVO', 'Boleta'];
    }
    if (mt_rand(1, 14) === 1) {
        $lista[] = [$cat('Mantenimiento'), $elegir(['Mantenimiento de surtidores', 'Cambio de mangueras', 'Calibración de dispensadores', 'Reparación de luminarias']), $azar(180, 850), 'TRANSFERENCIA', 'Factura'];
    }
    if (mt_rand(1, 20) === 1) {
        $lista[] = [$cat('Transporte'), 'Movilidad para trámites', $azar(20, 60), 'EFECTIVO', ''];
    }
    foreach ($lista as [$idCategoria, $descripcion, $monto, $medio, $comprobante]) {
        $gastos->Registrar($fecha, $idCategoria, $descripcion, round($monto, 2), $medio, $comprobante, $admin);
        $registrosGasto++;
    }
}
$pdo->exec('UPDATE gastos_grifo SET creado_en = CONCAT(fecha, " 12:00:00")');
$paso("$registrosGasto gastos");

// ---------- Tienda ----------
$tienda = new Modelo_Tienda();
$nuevoProducto = $pdo->prepare('INSERT INTO tienda_productos (codigo, nombre, categoria, unidad, precio_venta, costo, stock, stock_minimo, activo) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 1)');
foreach ([['7750001', 'Aceite 20W-50 1 galón', 'Lubricantes', 'UND', 85, 62, 4], ['7750002', 'Refrigerante 1 L', 'Aditivos', 'UND', 18, 11, 5],
          ['7750003', 'Agua 625 ml', 'Bebidas', 'UND', 2.5, 1.2, 24], ['7750004', 'Limpiaparabrisas', 'Accesorios', 'UND', 35, 20, 3],
          ['7750005', 'Gaseosa 500 ml', 'Bebidas', 'UND', 3.5, 2, 24], ['7750006', 'Aditivo limpia inyectores', 'Aditivos', 'UND', 25, 15, 4],
          ['7750007', 'Aceite 15W-40 1 galón', 'Lubricantes', 'UND', 95, 70, 4], ['7750008', 'Galletas de soda', 'Snacks', 'UND', 1.5, 0.8, 20],
          ['7750009', 'Chocolate 30 g', 'Snacks', 'UND', 2.5, 1.4, 20], ['7750010', 'Ambientador para auto', 'Accesorios', 'UND', 12, 6, 5],
          ['7750011', 'Bebida energizante 473 ml', 'Bebidas', 'UND', 6, 3.6, 12]] as $p) {
    if (!$pdo->query('SELECT COUNT(*) FROM tienda_productos WHERE codigo = ' . $pdo->quote($p[0]))->fetchColumn()) {
        $nuevoProducto->execute($p);
    }
}
$catalogo = $pdo->query('SELECT id_producto, categoria, stock_minimo FROM tienda_productos WHERE activo = 1')->fetchAll(PDO::FETCH_ASSOC);
foreach ($catalogo as $p) {
    $tienda->Entrada((int) $p['id_producto'], $p['categoria'] === 'Lubricantes' || $p['categoria'] === 'Accesorios' ? 15 : 80, null, 'Stock inicial', $admin);
}
$pdo->exec("UPDATE tienda_movimientos SET creado_en = '$inicio 08:00:00'");
$ventasTienda = 0;
for ($d = 0; $d < $dias; $d++) {
    $fecha = $dia($d);
    for ($v = 0, $n = mt_rand(3, 9); $v < $n; $v++) {
        $momento = strtotime("$fecha " . mt_rand(6, 22) . ':' . str_pad((string) mt_rand(0, 59), 2, '0', STR_PAD_LEFT));
        if ($momento > time()) {
            continue;
        }
        $items = [];
        foreach ((array) array_rand($catalogo, mt_rand(1, 3)) as $i) {
            $items[] = ['id_producto' => $catalogo[$i]['id_producto'], 'cantidad' => in_array($catalogo[$i]['categoria'], ['Bebidas', 'Snacks'], true) ? mt_rand(1, 3) : 1];
        }
        $medio = $elegir(['EFECTIVO', 'EFECTIVO', 'YAPE', 'TARJETA']);
        try {
            $venta = $tienda->Vender($items, $medio, $medio === 'EFECTIVO' ? '' : $operacion(), (int) $elegir($griferos));
        } catch (DomainException $e) {
            continue; // sin stock: se repone abajo
        }
        $idVenta = (int) ($venta['id_venta'] ?? $pdo->query('SELECT MAX(id_venta) FROM tienda_ventas')->fetchColumn());
        $cuando = date('Y-m-d H:i:s', $momento);
        $pdo->exec("UPDATE tienda_ventas SET fecha = '$cuando' WHERE id_venta = $idVenta");
        $pdo->exec("UPDATE tienda_movimientos SET creado_en = '$cuando' WHERE id_venta = $idVenta");
        $ventasTienda++;
    }
    // Reposición semanal de lo que bajó del mínimo.
    if ($d % 7 === 0 && $d > 0) {
        foreach ($pdo->query('SELECT id_producto, stock, stock_minimo FROM tienda_productos WHERE activo = 1 AND stock <= stock_minimo * 2')->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $tienda->Entrada((int) $p['id_producto'], max(10, (float) $p['stock_minimo'] * 4), null, 'Reposición', $admin);
            $pdo->exec("UPDATE tienda_movimientos SET creado_en = '$fecha 09:00:00' WHERE id_movimiento = " . (int) $pdo->query('SELECT MAX(id_movimiento) FROM tienda_movimientos')->fetchColumn());
        }
    }
}
// Dos productos quedan con stock bajo para que se vea el aviso.
$pdo->exec("UPDATE tienda_productos SET stock = LEAST(stock, stock_minimo) WHERE codigo IN ('7750002', '7750004')");
$paso("$ventasTienda ventas de la tienda");

// ---------- Solicitudes de flota desde la página pública ----------
$solicitud = $pdo->prepare("INSERT INTO solicitudes_flota (empresa, ruc, contacto, telefono, correo, unidades, galones_mes, mensaje, estado, ip, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '127.0.0.1', ?)");
foreach ([
    ['LOGÍSTICA COSTA VERDE S.A.C.', '20678901234', 'Patricia León', '986112233', 'compras@costaverde.pe', 8, 3200, 'Tenemos camiones que salen a Huacho todos los días.', 'NUEVA', 1],
    ['TURISMO LOS ANDES E.I.R.L.', '20689012345', 'Fernando Ortiz', '986223344', '', 5, 1500, '', 'NUEVA', 3],
    ['ACOPIO AGRARIO NORTE S.A.', '20690123456', 'Gloria Sotelo', '986334455', 'gsotelo@acopio.pe', 12, 5000, 'Queremos crédito a 30 días.', 'EN_PROCESO', 9],
] as [$empresa, $ruc, $contacto, $tel, $correo, $unidades, $galones, $mensaje, $estado, $hace]) {
    $solicitud->execute([$empresa, $ruc, $contacto, $tel, $correo ?: null, $unidades, $galones, $mensaje ?: null, $estado, date('Y-m-d H:i:s', strtotime("-$hace days"))]);
}
$pdo->exec('DELETE FROM auditoria');
$paso('3 solicitudes de flota');

echo "Listo. Usuarios: admin/admin123, supervisor1/supervisor123, contador1/contador123, grifero1/grifero123 y grifero2/grifero123\n";
