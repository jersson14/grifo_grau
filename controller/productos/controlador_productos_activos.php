<?php
require_once '../../model/model_productos.php';

$MProductos = new Modelo_Productos();
$consulta = $MProductos->Obtener_Precios_Actuales();
echo json_encode($consulta);
?>
