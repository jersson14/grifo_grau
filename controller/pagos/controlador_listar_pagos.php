<?php
    require '../../model/model_pagos.php';
    $MPA = new Modelo_Pagos();//Instaciamos
    $consulta = $MPA->Listar_Pagos();
    if($consulta){
        echo json_encode($consulta);
    }else{
        echo '{
            "sEcho": 1,
            "iTotalRecords": "0",
            "iTotalDisplayRecords": "0",
            "aaData": []
        }';
    }
?>
