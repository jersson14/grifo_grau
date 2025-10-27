<?php
    require '../../model/model_pagos.php';
    $MPA = new Modelo_Pagos();//Instaciamos
    $fechaini = htmlspecialchars($_POST['fechaini'],ENT_QUOTES,'UTF-8');
    $fechafin = htmlspecialchars($_POST['fechafin'],ENT_QUOTES,'UTF-8');
    $servicio = htmlspecialchars($_POST['servicio'],ENT_QUOTES,'UTF-8');

    $consulta = $MPA->Listar_pagos_filtro($fechaini,$fechafin,$servicio);
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
