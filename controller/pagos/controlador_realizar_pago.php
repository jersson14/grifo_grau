<?php
    require '../../model/model_pagos.php';
    $MPA = new Modelo_Pagos();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $pagar = strtoupper(htmlspecialchars($_POST['pagar'],ENT_QUOTES,'UTF-8'));
    $igv = strtoupper(htmlspecialchars($_POST['igv'],ENT_QUOTES,'UTF-8'));
    $subtotal = strtoupper(htmlspecialchars($_POST['subtotal'],ENT_QUOTES,'UTF-8'));
    $saldo = strtoupper(htmlspecialchars($_POST['saldo'],ENT_QUOTES,'UTF-8'));
    $descrip = strtoupper(htmlspecialchars($_POST['descrip'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));


    $consulta = $MPA->Realizar_pago($id,$pagar,$igv,$subtotal,$saldo,$descrip,$idusu);
    echo $consulta;



?>