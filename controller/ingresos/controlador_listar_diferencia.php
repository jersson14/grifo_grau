<?php
    require '../../model/model_ingresos.php';
    $MIN = new Modelo_Ingresos();//Instaciamos
    $consulta = $MIN->Listar_diferencia();
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
