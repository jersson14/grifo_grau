<?php
    require '../../model/model_gastos.php';
    $MGA = new Modelo_Gastos();//Instaciamos
    $indica = htmlspecialchars($_POST['indica'],ENT_QUOTES,'UTF-8');
    $fechainicio = htmlspecialchars($_POST['fechainicio'],ENT_QUOTES,'UTF-8');
    $fechafin = htmlspecialchars($_POST['fechafin'],ENT_QUOTES,'UTF-8');

    $consulta = $MGA->Listar_gastos_fechas($indica,$fechainicio,$fechafin);
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
