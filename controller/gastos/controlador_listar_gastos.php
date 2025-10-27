<?php
    require '../../model/model_gastos.php';
    $MGA = new Modelo_Gastos();//Instaciamos
    $consulta = $MGA->listar_Gastos();
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
