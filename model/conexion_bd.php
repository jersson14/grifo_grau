<?php
/**
 * Conexión a la base de datos. Los datos se configuran en .env (o en variables de entorno).
 * model/model_conexion.php, que git ignora, solo incluye este archivo.
 */
require_once dirname(__DIR__) . '/config/env.php';

date_default_timezone_set('America/Lima');

class conexionBD {
    /**
     * Abre una conexión nueva en cada llamada, como siempre lo hizo el sistema.
     * No se reutiliza una sola por petición: muchos modelos llaman procedimientos almacenados
     * que dejan resultados pendientes, y la siguiente consulta en la misma conexión fallaría
     * («Cannot execute queries while there are pending result sets»).
     */
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

    /** Se mantiene por compatibilidad: la conexión se cierra sola al terminar la petición. */
    public static function cerrar_conexion() {
    }
}
