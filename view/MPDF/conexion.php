<?php
require_once __DIR__ . '/../../config/env.php';
date_default_timezone_set('America/Lima');

$mysqli = new mysqli(env('DB_HOST', '127.0.0.1'), env('DB_USER', 'root'), env('DB_PASS', ''), env('DB_NAME'), (int) env('DB_PORT', '3306'));

if ($mysqli->connect_errno) {
    error_log('Fallo la conexion a MySQL: ' . $mysqli->connect_error);
    exit('No se pudo conectar a la base de datos.');
}

$mysqli->query("SET time_zone = '-05:00'");
