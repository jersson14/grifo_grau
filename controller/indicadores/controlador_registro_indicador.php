<?php
    require '../../model/model_indicadores.php';
    $MI = new Modelo_Indicadores();
    $tipo = strtoupper(htmlspecialchars($_POST['tipo'],ENT_QUOTES,'UTF-8'));
    $indi = strtoupper(htmlspecialchars($_POST['indi'],ENT_QUOTES,'UTF-8'));
    $descrip = strtoupper(htmlspecialchars($_POST['descrip'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));

    $consulta = $MI->Registrar_Indicadores($tipo,$indi,$descrip,$idusu);
    echo $consulta;



?>