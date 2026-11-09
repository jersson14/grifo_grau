<?php
/**
 * Instala y actualiza la base de datos. Solo por consola.
 *
 *   php database/migrate.php                Aplica las migraciones pendientes.
 *   php database/migrate.php --demo         Además crea datos de demostración en una base vacía.
 *
 * Con una base sin tablas instala primero el esquema provisional. Si la base ya tiene
 * tablas, por ejemplo tras importar producción, solo aplica las migraciones.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/env.php';

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', env('DB_HOST', '127.0.0.1'), env('DB_PORT', '3307'), env('DB_NAME')),
    env('DB_USER', 'root'),
    env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function ejecutar_sql(PDO $pdo, string $archivo): void
{
    $sql = preg_replace('/^\s*--.*$/m', '', file_get_contents($archivo));
    foreach (array_filter(array_map('trim', explode(";\n", str_replace("\r\n", "\n", $sql . "\n")))) as $sentencia) {
        $pdo->exec(rtrim($sentencia, ';'));
    }
}

$vacia = (int) $pdo->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchColumn() === 0;

if ($vacia) {
    echo "Base vacía: instalando esquema provisional\n";
    ejecutar_sql($pdo, __DIR__ . '/schema/000_base_provisional.sql');
    foreach (require __DIR__ . '/schema/routines.php' as $rutina) {
        $pdo->exec($rutina);
    }
}

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
    archivo VARCHAR(190) PRIMARY KEY,
    aplicada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB');

$aplicadas = $pdo->query('SELECT archivo FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$archivos  = glob(__DIR__ . '/migrations/*.sql');
sort($archivos);

$nuevas = 0;
foreach ($archivos as $archivo) {
    $nombre = basename($archivo);
    if (in_array($nombre, $aplicadas, true)) {
        continue;
    }
    echo "Aplicando $nombre\n";
    ejecutar_sql($pdo, $archivo);
    $pdo->prepare('INSERT INTO schema_migrations (archivo) VALUES (?)')->execute([$nombre]);
    $nuevas++;
}
echo "Migraciones aplicadas: $nuevas\n";

if ($vacia && in_array('--demo', $argv, true)) {
    $usuario = $pdo->prepare("INSERT INTO usuario (usu_nombre, usu_apellido, usu_usuario, usu_contrasenia, usu_rol, usu_foto) VALUES (?, ?, ?, ?, ?, 'img/avatar.png')");
    $usuario->execute(['ADMIN', 'DEMO', 'admin', password_hash('admin123', PASSWORD_DEFAULT), 'ADMINISTRADOR']);
    $usuario->execute(['GRIFERO', 'UNO', 'grifero1', password_hash('grifero123', PASSWORD_DEFAULT), 'GRIFERO']);
    $usuario->execute(['GRIFERO', 'DOS', 'grifero2', password_hash('grifero123', PASSWORD_DEFAULT), 'GRIFERO']);

    $pdo->exec("INSERT INTO empresa (nombre, email, telefono, direccion, logo) VALUES ('GRIFO DEMO', 'demo@example.com', '', '', 'img/grau.png')");

    $pdo->exec("INSERT INTO productos (id_producto, nombre, tipo, precio_actual) VALUES
        (1, 'DIESEL B5', 'DIESEL', 15.000), (2, 'REGULAR 84', 'REGULAR', 13.000), (3, 'PREMIUM 95', 'PREMIUM', 16.000)");

    $surtidor = $pdo->prepare('INSERT INTO surtidores (numero_maquina, codigo, id_producto, lectura_actual) VALUES (?, ?, ?, ?)');
    foreach ([1, 2] as $maquina) {
        foreach (['BS1' => 1, 'BS2' => 1, 'R1' => 2, 'R2' => 2, 'P1' => 3, 'P2' => 3] as $codigo => $producto) {
            $surtidor->execute([$maquina, $codigo, $producto, 1000]);
        }
    }

    // Historial de precios de los últimos meses para que la página pública muestre su gráfico.
    $historial = $pdo->prepare("INSERT INTO precios_historial (id_producto, precio_anterior, precio_nuevo, origen, creado_en) VALUES (?, ?, ?, 'DEMO', ?)");
    foreach ([1 => [14.20, 14.60, 14.45, 14.80, 15.00], 2 => [12.40, 12.70, 12.55, 12.90, 13.00], 3 => [15.30, 15.60, 15.45, 15.80, 16.00]] as $producto => $serie) {
        foreach (array_slice($serie, 1) as $k => $precio) {
            $historial->execute([$producto, $serie[$k], $precio, date('Y-m-d 07:00:00', strtotime('-' . (80 - $k * 20) . ' days'))]);
        }
    }

    $pdo->exec("INSERT INTO clientes (nombre_completo, dni_ruc, telefono) VALUES ('CLIENTE DEMO', '00000000', '')");
    // Las claves de demostración son públicas a propósito: no se obliga a cambiarlas. En producción queda encendido.
    $pdo->exec("UPDATE configuracion SET valor = '0' WHERE clave = 'clave_forzar_cambio'");
    echo "Datos de demostración creados. Usuarios: admin/admin123 y grifero1/grifero123\n";
}
