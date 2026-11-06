<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $estatus = strtoupper(htmlspecialchars($_POST['estatus'],ENT_QUOTES,'UTF-8'));

    $MU->Validar_Cambio_Estatus((int) $id, $estatus);
    $consulta = $MU->Modificar_Usuario_Estatus($id,$estatus);
    echo $consulta;



?>