<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();
    $id = strtoupper(trim((string) ($_POST['id'])));
    $estatus = strtoupper(trim((string) ($_POST['estatus'])));

    $MU->Validar_Cambio_Estatus((int) $id, $estatus);
    $consulta = $MU->Modificar_Usuario_Estatus($id,$estatus);
    echo $consulta;



?>