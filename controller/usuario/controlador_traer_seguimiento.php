<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();//Instaciamos
    $numero = strtoupper(trim((string) ($_POST['numero'])));
    $dni = strtoupper(trim((string) ($_POST['dni'])));

    $consulta = $MU->Cargar_Select_Datos_Seguimiento($numero,$dni);
    echo json_encode($consulta);
 
?>
