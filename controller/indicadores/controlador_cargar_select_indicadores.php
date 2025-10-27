<?php
    require '../../model/model_indicadores.php';
    $MI = new Modelo_Indicadores();//Instaciamos
    $consulta = $MI->Cargar_Select_Indicadores();
    echo json_encode($consulta);
 
?>
