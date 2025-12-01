<?php
date_default_timezone_set('America/Lima');

$mysqli = new mysqli("localhost", "root", "", "grifo_grau", 3307);

// Verificar conexión
if ($mysqli->connect_errno) {
    echo "Falló la conexión a MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}

// Configurar zona horaria en MySQL
$mysqli->query("SET time_zone = '-05:00'");
?>
