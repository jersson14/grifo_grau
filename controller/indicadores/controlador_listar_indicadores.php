<?php
    require '../../model/model_indicadores.php';
    $MI = new Modelo_Indicadores();//Instaciamos
    $consulta = $MI->Listar_Indicadores();
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
