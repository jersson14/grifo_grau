<?php
    require '../../model/model_gastos.php';
    $MGA = new Modelo_Gastos();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $indi = strtoupper(htmlspecialchars($_POST['indi'],ENT_QUOTES,'UTF-8'));
    $cantidad = strtoupper(htmlspecialchars($_POST['cantidad'],ENT_QUOTES,'UTF-8'));
    $monto = strtoupper(htmlspecialchars($_POST['monto'],ENT_QUOTES,'UTF-8'));
    $descri = strtoupper(htmlspecialchars($_POST['descri'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));

    $consulta = $MGA->Modificar_Gastos($id,$indi,$cantidad,$monto,$descri,$idusu);
    echo $consulta;



?>