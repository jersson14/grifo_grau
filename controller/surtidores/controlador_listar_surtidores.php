<?php
require_once '../../model/model_surtidores.php';

$MSurtidores = new Modelo_Surtidores();
$consulta = $MSurtidores->Listar_Surtidores();

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
