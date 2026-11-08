<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();//Instaciamos
    $codigo = strtoupper(trim((string) ($_POST['codigo'])));

    $consulta = $MU->Traer_Datos_Detalle_Seguimiento($codigo);
    echo json_encode($consulta);
 
?>
