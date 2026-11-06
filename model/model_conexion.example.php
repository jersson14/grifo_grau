<?php
// Plantilla de model/model_conexion.php, que git ignora. Copiarla con ese nombre al instalar.
// Los datos de conexión se configuran en el archivo .env.
require_once dirname(__DIR__) . '/config/env.php';

date_default_timezone_set('America/Lima');

class conexionBD {
    private $pdo;

    public static function conexionPDO() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_NAME')
            );
            $pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET time_zone = '-05:00'");
            return $pdo;
        } catch (PDOException $e) {
            error_log('Fallo la conexion a la base de datos: ' . $e->getMessage());
            return null;
        }
    }

    public function cerrar_conexion() {
        $this->pdo = null;
    }
}
