<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_clientes.php';
    $MCL = new Modelo_Clientes();//Instaciamos
    $id = strtoupper(trim((string) ($_POST['id'])));

    $consulta = $MCL->Eliminar_Cliente($id);
    echo $consulta;



?>