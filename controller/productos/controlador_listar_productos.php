<?php
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();
$consulta = $MProductos->Listar_Productos();

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
