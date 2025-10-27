<?php
    require '../../model/model_gastos.php';
    $MGA = new Modelo_Gastos();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $descri = strtoupper(htmlspecialchars($_POST['descri'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));

    $consulta = $MGA->Anular_Gastos($id,$descri,$idusu);
    echo $consulta;



?>