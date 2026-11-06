<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_indicadores.php';
    $MI = new Modelo_Indicadores();//Instaciamos
    $consulta = $MI->Cargar_Select_Indicadores();
    echo json_encode($consulta);
 
?>
