<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
$numero_maquina = htmlspecialchars($_POST['numero_maquina'], ENT_QUOTES, 'UTF-8');
$codigo = htmlspecialchars($_POST['codigo'], ENT_QUOTES, 'UTF-8');
$id_producto = htmlspecialchars($_POST['id_producto'], ENT_QUOTES, 'UTF-8');

// Verificar si el código ya existe en esa máquina (excluyendo el actual)
$existe = $MSurtidores->Verificar_Codigo_Duplicado($codigo, $numero_maquina, $id);
if ($existe > 0) {
    echo -1; // Código duplicado
} else {
    $consulta = $MSurtidores->Modificar_Surtidor($id, $numero_maquina, $codigo, $id_producto);
    echo $consulta;
}
?>
