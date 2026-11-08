<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();

$id = trim((string) ($_POST['id']));
$numero_maquina = trim((string) ($_POST['numero_maquina']));
$codigo = trim((string) ($_POST['codigo']));
$id_producto = trim((string) ($_POST['id_producto']));

// Verificar si el código ya existe en esa máquina (excluyendo el actual)
$existe = $MSurtidores->Verificar_Codigo_Duplicado($codigo, $numero_maquina, $id);
if ($existe > 0) {
    echo -1; // Código duplicado
} else {
    $consulta = $MSurtidores->Modificar_Surtidor($id, $numero_maquina, $codigo, $id_producto);
    echo $consulta;
}
?>
