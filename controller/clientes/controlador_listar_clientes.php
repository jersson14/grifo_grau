<?php
    require '../../model/model_clientes.php';
    $MCL = new Modelo_Clientes();//Instaciamos
    $consulta = $MCL->Listar_Clientes();
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
