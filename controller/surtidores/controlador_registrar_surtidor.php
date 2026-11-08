<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$numero_maquina = trim((string) ($_POST['numero_maquina']));
$codigo = trim((string) ($_POST['codigo']));
$id_producto = trim((string) ($_POST['id_producto']));
$lectura_inicial = trim((string) ($_POST['lectura_inicial']));

// Verificar si el código ya existe en esa máquina
$existe = $MSurtidores->Verificar_Codigo_Duplicado($codigo, $numero_maquina);
if ($existe > 0) {
    echo -1; // Código duplicado
} else {
    $consulta = $MSurtidores->Registrar_Surtidor($numero_maquina, $codigo, $id_producto, $lectura_inicial);
    echo $consulta;
}
?>
