<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$numero_maquina = htmlspecialchars($_POST['numero_maquina'], ENT_QUOTES, 'UTF-8');
$codigo = htmlspecialchars($_POST['codigo'], ENT_QUOTES, 'UTF-8');
$id_producto = htmlspecialchars($_POST['id_producto'], ENT_QUOTES, 'UTF-8');
$lectura_inicial = htmlspecialchars($_POST['lectura_inicial'], ENT_QUOTES, 'UTF-8');

// Verificar si el código ya existe en esa máquina
$existe = $MSurtidores->Verificar_Codigo_Duplicado($codigo, $numero_maquina);
if ($existe > 0) {
    echo -1; // Código duplicado
} else {
    $consulta = $MSurtidores->Registrar_Surtidor($numero_maquina, $codigo, $id_producto, $lectura_inicial);
    echo $consulta;
}
?>
