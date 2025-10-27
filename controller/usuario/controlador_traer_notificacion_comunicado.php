<?php
    require '../../model/model_comunicados.php';
    $MU = new Modelo_Comunicados();//Instaciamos
    $consulta = $MU->Listar_notificacion();
    echo json_encode($consulta);
 
?>
