<?php
    require '../../model/model_clientes.php';
    $MCL = new Modelo_Clientes();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));

    $consulta = $MCL->Eliminar_Cliente($id);
    echo $consulta;



?>