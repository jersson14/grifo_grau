<?php
    require '../../model/model_pagos.php';
    $MPA = new Modelo_Pagos();//Instaciamos
    $id_pago = strtoupper(htmlspecialchars($_POST['id_pago'],ENT_QUOTES,'UTF-8'));


    $consulta = $MPA->Listar_pagos_expediente($id_pago);
    echo json_encode($consulta);



?>