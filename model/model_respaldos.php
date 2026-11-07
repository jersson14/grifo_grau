<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';

/**
 * Copias de seguridad de la base en archivos .sql (comprimidos si el servidor lo permite), generadas con
 * PHP para no depender de mysqldump, que el hosting compartido suele no ofrecer. Se restauran con
 * phpMyAdmin (Importar) o con el cliente mysql.
 */
class Modelo_Respaldos extends conexionBD
{
    private const PATRON = '/^respaldo_\d{8}_\d{6}_[0-9a-f]{6}\.sql(\.gz)?$/';

    public static function Carpeta(): string
    {
        $carpeta = dirname(__DIR__) . '/respaldos';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0750, true);
        }
        // La carpeta nunca debe servirse por web: contiene hashes y secretos cifrados.
        if (!is_file($carpeta . '/.htaccess')) {
            file_put_contents($carpeta . '/.htaccess', "Require all denied\n");
        }
        return $carpeta;
    }

    public function Generar(?int $idUsuario): array
    {
        $comprimir = function_exists('gzopen');
        $nombre = 'respaldo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql' . ($comprimir ? '.gz' : '');
        $ruta = self::Carpeta() . '/' . $nombre;
        $archivo = $comprimir ? gzopen($ruta, 'wb6') : fopen($ruta, 'wb');
        $escribir = fn(string $texto) => $comprimir ? gzwrite($archivo, $texto) : fwrite($archivo, $texto);

        $c = conexionBD::conexionPDO();
        // Sin búfer, una tabla grande se recorre sin cargarla entera en memoria.
        $lectura = conexionBD::conexionPDO();
        $lectura->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        try {
            $escribir("-- Copia de seguridad de " . $c->query('SELECT DATABASE()')->fetchColumn() . ' generada el ' . date('Y-m-d H:i:s') . "\n");
            $escribir("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET time_zone = '-05:00';\n\n");

            $tablas = $c->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tablas as $tabla) {
                $creacion = $c->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM)[1];
                $escribir("DROP TABLE IF EXISTS `$tabla`;\n$creacion;\n");

                $consulta = $lectura->query("SELECT * FROM `$tabla`");
                $lote = [];
                while ($fila = $consulta->fetch(PDO::FETCH_NUM)) {
                    $lote[] = '(' . implode(',', array_map(fn($v) => $v === null ? 'NULL' : $c->quote((string) $v), $fila)) . ')';
                    if (count($lote) === 200) {
                        $escribir("INSERT INTO `$tabla` VALUES\n" . implode(",\n", $lote) . ";\n");
                        $lote = [];
                    }
                }
                $consulta->closeCursor();
                if ($lote) {
                    $escribir("INSERT INTO `$tabla` VALUES\n" . implode(",\n", $lote) . ";\n");
                }
                $escribir("\n");
            }

            foreach ($c->query('SHOW PROCEDURE STATUS WHERE Db = DATABASE()')->fetchAll(PDO::FETCH_ASSOC) as $proc) {
                $nombreProc = $proc['Name'];
                $definicion = $c->query("SHOW CREATE PROCEDURE `$nombreProc`")->fetch(PDO::FETCH_ASSOC)['Create Procedure'];
                // Sin DEFINER, la copia se puede restaurar con otro usuario de base de datos, como en un hosting.
                $definicion = preg_replace('/DEFINER=`[^`]*`@`[^`]*`\s*/', '', $definicion);
                $escribir("DROP PROCEDURE IF EXISTS `$nombreProc`;\nDELIMITER $$\n$definicion$$\nDELIMITER ;\n\n");
            }

            $escribir("SET FOREIGN_KEY_CHECKS = 1;\n");
        } catch (Throwable $e) {
            $comprimir ? gzclose($archivo) : fclose($archivo);
            @unlink($ruta);
            throw $e;
        }
        $comprimir ? gzclose($archivo) : fclose($archivo);

        $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
          ->execute([$idUsuario, $_SESSION['S_USU'] ?? 'sistema', 'RESPALDO_GENERADO', $nombre, $_SERVER['REMOTE_ADDR'] ?? null]);

        $this->Depurar();
        return ['archivo' => $nombre, 'bytes' => filesize($ruta)];
    }

    /** Conserva solo las copias más recientes, según Configuración. */
    private function Depurar(): void
    {
        $config = new Modelo_Configuracion();
        $conservar = max(1, (int) $config->Obtener('respaldo_conservar', '14'));
        $copias = $this->Listar();
        foreach (array_slice($copias, $conservar) as $copia) {
            @unlink(self::Carpeta() . '/' . $copia['archivo']);
        }
    }

    public function Listar(): array
    {
        $copias = [];
        foreach (scandir(self::Carpeta()) as $archivo) {
            if (preg_match(self::PATRON, $archivo)) {
                $ruta = self::Carpeta() . '/' . $archivo;
                $copias[] = ['archivo' => $archivo, 'bytes' => filesize($ruta), 'fecha' => date('Y-m-d H:i:s', filemtime($ruta))];
            }
        }
        // El nombre empieza por la fecha: ordenarlo de mayor a menor deja primero la más reciente.
        usort($copias, fn($a, $b) => strcmp($b['archivo'], $a['archivo']));
        return $copias;
    }

    /** Ruta absoluta de una copia, solo si el nombre es exactamente el de una copia existente. */
    public function Ruta(string $archivo): ?string
    {
        if (!preg_match(self::PATRON, $archivo)) {
            return null;
        }
        $ruta = self::Carpeta() . '/' . $archivo;
        return is_file($ruta) ? $ruta : null;
    }

    public function Eliminar(string $archivo, int $idUsuario): void
    {
        $ruta = $this->Ruta($archivo);
        if ($ruta === null) {
            throw new DomainException('La copia no existe.');
        }
        unlink($ruta);
        $c = conexionBD::conexionPDO();
        $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
          ->execute([$idUsuario, $_SESSION['S_USU'] ?? null, 'RESPALDO_ELIMINADO', $archivo, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    /** Días desde la última copia, o null si nunca se hizo una. */
    public static function Dias_Sin_Respaldo(): ?int
    {
        $m = new self();
        $copias = $m->Listar();
        if (!$copias) {
            return null;
        }
        return (int) floor((time() - strtotime($copias[0]['fecha'])) / 86400);
    }
}
