<?php
    require '../../model/model_indicadores.php';
    $MI = new Modelo_Indicadores();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));

    $consulta = $MI->Eliminar_Indicador($id);
    echo $consulta;



?>